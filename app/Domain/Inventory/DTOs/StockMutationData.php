<?php

namespace App\Domain\Inventory\DTOs;

use App\Domain\Inventory\DTOs\Concerns\BuildsTransactionData;

final readonly class StockMutationData
{
    use BuildsTransactionData;

    public function __construct(public string $mutationDate, public int $sourceLocationId, public int $destinationLocationId, public ?string $notes, public array $items) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(mutationDate: (string) $data['mutation_date'], sourceLocationId: (int) $data['source_location_id'], destinationLocationId: (int) $data['destination_location_id'], notes: $data['notes'] ?? null, items: self::normalizeItems($data));
    }
}
