<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class InventoryReportQuery
{
    /** @param array<string, mixed> $filters */
    public function stock(array $filters): Builder
    {
        $query = DB::table('stocks')
            ->join('items', 'items.id', '=', 'stocks.item_id')
            ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
            ->join('locations', 'locations.id', '=', 'stocks.location_id')
            ->select([
                'stocks.id',
                'items.sku',
                'items.name as item_name',
                'items.unit',
                'items.minimum_stock',
                'categories.name as category_name',
                'locations.code as location_code',
                'locations.name as location_name',
                'stocks.qty_on_hand',
                'stocks.qty_reserved',
                'stocks.qty_available',
                'stocks.last_movement_at',
                DB::raw('CASE WHEN stocks.qty_available <= items.minimum_stock THEN 1 ELSE 0 END as is_low_stock'),
            ])
            ->where('items.is_active', true);

        if (! empty($filters['location_id'])) {
            $query->where('stocks.location_id', $filters['location_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('items.category_id', $filters['category_id']);
        }
        if (! empty($filters['supplier_id'])) {
            $query->whereExists(function (Builder $sub) use ($filters): void {
                $sub->selectRaw('1')
                    ->from('goods_receipt_items')
                    ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
                    ->whereColumn('goods_receipt_items.item_id', 'items.id')
                    ->where('goods_receipts.supplier_id', $filters['supplier_id'])
                    ->where('goods_receipts.status', 'posted');
            });
        }
        $this->applyKeyword($query, $filters, ['items.name', 'items.sku', 'locations.name', 'locations.code']);

        return $query->orderBy('locations.name')->orderBy('items.name');
    }

    /** @param array<string, mixed> $filters */
    public function goodsIn(array $filters): Builder
    {
        $query = DB::table('goods_receipts')
            ->join('goods_receipt_items', 'goods_receipt_items.goods_receipt_id', '=', 'goods_receipts.id')
            ->join('items', 'items.id', '=', 'goods_receipt_items.item_id')
            ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'goods_receipts.supplier_id')
            ->leftJoin('locations', 'locations.id', '=', 'goods_receipts.warehouse_location_id')
            ->select([
                'goods_receipts.id as receipt_id',
                'goods_receipts.receipt_no',
                'goods_receipts.receipt_date',
                'goods_receipts.po_no',
                'goods_receipts.invoice_no',
                'goods_receipts.source_type',
                'goods_receipts.status',
                'suppliers.name as supplier_name',
                'locations.name as warehouse_name',
                'items.sku',
                'items.name as item_name',
                'categories.name as category_name',
                'goods_receipt_items.qty',
                'goods_receipt_items.unit_price',
                'goods_receipt_items.total_price',
                'goods_receipt_items.condition_status',
                'goods_receipt_items.serial_numbers',
            ])
            ->where('goods_receipts.status', 'posted');

        $this->applyPeriod($query, 'goods_receipts.receipt_date', $filters);
        if (! empty($filters['location_id'])) {
            $query->where('goods_receipts.warehouse_location_id', $filters['location_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('items.category_id', $filters['category_id']);
        }
        if (! empty($filters['supplier_id'])) {
            $query->where('goods_receipts.supplier_id', $filters['supplier_id']);
        }
        $this->applyKeyword($query, $filters, ['goods_receipts.receipt_no', 'goods_receipts.po_no', 'goods_receipts.invoice_no', 'items.name', 'items.sku']);

        return $query->orderByDesc('goods_receipts.receipt_date')->orderByDesc('goods_receipts.id');
    }

    /** @param array<string, mixed> $filters */
    public function goodsOut(array $filters): Builder
    {
        $query = DB::table('goods_issues')
            ->join('goods_issue_items', 'goods_issue_items.goods_issue_id', '=', 'goods_issues.id')
            ->join('items', 'items.id', '=', 'goods_issue_items.item_id')
            ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
            ->leftJoin('locations', 'locations.id', '=', 'goods_issues.target_location_id')
            ->leftJoin('users as requested_users', 'requested_users.id', '=', 'goods_issues.requested_by')
            ->leftJoin('users as posted_users', 'posted_users.id', '=', 'goods_issues.posted_by')
            ->select([
                'goods_issues.id as issue_id',
                'goods_issues.issue_no',
                'goods_issues.issue_date',
                'goods_issues.recipient_type',
                'goods_issues.recipient_name',
                'goods_issues.document_no',
                'goods_issues.status',
                'locations.name as target_location_name',
                'requested_users.name as requested_by_name',
                'posted_users.name as posted_by_name',
                'items.sku',
                'items.name as item_name',
                'categories.name as category_name',
                'goods_issue_items.qty',
                'goods_issue_items.condition_status',
                'goods_issue_items.serial_no',
                'goods_issue_items.notes',
            ])
            ->where('goods_issues.status', 'posted');

        $this->applyPeriod($query, 'goods_issues.issue_date', $filters);
        if (! empty($filters['location_id'])) {
            $query->where('goods_issues.target_location_id', $filters['location_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('items.category_id', $filters['category_id']);
        }
        if (! empty($filters['supplier_id'])) {
            $query->whereExists(function (Builder $sub) use ($filters): void {
                $sub->selectRaw('1')
                    ->from('goods_receipt_items')
                    ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
                    ->whereColumn('goods_receipt_items.item_id', 'items.id')
                    ->where('goods_receipts.supplier_id', $filters['supplier_id'])
                    ->where('goods_receipts.status', 'posted');
            });
        }
        $this->applyKeyword($query, $filters, ['goods_issues.issue_no', 'goods_issues.document_no', 'goods_issues.recipient_name', 'items.name', 'items.sku']);

        return $query->orderByDesc('goods_issues.issue_date')->orderByDesc('goods_issues.id');
    }

    /** @param array<string, mixed> $filters */
    private function applyPeriod(Builder $query, string $column, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->where($column, '>=', Carbon::parse((string) $filters['date_from'])->startOfDay());
        }
        if (! empty($filters['date_to'])) {
            $query->where($column, '<=', Carbon::parse((string) $filters['date_to'])->endOfDay());
        }
    }

    /** @param array<string, mixed> $filters @param list<string> $columns */
    private function applyKeyword(Builder $query, array $filters, array $columns): void
    {
        if (empty($filters['keyword'])) {
            return;
        }
        $keyword = '%'.$filters['keyword'].'%';
        $query->where(function (Builder $q) use ($columns, $keyword): void {
            foreach ($columns as $index => $column) {
                $index === 0 ? $q->where($column, 'like', $keyword) : $q->orWhere($column, 'like', $keyword);
            }
        });
    }
}
