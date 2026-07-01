<?php

namespace App\Exports\Reports;

use App\Services\Reports\InventoryReportQuery;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockReportExport implements FromQuery, ShouldAutoSize, WithCustomChunkSize, WithHeadings, WithMapping
{
    use Exportable;

    /** @param array<string, mixed> $filters */
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return app(InventoryReportQuery::class)->stock($this->filters);
    }

    /** @return list<string> */
    public function headings(): array
    {
        return ['SKU', 'Nama Barang', 'Kategori', 'Lokasi', 'Kode Lokasi', 'Qty On Hand', 'Qty Reserved', 'Qty Available', 'Minimum Stock', 'Satuan', 'Status Stock', 'Last Movement'];
    }

    /** @return list<mixed> */
    public function map(mixed $row): array
    {
        return [$row->sku, $row->item_name, $row->category_name, $row->location_name, $row->location_code, (int) $row->qty_on_hand, (int) $row->qty_reserved, (int) $row->qty_available, (int) $row->minimum_stock, $row->unit, $row->is_low_stock ? 'Low Stock' : 'Aman', $row->last_movement_at];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
