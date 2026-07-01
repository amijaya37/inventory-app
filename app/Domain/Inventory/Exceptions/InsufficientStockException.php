<?php

namespace App\Domain\Inventory\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public static function make(int $itemId, int $locationId, int $requestedQty, int $availableQty): self
    {
        return new self("Stok tidak mencukupi. Item: {$itemId}, lokasi: {$locationId}, diminta: {$requestedQty}, tersedia: {$availableQty}.");
    }
}
