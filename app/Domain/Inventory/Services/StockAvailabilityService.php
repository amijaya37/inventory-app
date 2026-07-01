<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Models\Stock;

class StockAvailabilityService
{
    public function availableQty(int $itemId, int $locationId): int
    {
        return (int) (Stock::query()->where('item_id', $itemId)->where('location_id', $locationId)->value('qty_available') ?? 0);
    }

    public function ensureAvailable(int $itemId, int $locationId, int $qty): void
    {
        if ($this->availableQty($itemId, $locationId) < $qty) {
            throw new \RuntimeException('Stok tidak mencukupi. Transaksi dibatalkan.');
        }
    }
}
