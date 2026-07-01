<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\StockCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StockMovementService
{
    public function __construct(
        private readonly StockEngine $stockEngine,
    ) {}

    public function increase(int $itemId, int $locationId, int $qty, StockMovementType $movementType, Model $reference, User $actor, ?float $unitCost = null): StockCard
    {
        return $this->stockEngine->increase(
            itemId: $itemId,
            locationId: $locationId,
            qty: $qty,
            movementType: $movementType,
            referenceType: $reference->getTable(),
            referenceId: (int) $reference->getKey(),
            referenceNo: $this->referenceNo($reference),
            postedBy: $actor->id,
            unitCost: $unitCost,
        );
    }

    public function decrease(int $itemId, int $locationId, int $qty, StockMovementType $movementType, Model $reference, User $actor, ?float $unitCost = null): StockCard
    {
        return $this->stockEngine->decrease(
            itemId: $itemId,
            locationId: $locationId,
            qty: $qty,
            movementType: $movementType,
            referenceType: $reference->getTable(),
            referenceId: (int) $reference->getKey(),
            referenceNo: $this->referenceNo($reference),
            postedBy: $actor->id,
            unitCost: $unitCost,
        );
    }

    private function referenceNo(Model $reference): ?string
    {
        foreach (['receipt_no', 'issue_no', 'return_no', 'mutation_no', 'document_no'] as $field) {
            $value = $reference->getAttribute($field);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
