<?php

namespace App\Actions\Inventory\GoodsIssue;

use App\Domain\Inventory\Enums\TransactionStatus;
use App\Domain\Inventory\Models\GoodsIssue;
use App\Domain\Inventory\Services\DocumentNumberService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreGoodsIssueAction
{
    public function __construct(private readonly DocumentNumberService $numberService) {}

    /** @param array<string, mixed> $payload */
    public function execute(array $payload, User $actor): GoodsIssue
    {
        return DB::transaction(function () use ($payload, $actor): GoodsIssue {
            $goodsIssue = GoodsIssue::query()->create([
                'issue_no' => $this->numberService->next('goods_issue'),
                'issue_date' => $payload['issue_date'],
                'source_location_id' => $payload['source_location_id'],
                'recipient_type' => $payload['recipient_type'],
                'recipient_user_id' => $payload['recipient_user_id'] ?? null,
                'recipient_name' => $payload['recipient_name'],
                'recipient_department' => $payload['recipient_department'] ?? null,
                'recipient_phone' => $payload['recipient_phone'] ?? null,
                'target_location_id' => $payload['target_location_id'] ?? null,
                'pic_user_id' => $payload['pic_user_id'],
                'requested_by' => $actor->id,
                'status' => TransactionStatus::Draft,
                'remarks' => $payload['remarks'] ?? null,
            ]);

            foreach ($payload['items'] as $line) {
                $goodsIssue->items()->create([
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                    'serial_no' => $line['serial_no'] ?? null,
                    'condition_status' => $line['condition_status'] ?? 'good',
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            return $goodsIssue->load('items.item');
        });
    }
}
