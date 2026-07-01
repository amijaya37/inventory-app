<?php

namespace App\Domain\Inventory\DTOs\Concerns;

trait BuildsTransactionData
{
    /** @param array<string, mixed> $data */
    protected static function normalizeItems(array $data): array
    {
        return array_values($data['items'] ?? []);
    }
}
