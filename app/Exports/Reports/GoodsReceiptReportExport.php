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

class GoodsReceiptReportExport implements FromQuery, ShouldAutoSize, WithCustomChunkSize, WithHeadings, WithMapping
{
    use Exportable;

    /** @param array<string, mixed> $filters */
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return app(InventoryReportQuery::class)->goodsIn($this->filters);
    }

    /** @return list<string> */
    public function headings(): array
    {
        return ['Tanggal Masuk', 'No Barang Masuk', 'Source Type', 'Supplier', 'PO No', 'Invoice No', 'Gudang', 'SKU', 'Nama Barang', 'Kategori', 'Qty', 'Harga Satuan', 'Total Harga', 'Kondisi', 'Serial Numbers'];
    }

    /** @return list<mixed> */
    public function map(mixed $row): array
    {
        return [$row->receipt_date, $row->receipt_no, $row->source_type, $row->supplier_name, $row->po_no, $row->invoice_no, $row->warehouse_name, $row->sku, $row->item_name, $row->category_name, (int) $row->qty, (float) $row->unit_price, (float) $row->total_price, $row->condition_status, $row->serial_numbers];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
