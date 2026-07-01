<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Enums\StockDirection;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Exceptions\StockRowNotFoundException;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockCard;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockEngine
{
    public function increase(
        int $itemId,
        int $locationId,
        int $qty,
        StockMovementType $movementType,
        string $referenceType,
        int $referenceId,
        ?string $referenceNo = null,
        ?int $postedBy = null,
        ?float $unitCost = null,
        ?string $remarks = null,
    ): StockCard {
        $this->validatePositiveQty($qty);

        return DB::transaction(function () use ($itemId, $locationId, $qty, $movementType, $referenceType, $referenceId, $referenceNo, $postedBy, $unitCost, $remarks): StockCard {
            $stock = $this->lockStock($itemId, $locationId, createIfMissing: true);
            $qtyBefore = $stock->qty_on_hand;
            $qtyAfter = $qtyBefore + $qty;

            $stock->forceFill([
                'qty_on_hand' => $qtyAfter,
                'last_movement_at' => now(),
            ])->save();

            return $this->createCard($stock, StockDirection::In, $movementType, $qty, $qtyBefore, $qtyAfter, $referenceType, $referenceId, $referenceNo, $postedBy, $unitCost, $remarks);
        }, attempts: 3);
    }

    public function decrease(
        int $itemId,
        int $locationId,
        int $qty,
        StockMovementType $movementType,
        string $referenceType,
        int $referenceId,
        ?string $referenceNo = null,
        ?int $postedBy = null,
        ?float $unitCost = null,
        ?string $remarks = null,
    ): StockCard {
        $this->validatePositiveQty($qty);

        return DB::transaction(function () use ($itemId, $locationId, $qty, $movementType, $referenceType, $referenceId, $referenceNo, $postedBy, $unitCost, $remarks): StockCard {
            $stock = $this->lockStock($itemId, $locationId, createIfMissing: false);
            $availableQty = $stock->qty_on_hand - $stock->qty_reserved;

            if ($availableQty < $qty) {
                throw InsufficientStockException::make($itemId, $locationId, $qty, $availableQty);
            }

            $qtyBefore = $stock->qty_on_hand;
            $qtyAfter = $qtyBefore - $qty;

            $stock->forceFill([
                'qty_on_hand' => $qtyAfter,
                'last_movement_at' => now(),
            ])->save();

            return $this->createCard($stock, StockDirection::Out, $movementType, $qty, $qtyBefore, $qtyAfter, $referenceType, $referenceId, $referenceNo, $postedBy, $unitCost, $remarks);
        }, attempts: 3);
    }

    /** @return array{0: StockCard, 1: StockCard} */
    public function transfer(
        int $itemId,
        int $fromLocationId,
        int $toLocationId,
        int $qty,
        string $referenceType,
        int $referenceId,
        ?string $referenceNo = null,
        ?int $postedBy = null,
        ?string $remarks = null,
    ): array {
        $this->validatePositiveQty($qty);

        if ($fromLocationId === $toLocationId) {
            throw new InvalidArgumentException('Lokasi asal dan tujuan mutasi tidak boleh sama.');
        }

        return DB::transaction(function () use ($itemId, $fromLocationId, $toLocationId, $qty, $referenceType, $referenceId, $referenceNo, $postedBy, $remarks): array {
            $this->ensureStockRow($itemId, $toLocationId);

            $stocks = Stock::query()
                ->where('item_id', $itemId)
                ->whereIn('location_id', [$fromLocationId, $toLocationId])
                ->orderBy('location_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('location_id');

            $sourceStock = $stocks->get($fromLocationId);
            $targetStock = $stocks->get($toLocationId);

            if (! $sourceStock instanceof Stock) {
                throw StockRowNotFoundException::make($itemId, $fromLocationId);
            }

            if (! $targetStock instanceof Stock) {
                throw StockRowNotFoundException::make($itemId, $toLocationId);
            }

            $availableQty = $sourceStock->qty_on_hand - $sourceStock->qty_reserved;
            if ($availableQty < $qty) {
                throw InsufficientStockException::make($itemId, $fromLocationId, $qty, $availableQty);
            }

            $sourceBefore = $sourceStock->qty_on_hand;
            $sourceAfter = $sourceBefore - $qty;
            $targetBefore = $targetStock->qty_on_hand;
            $targetAfter = $targetBefore + $qty;

            $sourceStock->forceFill(['qty_on_hand' => $sourceAfter, 'last_movement_at' => now()])->save();
            $targetStock->forceFill(['qty_on_hand' => $targetAfter, 'last_movement_at' => now()])->save();

            $outCard = $this->createCard($sourceStock, StockDirection::Out, StockMovementType::MutationOut, $qty, $sourceBefore, $sourceAfter, $referenceType, $referenceId, $referenceNo, $postedBy, null, $remarks);
            $inCard = $this->createCard($targetStock, StockDirection::In, StockMovementType::MutationIn, $qty, $targetBefore, $targetAfter, $referenceType, $referenceId, $referenceNo, $postedBy, null, $remarks);

            return [$outCard, $inCard];
        }, attempts: 3);
    }

    public function ensureStockRow(int $itemId, int $locationId): void
    {
        Stock::query()->insertOrIgnore([
            'item_id' => $itemId,
            'location_id' => $locationId,
            'qty_on_hand' => 0,
            'qty_reserved' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function validatePositiveQty(int $qty): void
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Qty harus lebih besar dari 0.');
        }
    }

    private function lockStock(int $itemId, int $locationId, bool $createIfMissing): Stock
    {
        if ($createIfMissing) {
            $this->ensureStockRow($itemId, $locationId);
        }

        $stock = Stock::query()
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if (! $stock instanceof Stock) {
            throw StockRowNotFoundException::make($itemId, $locationId);
        }

        return $stock;
    }

    private function createCard(
        Stock $stock,
        StockDirection $direction,
        StockMovementType $movementType,
        int $qty,
        int $qtyBefore,
        int $qtyAfter,
        string $referenceType,
        int $referenceId,
        ?string $referenceNo,
        ?int $postedBy,
        ?float $unitCost,
        ?string $remarks,
    ): StockCard {
        return StockCard::query()->create([
            'stock_id' => $stock->id,
            'item_id' => $stock->item_id,
            'location_id' => $stock->location_id,
            'trx_date' => now(),
            'direction' => $direction->value,
            'movement_type' => $movementType->value,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reference_no' => $referenceNo,
            'qty' => $qty,
            'qty_before' => $qtyBefore,
            'qty_after' => $qtyAfter,
            'unit_cost' => $unitCost,
            'remarks' => $remarks,
            'posted_by' => $postedBy,
        ]);
    }
}
