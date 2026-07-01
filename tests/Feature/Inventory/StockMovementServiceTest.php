<?php

use App\Domain\Inventory\Enums\StockDirection;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Exceptions\StockRowNotFoundException;
use App\Domain\Inventory\Models\GoodsReceipt;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Services\StockMovementService;
use App\Domain\Master\Models\Category;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records stock increase in stock balance and stock card ledger', function (): void {
    $actor = User::factory()->create();
    $category = Category::query()->create(['name' => 'Network', 'code' => 'NET']);
    $item = Item::query()->create(['category_id' => $category->id, 'sku' => 'RTR-001', 'name' => 'Router', 'unit' => 'pcs']);
    $location = Location::query()->create(['name' => 'Gudang IT', 'code' => 'GDG-IT']);
    $receipt = GoodsReceipt::query()->create([
        'receipt_no' => 'GR-TEST-001',
        'receipt_date' => now()->toDateString(),
        'warehouse_location_id' => $location->id,
        'created_by' => $actor->id,
    ]);

    $card = app(StockMovementService::class)->increase(
        itemId: $item->id,
        locationId: $location->id,
        qty: 5,
        movementType: StockMovementType::GoodsReceipt,
        reference: $receipt,
        actor: $actor,
        unitCost: 100000,
    );

    expect(Stock::query()->where('item_id', $item->id)->where('location_id', $location->id)->value('qty_on_hand'))->toBe(5)
        ->and($card->direction)->toBe(StockDirection::In)
        ->and($card->qty_before)->toBe(0)
        ->and($card->qty_after)->toBe(5);
});

it('prevents negative stock on decrease', function (): void {
    $actor = User::factory()->create();
    $category = Category::query()->create(['name' => 'Endpoint', 'code' => 'END']);
    $item = Item::query()->create(['category_id' => $category->id, 'sku' => 'NB-001', 'name' => 'Notebook', 'unit' => 'pcs']);
    $location = Location::query()->create(['name' => 'Gudang HO', 'code' => 'GDG-HO']);
    $receipt = GoodsReceipt::query()->create([
        'receipt_no' => 'GR-TEST-002',
        'receipt_date' => now()->toDateString(),
        'warehouse_location_id' => $location->id,
        'created_by' => $actor->id,
    ]);

    app(StockMovementService::class)->decrease(
        itemId: $item->id,
        locationId: $location->id,
        qty: 1,
        movementType: StockMovementType::GoodsIssue,
        reference: $receipt,
        actor: $actor,
    );
})->throws(StockRowNotFoundException::class);
