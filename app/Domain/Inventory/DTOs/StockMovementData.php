<?php

namespace App\Domain\Inventory\DTOs;

use App\Domain\Inventory\Enums\StockDirection;
use App\Domain\Inventory\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;

final readonly class StockMovementData
{
    public function __construct(
        public int $itemId,
        public int $locationId,
        public int $qty,
        public StockDirection $direction,
        public StockMovementType $movementType,
        public Model $reference,
        public ?float $unitCost = null,
    ) {}
}
