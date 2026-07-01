<?php

namespace App\Actions\Inventory\GoodsReceipt;

use App\Domain\Inventory\DTOs\GoodsReceiptData;
use App\Domain\Inventory\Enums\TransactionStatus;
use App\Domain\Inventory\Models\GoodsReceipt;
use App\Domain\Inventory\Services\DocumentNumberService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreGoodsReceiptAction
{
    public function __construct(private DocumentNumberService $documentNumberService) {}

    public function execute(GoodsReceiptData $data, User $actor, ?string $poFilePath = null, ?string $invoiceFilePath = null, ?string $remarks = null, ?string $purchaseDate = null): GoodsReceipt
    {
        return DB::transaction(function () use ($data, $actor, $poFilePath, $invoiceFilePath, $remarks, $purchaseDate): GoodsReceipt {
            $goodsReceipt = GoodsReceipt::query()->create([
                'receipt_no' => $this->documentNumberService->next('goods_receipt'),
                'source_type' => $data->sourceType ?: 'purchase',
                'supplier_id' => $data->supplierId,
                'warehouse_location_id' => $data->warehouseLocationId,
                'po_no' => $data->poNo,
                'invoice_no' => $data->invoiceNo,
                'purchase_date' => $purchaseDate,
                'receipt_date' => $data->receiptDate,
                'po_file_path' => $poFilePath,
                'invoice_file_path' => $invoiceFilePath,
                'status' => TransactionStatus::Draft,
                'remarks' => $remarks,
                'created_by' => $actor->id,
            ]);

            $totalAmount = 0;
            foreach ($data->items as $item) {
                $qty = (int) $item['qty'];
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $totalPrice = $qty * $unitPrice;

                $goodsReceipt->items()->create([
                    'item_id' => $item['item_id'],
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'serial_numbers' => $item['serial_numbers'] ?? null,
                    'warranty_until' => $item['warranty_until'] ?? null,
                    'condition_status' => $item['condition_status'] ?? 'new',
                    'notes' => $item['notes'] ?? null,
                ]);

                $totalAmount += $totalPrice;
            }

            $goodsReceipt->forceFill(['total_amount' => $totalAmount])->save();

            return $goodsReceipt->refresh();
        });
    }
}
