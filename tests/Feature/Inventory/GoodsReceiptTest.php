<?php

use App\Domain\Inventory\Enums\TransactionStatus;
use App\Domain\Inventory\Models\GoodsReceipt;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockCard;
use App\Domain\Master\Models\Category;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Domain\Master\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function goodsReceiptAdmin(object $test): User
{
    $test->seed(RolePermissionSeeder::class);
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Admin Gudang');

    return $user;
}

function goodsReceiptFixture(): array
{
    $category = Category::query()->create(['code' => uniqid('CAT'), 'name' => uniqid('Category'), 'is_active' => true]);
    $supplier = Supplier::query()->create(['code' => uniqid('SUP'), 'name' => 'Supplier Test', 'is_active' => true]);
    $location = Location::query()->create(['code' => uniqid('GDG'), 'name' => 'Gudang IT', 'is_active' => true]);
    $item = Item::query()->create(['category_id' => $category->id, 'sku' => uniqid('SKU'), 'name' => 'Router Test', 'unit' => 'pcs', 'is_active' => true, 'is_serialized' => false]);

    return [$supplier, $location, $item];
}

it('admin can create goods receipt as draft without increasing stock', function (): void {
    $admin = goodsReceiptAdmin($this);
    [$supplier, $location, $item] = goodsReceiptFixture();

    $response = $this->actingAs($admin)->post(route('goods-receipts.store'), [
        'supplier_id' => $supplier->id,
        'warehouse_location_id' => $location->id,
        'receipt_date' => now()->toDateString(),
        'po_no' => 'PO-001',
        'invoice_no' => 'INV-001',
        'items' => [[
            'item_id' => $item->id,
            'qty' => 5,
            'unit_price' => 100000,
            'condition_status' => 'new',
        ]],
    ]);

    $receipt = GoodsReceipt::query()->firstOrFail();

    $response->assertRedirect(route('goods-receipts.show', $receipt));
    expect($receipt->receipt_no)->toStartWith('BM-')
        ->and($receipt->status)->toBe(TransactionStatus::Draft)
        ->and((float) $receipt->total_amount)->toBe(500000.0);

    $this->assertDatabaseHas('goods_receipt_items', [
        'goods_receipt_id' => $receipt->id,
        'item_id' => $item->id,
        'qty' => 5,
        'total_price' => 500000,
    ]);
    expect(Stock::query()->count())->toBe(0)
        ->and(StockCard::query()->count())->toBe(0);
});

it('posting goods receipt increases stock and writes stock card', function (): void {
    $admin = goodsReceiptAdmin($this);
    [$supplier, $location, $item] = goodsReceiptFixture();

    $receipt = GoodsReceipt::query()->create([
        'receipt_no' => 'BM-20260629-00001',
        'source_type' => 'purchase',
        'supplier_id' => $supplier->id,
        'warehouse_location_id' => $location->id,
        'receipt_date' => now()->toDateString(),
        'status' => TransactionStatus::Draft,
        'created_by' => $admin->id,
    ]);
    $receipt->items()->create([
        'item_id' => $item->id,
        'qty' => 10,
        'unit_price' => 50000,
        'total_price' => 500000,
        'condition_status' => 'new',
    ]);

    $response = $this->actingAs($admin)->post(route('goods-receipts.post', $receipt));

    $response->assertRedirect(route('goods-receipts.show', $receipt));
    $receipt->refresh();

    expect($receipt->status)->toBe(TransactionStatus::Posted)
        ->and($receipt->posted_by)->toBe($admin->id)
        ->and($receipt->posted_at)->not->toBeNull();

    $this->assertDatabaseHas('stocks', [
        'item_id' => $item->id,
        'location_id' => $location->id,
        'qty_on_hand' => 10,
    ]);
    $this->assertDatabaseHas('stock_cards', [
        'reference_type' => 'goods_receipts',
        'reference_id' => $receipt->id,
        'reference_no' => 'BM-20260629-00001',
        'item_id' => $item->id,
        'location_id' => $location->id,
        'direction' => 'in',
        'movement_type' => 'goods_receipt',
        'qty' => 10,
        'qty_before' => 0,
        'qty_after' => 10,
        'posted_by' => $admin->id,
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'event' => 'post',
        'module' => 'goods_receipt',
        'reference_type' => GoodsReceipt::class,
        'reference_id' => $receipt->id,
        'reference_no' => 'BM-20260629-00001',
        'user_id' => $admin->id,
    ]);
});

it('cannot post goods receipt twice', function (): void {
    $admin = goodsReceiptAdmin($this);
    [$supplier, $location, $item] = goodsReceiptFixture();

    $receipt = GoodsReceipt::query()->create([
        'receipt_no' => 'BM-20260629-00002',
        'source_type' => 'purchase',
        'supplier_id' => $supplier->id,
        'warehouse_location_id' => $location->id,
        'receipt_date' => now()->toDateString(),
        'status' => TransactionStatus::Posted,
        'created_by' => $admin->id,
        'posted_by' => $admin->id,
        'posted_at' => now(),
    ]);
    $receipt->items()->create([
        'item_id' => $item->id,
        'qty' => 1,
        'unit_price' => 10000,
        'total_price' => 10000,
        'condition_status' => 'new',
    ]);

    $response = $this->actingAs($admin)->post(route('goods-receipts.post', $receipt));

    $response->assertSessionHasErrors('posting');
    expect(Stock::query()->count())->toBe(0)
        ->and(StockCard::query()->count())->toBe(0);
});

it('validates serialized item serial numbers', function (): void {
    $admin = goodsReceiptAdmin($this);
    [$supplier, $location, $item] = goodsReceiptFixture();
    $item->forceFill(['is_serialized' => true])->save();

    $response = $this->actingAs($admin)->post(route('goods-receipts.store'), [
        'supplier_id' => $supplier->id,
        'warehouse_location_id' => $location->id,
        'receipt_date' => now()->toDateString(),
        'items' => [[
            'item_id' => $item->id,
            'qty' => 2,
            'unit_price' => 100000,
            'condition_status' => 'new',
            'serial_numbers' => ['SN-001'],
        ]],
    ]);

    $response->assertSessionHasErrors('items.0.serial_numbers');
});
