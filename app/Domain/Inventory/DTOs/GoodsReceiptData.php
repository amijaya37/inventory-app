<?php

namespace App\Domain\Inventory\DTOs;

use App\Domain\Inventory\DTOs\Concerns\BuildsTransactionData;

final readonly class GoodsReceiptData
{
    use BuildsTransactionData;

    public function __construct(public string $sourceType, public ?int $supplierId, public ?string $poNo, public ?string $invoiceNo, public string $receiptDate, public int $warehouseLocationId, public array $items, public array $documents = []) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(sourceType: (string) $data['source_type'], supplierId: isset($data['supplier_id']) ? (int) $data['supplier_id'] : null, poNo: $data['po_no'] ?? null, invoiceNo: $data['invoice_no'] ?? null, receiptDate: (string) $data['receipt_date'], warehouseLocationId: (int) $data['warehouse_location_id'], items: self::normalizeItems($data), documents: $data['documents'] ?? []);
    }
}
