<?php

use App\Actions\Inventory\GoodsReturn\PostGoodsReturnAction;
use App\Domain\Inventory\Enums\StockDirection;
use App\Domain\Inventory\Models\GoodsReturn;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockCard;
use App\Domain\Master\Models\Category;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function goodsReturnAdmin(object $test): User
{
    $test->seed(RolePermissionSeeder::class);
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Admin Gudang');

    return $user;
}

function goodsReturnFixture(): array
{
    $category = Category::query()->create(['code' => uniqid('CAT'), 'name' => uniqid('Kategori'), 'is_active' => true]);
    $warehouse = Location::query()->create(['code' => uniqid('WH'), 'name' => 'Gudang IT', 'is_active' => true]);
    $origin = Location::query()->create(['code' => uniqid('ORG'), 'name' => 'Area Office', 'is_active' => true]);
    $item = Item::query()->create([
        'category_id' => $category->id,
        'sku' => uniqid('SKU'),
        'name' => 'Keyboard Test',
        'unit' => 'pcs',
        'minimum_stock' => 1,
        'is_active' => true,
        'is_serialized' => false,
    ]);

    return [$warehouse, $origin, $item];
}

it('creates goods return draft without adding stock', function (): void {
    $admin = goodsReturnAdmin($this);
    [$warehouse, $origin, $item] = goodsReturnFixture();

    $response = $this->actingAs($admin)->post(route('goods-returns.store'), [
        'return_date' => now()->toDateString(),
        'origin_type' => 'location',
        'origin_location_id' => $origin->id,
        'origin_pic_name' => 'PIC Lapangan',
        'warehouse_location_id' => $warehouse->id,
        'return_reason' => 'Perangkat ditarik dari user',
        'items' => [[
            'item_id' => $item->id,
            'qty' => 2,
            'condition_status' => 'layak_pakai',
            'final_action' => 'return_to_stock',
        ]],
    ]);

    $response->assertRedirect();
    expect(Stock::query()->where('item_id', $item->id)->where('location_id', $warehouse->id)->exists())->toBeFalse()
        ->and(GoodsReturn::query()->first()?->items()->count())->toBe(1);
});

it('posts return_to_stock item and increases warehouse stock', function (): void {
    $admin = goodsReturnAdmin($this);
    [$warehouse, $origin, $item] = goodsReturnFixture();

    $return = GoodsReturn::query()->create([
        'return_no' => 'BT-20260630-00001',
        'return_date' => now()->toDateString(),
        'origin_type' => 'location',
        'origin_location_id' => $origin->id,
        'origin_pic_name' => 'PIC Lapangan',
        'warehouse_location_id' => $warehouse->id,
        'return_reason' => 'Tarikan perangkat',
        'status' => 'draft',
        'created_by' => $admin->id,
    ]);
    $return->items()->create(['item_id' => $item->id, 'qty' => 3, 'condition_status' => 'layak_pakai', 'final_action' => 'return_to_stock']);

    app(PostGoodsReturnAction::class)->execute($return, $admin);

    $stock = Stock::query()->where('item_id', $item->id)->where('location_id', $warehouse->id)->first();
    expect((int) $stock?->qty_available)->toBe(3)
        ->and(StockCard::query()->where('item_id', $item->id)->where('direction', StockDirection::In)->count())->toBe(1)
        ->and($return->fresh()->isPosted())->toBeTrue();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'post',
        'module' => 'goods_return',
        'reference_type' => GoodsReturn::class,
        'reference_id' => $return->id,
        'reference_no' => 'BT-20260630-00001',
        'user_id' => $admin->id,
    ]);
});

it('does not add stock for scrap final action', function (): void {
    $admin = goodsReturnAdmin($this);
    [$warehouse, $origin, $item] = goodsReturnFixture();

    $return = GoodsReturn::query()->create([
        'return_no' => 'BT-20260630-00002',
        'return_date' => now()->toDateString(),
        'origin_type' => 'location',
        'origin_location_id' => $origin->id,
        'origin_pic_name' => 'PIC Lapangan',
        'warehouse_location_id' => $warehouse->id,
        'return_reason' => 'Tarikan scrap',
        'status' => 'draft',
        'created_by' => $admin->id,
    ]);
    $return->items()->create(['item_id' => $item->id, 'qty' => 5, 'condition_status' => 'scrap', 'final_action' => 'scrap']);

    app(PostGoodsReturnAction::class)->execute($return, $admin);

    expect(Stock::query()->where('item_id', $item->id)->where('location_id', $warehouse->id)->exists())->toBeFalse()
        ->and(StockCard::query()->count())->toBe(0)
        ->and($return->fresh()->isPosted())->toBeTrue();
});

it('blocks invalid scrap return_to_stock validation', function (): void {
    $admin = goodsReturnAdmin($this);
    [$warehouse, $origin, $item] = goodsReturnFixture();

    $response = $this->actingAs($admin)->post(route('goods-returns.store'), [
        'return_date' => now()->toDateString(),
        'origin_type' => 'location',
        'origin_location_id' => $origin->id,
        'origin_pic_name' => 'PIC Lapangan',
        'warehouse_location_id' => $warehouse->id,
        'return_reason' => 'Tarikan scrap',
        'items' => [[
            'item_id' => $item->id,
            'qty' => 1,
            'condition_status' => 'scrap',
            'final_action' => 'return_to_stock',
        ]],
    ]);

    $response->assertSessionHasErrors('items.0.final_action');
});
