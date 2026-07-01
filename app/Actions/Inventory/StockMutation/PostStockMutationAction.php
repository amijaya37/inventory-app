<?php

namespace App\Actions\Inventory\StockMutation;

use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\StockMutation;
use App\Domain\Inventory\Models\StockMutationItem;
use App\Domain\Inventory\Services\InventoryAuditService;
use App\Domain\Inventory\Services\StockMovementService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostStockMutationAction
{
    public function __construct(
        private readonly StockMovementService $stockMovementService,
        private readonly InventoryAuditService $auditService,
    ) {}

    public function execute(StockMutation $stockMutation, User $actor): StockMutation
    {
        $stockMovementService = $this->stockMovementService;
        $auditService = $this->auditService;

        return DB::transaction(function () use ($stockMutation, $actor, $stockMovementService, $auditService): StockMutation {
            $mutation = StockMutation::query()
                ->whereKey($stockMutation->id)
                ->lockForUpdate()
                ->with(['items.item'])
                ->firstOrFail();

            if ((int) $mutation->source_location_id === (int) $mutation->destination_location_id) {
                throw ValidationException::withMessages(['destination_location_id' => 'Lokasi asal dan tujuan tidak boleh sama.']);
            }

            if (! $mutation->isDraft()) {
                throw ValidationException::withMessages(['status' => 'Hanya transaksi draft yang boleh diposting.']);
            }

            if ($mutation->items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Detail mutasi barang tidak boleh kosong.']);
            }

            $groupedItems = $mutation->items
                ->groupBy('item_id')
                ->map(fn ($rows) => $rows->sum('qty'));

            foreach ($groupedItems as $itemId => $qty) {
                $sourceDetail = new StockMutationItem([
                    'item_id' => (int) $itemId,
                    'qty' => (int) $qty,
                ]);

                $stockMovementService->decrease(
                    (int) $sourceDetail->item_id,
                    (int) $mutation->source_location_id,
                    (int) $sourceDetail->qty,
                    StockMovementType::MutationOut,
                    $mutation,
                    $actor,
                );

                $stockMovementService->increase(
                    (int) $sourceDetail->item_id,
                    (int) $mutation->destination_location_id,
                    (int) $sourceDetail->qty,
                    StockMovementType::MutationIn,
                    $mutation,
                    $actor,
                );
            }

            $mutation->markAsPosted($actor->id);
            $auditService->logPosted('stock_mutation', $mutation, $actor);

            return $mutation->refresh()->load(['items.item', 'sourceLocation', 'destinationLocation', 'requester', 'postedBy']);
        });
    }
}
