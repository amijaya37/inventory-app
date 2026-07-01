<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Domain\Master\Models\Item;
use App\Domain\Inventory\Models\GoodsReceipt;
use App\Domain\Inventory\Models\GoodsReceiptItem;
use App\Domain\Inventory\Models\GoodsIssue;
use App\Domain\Inventory\Models\GoodsIssueItem;
use App\Domain\Inventory\Models\GoodsReturn;
use App\Domain\Inventory\Models\StockMutation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalItems = Item::query()->count();

        // Items with total stock <= minimum_stock (where minimum_stock > 0)
        $lowStockCount = Item::query()
            ->where('minimum_stock', '>', 0)
            ->where(function ($query) {
                $query->selectRaw('COALESCE(SUM(qty_on_hand), 0)')
                    ->from('stocks')
                    ->whereColumn('stocks.item_id', 'items.id');
            }, '<=', DB::raw('minimum_stock'))
            ->count();

        $today = now()->startOfDay();

        // Sum quantities for today's posted receipts/issues
        $goodsInToday = (int) GoodsReceiptItem::query()
            ->whereHas('goodsReceipt', fn ($q) => $q->whereDate('receipt_date', $today)->where('status', 'posted'))
            ->sum('qty');

        $goodsOutToday = (int) GoodsIssueItem::query()
            ->whereHas('goodsIssue', fn ($q) => $q->whereDate('issue_date', $today)->where('status', 'posted'))
            ->sum('qty');

        // Recent Transactions: pull latest 3 from each transaction type
        $receipts = GoodsReceipt::query()->latest()->limit(3)->get()->map(fn ($r) => [
            'type' => 'Barang Masuk',
            'no' => $r->receipt_no,
            'date' => $r->receipt_date,
            'status' => $r->status,
            'qty' => $r->items()->sum('qty'),
            'created_at' => $r->created_at,
        ]);

        $issues = GoodsIssue::query()->latest()->limit(3)->get()->map(fn ($i) => [
            'type' => 'Barang Keluar',
            'no' => $i->issue_no,
            'date' => $i->issue_date,
            'status' => $i->status,
            'qty' => $i->items()->sum('qty'),
            'created_at' => $i->created_at,
        ]);

        $mutations = StockMutation::query()->latest()->limit(3)->get()->map(fn ($m) => [
            'type' => 'Mutasi',
            'no' => $m->mutation_no,
            'date' => $m->mutation_date,
            'status' => $m->status,
            'qty' => $m->items()->sum('qty'),
            'created_at' => $m->created_at,
        ]);

        $returns = GoodsReturn::query()->latest()->limit(3)->get()->map(fn ($ret) => [
            'type' => 'Barang Tarikan',
            'no' => $ret->return_no,
            'date' => $ret->return_date,
            'status' => $ret->status,
            'qty' => $ret->items()->sum('qty'),
            'created_at' => $ret->created_at,
        ]);

        // Merge and sort
        $recentTransactions = collect()
            ->concat($receipts)
            ->concat($issues)
            ->concat($mutations)
            ->concat($returns)
            ->sortByDesc('created_at')
            ->take(5);

        return view('dashboard.index', [
            'totalItems' => $totalItems,
            'lowStockCount' => $lowStockCount,
            'goodsInToday' => $goodsInToday,
            'goodsOutToday' => $goodsOutToday,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}

