<?php

namespace App\Actions\Inventory\StockMutation;

use App\Domain\Inventory\Enums\TransactionStatus;
use App\Domain\Inventory\Models\StockMutation;
use App\Domain\Inventory\Services\DocumentNumberService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreStockMutationAction
{
    public function __construct(private readonly DocumentNumberService $numberService) {}

    /** @param array<string, mixed> $payload */
    public function execute(array $payload, User $actor): StockMutation
    {
        return DB::transaction(function () use ($payload, $actor): StockMutation {
            $mutation = StockMutation::query()->create([
                'mutation_no' => $this->numberService->next('stock_mutation'),
                'mutation_date' => $payload['mutation_date'],
                'source_location_id' => $payload['source_location_id'],
                'destination_location_id' => $payload['destination_location_id'],
                'requested_by' => $payload['requested_by'] ?? $actor->id,
                'status' => TransactionStatus::Draft,
                'remarks' => $payload['remarks'] ?? null,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            foreach ($payload['items'] as $line) {
                $mutation->items()->create([
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                    'serial_no' => $line['serial_no'] ?? null,
                    'condition_status' => $line['condition_status'] ?? 'layak_pakai',
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            return $mutation->load(['items.item', 'sourceLocation', 'destinationLocation', 'requester']);
        });
    }
}
