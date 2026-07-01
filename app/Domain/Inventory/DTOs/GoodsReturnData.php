<?php

namespace App\Domain\Inventory\DTOs;

use App\Domain\Inventory\DTOs\Concerns\BuildsTransactionData;

final readonly class GoodsReturnData
{
    use BuildsTransactionData;

    public function __construct(public string $returnDate, public int $warehouseLocationId, public ?string $originLocation, public ?string $reason, public array $items) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(returnDate: (string) $data['return_date'], warehouseLocationId: (int) $data['warehouse_location_id'], originLocation: $data['origin_location'] ?? null, reason: $data['reason'] ?? null, items: self::normalizeItems($data));
    }
}
