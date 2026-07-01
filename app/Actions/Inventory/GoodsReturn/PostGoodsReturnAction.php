<?php

namespace App\Actions\Inventory\GoodsReturn;

use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\GoodsReturn;
use App\Domain\Inventory\Models\GoodsReturnItem;
use App\Domain\Inventory\Services\InventoryAuditService;
use App\Domain\Inventory\Services\StockMovementService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PostGoodsReturnAction
{
    public function __construct(
        private readonly StockMovementService $stockMovementService,
        private readonly InventoryAuditService $auditService,
    ) {}

    public function execute(GoodsReturn $goodsReturn, User $actor): GoodsReturn
    {
        $stockMovementService = $this->stockMovementService;
        $auditService = $this->auditService;

        return DB::transaction(function () use ($goodsReturn, $actor, $stockMovementService, $auditService): GoodsReturn {
            $return = GoodsReturn::query()
                ->whereKey($goodsReturn->id)
                ->lockForUpdate()
                ->with(['items.item'])
                ->firstOrFail();

            if (! $return->isDraft()) {
                throw new RuntimeException('Hanya transaksi draft yang boleh diposting.');
            }

            if ($return->items->isEmpty()) {
                throw new RuntimeException('Detail barang tarikan tidak boleh kosong.');
            }

            foreach ($return->items as $detail) {
                /** @var GoodsReturnItem $detail */
                if (! $detail->shouldReturnToStock()) {
                    continue;
                }

                $stockMovementService->increase(
                    (int) $detail->item_id,
                    (int) $return->warehouse_location_id,
                    (int) $detail->qty,
                    StockMovementType::GoodsReturn,
                    $return,
                    $actor,
                );
            }

            $return->markAsPosted($actor->id);
            $auditService->logPosted('goods_return', $return, $actor);

            return $return->refresh()->load(['items.item', 'items.photos', 'originUser', 'originLocation', 'warehouseLocation', 'postedBy']);
        });
    }
}
