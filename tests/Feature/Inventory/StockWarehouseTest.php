<?php

use App\Domain\Inventory\Enums\StockDirection;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockCard;
use App\Domain\Master\Models\Category;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stockWarehouseUser(object $test, string $role = 'Admin Gudang'): User
{
    $test->seed(RolePermissionSeeder::class);
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole($role);

    return $user;
}

function stockWarehouseFixture(): array
{
    $category = Category::query()->create(['code' => uniqid('CAT'), 'name' => uniqid('Kategori'), 'is_active' => true]);
    $location = Location::query()->create(['code' => uniqid('GDG'), 'name' => uniqid('Gudang'), 'is_active' => true]);
    $item = Item::query()->create([
        'category_id' => $category->id,
        'sku' => uniqid('SKU'),
        'name' => 'Switch Access Test',
        'unit' => 'pcs',
        'minimum_stock' => 5,
        'is_active' => true,
        'is_serialized' => false,
    ]);
    $stock = Stock::query()->create([
        'item_id' => $item->id,
        'location_id' => $location->id,
        'qty_on_hand' => 10,
        'qty_reserved' => 2,
        'last_movement_at' => now(),
    ])->refresh();

    return [$category, $location, $item, $stock];
}

it('shows stock warehouse summary and list', function (): void {
    $admin = stockWarehouseUser($this);
    [, , $item, $stock] = stockWarehouseFixture();

    $response = $this->actingAs($admin)->get(route('stock.index'));

    $response->assertOk()
        ->assertSee('Stock Gudang')
        ->assertSee($item->sku)
        ->assertSee('Switch Access Test')
        ->assertSee('Kartu Stok');

    expect((int) $stock->qty_available)->toBe(8);
});

it('filters stock by category location keyword low and empty status', function (): void {
    $admin = stockWarehouseUser($this);
    [$category, $location, $item] = stockWarehouseFixture();
    $emptyItem = Item::query()->create([
        'category_id' => $category->id,
        'sku' => uniqid('EMPTY'),
        'name' => 'Empty Stock Item',
        'unit' => 'pcs',
        'minimum_stock' => 1,
        'is_active' => true,
        'is_serialized' => false,
    ]);
    Stock::query()->create([
        'item_id' => $emptyItem->id,
        'location_id' => $location->id,
        'qty_on_hand' => 0,
        'qty_reserved' => 0,
    ]);

    $this->actingAs($admin)->get(route('stock.index', [
        'category_id' => $category->id,
        'location_id' => $location->id,
        'keyword' => $item->sku,
    ]))->assertOk()->assertSee($item->sku)->assertDontSee('Empty Stock Item');

    $this->actingAs($admin)->get(route('stock.index', ['stock_status' => 'empty']))
        ->assertOk()
        ->assertSee('Empty Stock Item')
        ->assertDontSee('Switch Access Test');
});

it('shows stock card history with date filter', function (): void {
    $admin = stockWarehouseUser($this);
    [, , $item, $stock] = stockWarehouseFixture();

    StockCard::query()->create([
        'stock_id' => $stock->id,
        'item_id' => $item->id,
        'location_id' => $stock->location_id,
        'trx_date' => now(),
        'direction' => StockDirection::In,
        'movement_type' => StockMovementType::GoodsReceipt,
        'reference_type' => 'goods_receipts',
        'reference_id' => 1,
        'reference_no' => 'BM-20260630-00001',
        'qty' => 10,
        'qty_before' => 0,
        'qty_after' => 10,
        'posted_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->get(route('stock.card', [
        'stock' => $stock,
        'date_from' => now()->subDay()->toDateString(),
        'date_to' => now()->addDay()->toDateString(),
    ]));

    $response->assertOk()
        ->assertSee('Kartu Stok')
        ->assertSee('BM-20260630-00001')
        ->assertSee('goods_receipt')
        ->assertSee($admin->name);
});

it('requires stock view permission for stock pages', function (): void {
    $staff = User::factory()->create(['is_active' => true]);

    $this->actingAs($staff)->get(route('stock.index'))->assertForbidden();
});
