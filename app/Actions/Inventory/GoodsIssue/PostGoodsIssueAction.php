<?php

namespace App\Actions\Inventory\GoodsIssue;

use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\GoodsIssue;
use App\Domain\Inventory\Services\InventoryAuditService;
use App\Domain\Inventory\Services\StockMovementService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PostGoodsIssueAction
{
    public function __construct(
        private readonly StockMovementService $stockMovementService,
        private readonly InventoryAuditService $auditService,
    ) {}

    public function execute(GoodsIssue $goodsIssue, User $actor): GoodsIssue
    {
        $stockMovementService = $this->stockMovementService;
        $auditService = $this->auditService;

        return DB::transaction(function () use ($goodsIssue, $actor, $stockMovementService, $auditService): GoodsIssue {
            $issue = GoodsIssue::query()
                ->whereKey($goodsIssue->id)
                ->lockForUpdate()
                ->with(['items.item'])
                ->firstOrFail();

            if (! $issue->isDraft()) {
                throw new RuntimeException('Hanya transaksi draft yang boleh diposting.');
            }

            if ($issue->items->isEmpty()) {
                throw new RuntimeException('Detail barang keluar tidak boleh kosong.');
            }

            foreach ($issue->items as $detail) {
                $stockMovementService->decrease(
                    (int) $detail->item_id,
                    (int) $issue->source_location_id,
                    (int) $detail->qty,
                    StockMovementType::GoodsIssue,
                    $issue,
                    $actor,
                );
            }

            $documentNo = $issue->document_no ?: str_replace('BK-', 'ST-', (string) $issue->issue_no);
            $issue->markAsPosted($actor->id, $documentNo);
            $auditService->logPosted('goods_issue', $issue, $actor);

            return $issue->refresh()->load(['items.item', 'sourceLocation', 'targetLocation', 'picUser', 'recipientUser', 'postedBy']);
        });
    }
}
