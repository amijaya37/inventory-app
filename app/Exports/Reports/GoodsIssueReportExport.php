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

class GoodsIssueReportExport implements FromQuery, ShouldAutoSize, WithCustomChunkSize, WithHeadings, WithMapping
{
    use Exportable;

    /** @param array<string, mixed> $filters */
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return app(InventoryReportQuery::class)->goodsOut($this->filters);
    }

    /** @return list<string> */
    public function headings(): array
    {
        return ['Tanggal Keluar', 'No Barang Keluar', 'Jenis Penerima', 'Nama Penerima', 'Lokasi Tujuan', 'Requested By', 'Posted By', 'No Dokumen', 'SKU', 'Nama Barang', 'Kategori', 'Qty', 'Kondisi', 'Serial Number', 'Catatan'];
    }

    /** @return list<mixed> */
    public function map(mixed $row): array
    {
        return [$row->issue_date, $row->issue_no, $row->recipient_type, $row->recipient_name, $row->target_location_name, $row->requested_by_name, $row->posted_by_name, $row->document_no, $row->sku, $row->item_name, $row->category_name, (int) $row->qty, $row->condition_status, $row->serial_no, $row->notes];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
