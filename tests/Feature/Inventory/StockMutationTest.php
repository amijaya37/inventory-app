<?php

use App\Actions\Inventory\StockMutation\PostStockMutationAction;
use App\Domain\Inventory\Enums\StockDirection;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockCard;
use App\Domain\Inventory\Models\StockMutation;
use App\Domain\Master\Models\Category;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stockMutationAdmin(object $test): User
{
    $test->seed(RolePermissionSeeder::class);
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Admin Gudang');

    return $user;
}

function stockMutationFixture(int $sourceQty = 10): array
{
    $category = Category::query()->create(['code' => uniqid('CAT'), 'name' => uniqid('Kategori'), 'is_active' => true]);
    $source = Location::query()->create(['code' => uniqid('SRC'), 'name' => 'Gudang Asal', 'is_active' => true]);
    $destination = Location::query()->create(['code' => uniqid('DST'), 'name' => 'Gudang Tujuan', 'is_active' => true]);
    $item = Item::query()->create([
        'category_id' => $category->id,
        'sku' => uniqid('SKU'),
        'name' => 'Mouse Test',
        'unit' => 'pcs',
        'minimum_stock' => 1,
        'is_active' => true,
        'is_serialized' => false,
    ]);
    Stock::query()->create(['item_id' => $item->id, 'location_id' => $source->id, 'qty_on_hand' => $sourceQty, 'qty_reserved' => 0]);

    return [$source, $destination, $item];
}

it('creates mutation draft without moving stock', function (): void {
    $admin = stockMutationAdmin($this);
    [$source, $destination, $item] = stockMutationFixture(8);

    $response = $this->actingAs($admin)->post(route('stock-mutations.store'), [
        'mutation_date' => now()->toDateString(),
        'source_location_id' => $source->id,
        'destination_location_id' => $destination->id,
        'requested_by' => $admin->id,
        'remarks' => 'Mutasi untuk test',
        'items' => [[
            'item_id' => $item->id,
            'qty' => 3,
            'condition_status' => 'layak_pakai',
        ]],
    ]);

    $response->assertRedirect();
    expect((int) Stock::query()->where('item_id', $item->id)->where('location_id', $source->id)->first()?->qty_available)->toBe(8)
        ->and(Stock::query()->where('item_id', $item->id)->where('location_id', $destination->id)->exists())->toBeFalse()
        ->and(StockMutation::query()->first()?->items()->count())->toBe(1);
});

it('posts mutation with atomic out and in stock movements', function (): void {
    $admin = stockMutationAdmin($this);
    [$source, $destination, $item] = stockMutationFixture(10);

    $mutation = StockMutation::query()->create([
        'mutation_no' => 'MT-20260630-00001',
        'mutation_date' => now()->toDateString(),
        'source_location_id' => $source->id,
        'destination_location_id' => $destination->id,
        'requested_by' => $admin->id,
        'remarks' => 'Mutasi test',
        'status' => 'draft',
        'created_by' => $admin->id,
    ]);
    $mutation->items()->create(['item_id' => $item->id, 'qty' => 4, 'condition_status' => 'layak_pakai']);

    app(PostStockMutationAction::class)->execute($mutation, $admin);

    expect((int) Stock::query()->where('item_id', $item->id)->where('location_id', $source->id)->first()?->qty_available)->toBe(6)
        ->and((int) Stock::query()->where('item_id', $item->id)->where('location_id', $destination->id)->first()?->qty_available)->toBe(4)
        ->and(StockCard::query()->where('item_id', $item->id)->where('location_id', $source->id)->where('direction', StockDirection::Out)->where('movement_type', StockMovementType::MutationOut)->count())->toBe(1)
        ->and(StockCard::query()->where('item_id', $item->id)->where('location_id', $destination->id)->where('direction', StockDirection::In)->where('movement_type', StockMovementType::MutationIn)->count())->toBe(1)
        ->and($mutation->fresh()->isPosted())->toBeTrue();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'post',
        'module' => 'stock_mutation',
        'reference_type' => StockMutation::class,
        'reference_id' => $mutation->id,
        'reference_no' => 'MT-20260630-00001',
        'user_id' => $admin->id,
    ]);
});

it('rolls back mutation posting when source stock is insufficient', function (): void {
    $admin = stockMutationAdmin($this);
    [$source, $destination, $item] = stockMutationFixture(2);

    $mutation = StockMutation::query()->create([
        'mutation_no' => 'MT-20260630-00002',
        'mutation_date' => now()->toDateString(),
        'source_location_id' => $source->id,
        'destination_location_id' => $destination->id,
        'requested_by' => $admin->id,
        'remarks' => 'Mutasi gagal',
        'status' => 'draft',
        'created_by' => $admin->id,
    ]);
    $mutation->items()->create(['item_id' => $item->id, 'qty' => 5, 'condition_status' => 'layak_pakai']);

    app(PostStockMutationAction::class)->execute($mutation, $admin);
})->throws(InsufficientStockException::class);

it('blocks mutation when source and destination are the same', function (): void {
    $admin = stockMutationAdmin($this);
    [$source, , $item] = stockMutationFixture(5);

    $response = $this->actingAs($admin)->post(route('stock-mutations.store'), [
        'mutation_date' => now()->toDateString(),
        'source_location_id' => $source->id,
        'destination_location_id' => $source->id,
        'requested_by' => $admin->id,
        'items' => [[
            'item_id' => $item->id,
            'qty' => 1,
            'condition_status' => 'layak_pakai',
        ]],
    ]);

    $response->assertSessionHasErrors('destination_location_id');
});
