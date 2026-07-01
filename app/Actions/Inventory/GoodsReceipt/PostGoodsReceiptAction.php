<?php

namespace App\Actions\Inventory\GoodsReceipt;

use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\GoodsReceipt;
use App\Domain\Inventory\Services\InventoryAuditService;
use App\Domain\Inventory\Services\StockMovementService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PostGoodsReceiptAction
{
    public function __construct(private StockMovementService $stockMovementService, private InventoryAuditService $auditService) {}

    public function execute(GoodsReceipt $goodsReceipt, User $actor): GoodsReceipt
    {
        $stockMovementService = $this->stockMovementService;
        $auditService = $this->auditService;

        return DB::transaction(function () use ($goodsReceipt, $actor, $stockMovementService, $auditService): GoodsReceipt {
            $goodsReceipt = GoodsReceipt::query()
                ->with('items')
                ->whereKey($goodsReceipt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $goodsReceipt->isDraft()) {
                throw new RuntimeException('Hanya transaksi draft yang boleh diposting.');
            }

            if ($goodsReceipt->items->isEmpty()) {
                throw new RuntimeException('Transaksi barang masuk tidak memiliki detail barang.');
            }

            foreach ($goodsReceipt->items as $detail) {
                $stockMovementService->increase(
                    $detail->item_id,
                    $goodsReceipt->warehouse_location_id,
                    $detail->qty,
                    StockMovementType::GoodsReceipt,
                    $goodsReceipt,
                    $actor,
                    $detail->unit_price !== null ? (float) $detail->unit_price : null,
                );
            }

            $goodsReceipt->markAsPosted($actor->id);
            $auditService->logPosted('goods_receipt', $goodsReceipt, $actor);

            return $goodsReceipt->refresh();
        }, attempts: 3);
    }
}
