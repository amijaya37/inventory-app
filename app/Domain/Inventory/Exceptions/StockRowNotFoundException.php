<?php

namespace App\Domain\Inventory\Exceptions;

use RuntimeException;

class StockRowNotFoundException extends RuntimeException
{
    public static function make(int $itemId, int $locationId): self
    {
        return new self("Saldo stok tidak ditemukan untuk item {$itemId} di lokasi {$locationId}.");
    }
}
