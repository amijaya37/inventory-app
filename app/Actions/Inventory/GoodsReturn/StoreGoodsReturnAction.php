<?php

namespace App\Actions\Inventory\GoodsReturn;

use App\Domain\Inventory\Enums\TransactionStatus;
use App\Domain\Inventory\Models\GoodsReturn;
use App\Domain\Inventory\Models\GoodsReturnItem;
use App\Domain\Inventory\Services\DocumentNumberService;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StoreGoodsReturnAction
{
    public function __construct(private readonly DocumentNumberService $numberService) {}

    /** @param array<string, mixed> $payload */
    public function execute(array $payload, User $actor): GoodsReturn
    {
        return DB::transaction(function () use ($payload, $actor): GoodsReturn {
            $goodsReturn = GoodsReturn::query()->create([
                'return_no' => $this->numberService->next('goods_return'),
                'return_date' => $payload['return_date'],
                'origin_type' => $payload['origin_type'],
                'origin_user_id' => $payload['origin_user_id'] ?? null,
                'origin_location_id' => $payload['origin_location_id'] ?? null,
                'origin_pic_name' => $payload['origin_pic_name'],
                'origin_pic_phone' => $payload['origin_pic_phone'] ?? null,
                'warehouse_location_id' => $payload['warehouse_location_id'],
                'return_reason' => $payload['return_reason'],
                'status' => TransactionStatus::Draft,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
                'remarks' => $payload['remarks'] ?? null,
            ]);

            foreach ($payload['items'] as $line) {
                /** @var GoodsReturnItem $returnItem */
                $returnItem = $goodsReturn->items()->create([
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                    'serial_no' => $line['serial_no'] ?? null,
                    'condition_status' => $line['condition_status'],
                    'final_action' => $line['final_action'],
                    'notes' => $line['notes'] ?? null,
                ]);

                foreach (($line['photos'] ?? []) as $photo) {
                    if (! $photo instanceof UploadedFile) {
                        continue;
                    }

                    $path = $photo->store('documents/goods-returns/photos', 'local');
                    $returnItem->photos()->create([
                        'file_name' => $photo->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $photo->getMimeType(),
                        'file_size' => $photo->getSize(),
                        'uploaded_by' => $actor->id,
                    ]);
                }
            }

            return $goodsReturn->load(['items.item', 'items.photos']);
        });
    }
}
