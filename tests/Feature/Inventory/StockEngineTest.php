<?php

use App\Domain\Inventory\Enums\StockDirection;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Exceptions\StockRowNotFoundException;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockCard;
use App\Domain\Inventory\Services\StockEngine;
use App\Domain\Master\Models\Category;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stockEngineFixture(): array
{
    $user = User::factory()->create(['is_active' => true]);
    $category = Category::query()->create(['code' => uniqid('CAT'), 'name' => uniqid('Category'), 'is_active' => true]);
    $item = Item::query()->create(['category_id' => $category->id, 'sku' => uniqid('SKU'), 'name' => 'Router', 'unit' => 'pcs']);
    $from = Location::query()->create(['code' => uniqid('FROM'), 'name' => 'Gudang Pusat']);
    $to = Location::query()->create(['code' => uniqid('TO'), 'name' => 'Gudang Cabang']);

    return [$user, $item, $from, $to];
}

it('increases stock and writes append-only ledger', function (): void {
    [$user, $item, $location] = stockEngineFixture();

    $card = app(StockEngine::class)->increase(
        itemId: $item->id,
        locationId: $location->id,
        qty: 10,
        movementType: StockMovementType::GoodsReceipt,
        referenceType: 'goods_receipts',
        referenceId: 1001,
        referenceNo: 'GR-001',
        postedBy: $user->id,
        unitCost: 125000,
        remarks: 'Posting barang masuk',
    );

    $stock = Stock::query()->where('item_id', $item->id)->where('location_id', $location->id)->firstOrFail();

    expect($stock->qty_on_hand)->toBe(10)
        ->and($stock->qty_reserved)->toBe(0)
        ->and($stock->qty_available)->toBe(10)
        ->and($card->stock_id)->toBe($stock->id)
        ->and($card->direction)->toBe(StockDirection::In)
        ->and($card->movement_type)->toBe(StockMovementType::GoodsReceipt)
        ->and($card->qty)->toBe(10)
        ->and($card->qty_before)->toBe(0)
        ->and($card->qty_after)->toBe(10)
        ->and($card->reference_no)->toBe('GR-001')
        ->and($card->posted_by)->toBe($user->id);
});

it('decreases stock only when available and writes out ledger', function (): void {
    [$user, $item, $location] = stockEngineFixture();

    Stock::query()->create(['item_id' => $item->id, 'location_id' => $location->id, 'qty_on_hand' => 8, 'qty_reserved' => 2]);

    $card = app(StockEngine::class)->decrease(
        itemId: $item->id,
        locationId: $location->id,
        qty: 3,
        movementType: StockMovementType::GoodsIssue,
        referenceType: 'goods_issues',
        referenceId: 2001,
        referenceNo: 'GI-001',
        postedBy: $user->id,
    );

    $stock = Stock::query()->where('item_id', $item->id)->where('location_id', $location->id)->firstOrFail();

    expect($stock->qty_on_hand)->toBe(5)
        ->and($stock->qty_reserved)->toBe(2)
        ->and($stock->qty_available)->toBe(3)
        ->and($card->direction)->toBe(StockDirection::Out)
        ->and($card->qty_before)->toBe(8)
        ->and($card->qty_after)->toBe(5);
});

it('prevents stock from going below available quantity', function (): void {
    [$user, $item, $location] = stockEngineFixture();

    Stock::query()->create(['item_id' => $item->id, 'location_id' => $location->id, 'qty_on_hand' => 3, 'qty_reserved' => 1]);

    app(StockEngine::class)->decrease(
        itemId: $item->id,
        locationId: $location->id,
        qty: 3,
        movementType: StockMovementType::GoodsIssue,
        referenceType: 'goods_issues',
        referenceId: 2002,
        referenceNo: 'GI-002',
        postedBy: $user->id,
    );
})->throws(InsufficientStockException::class, 'Stok tidak mencukupi');

it('fails decrease when stock row does not exist', function (): void {
    [$user, $item, $location] = stockEngineFixture();

    app(StockEngine::class)->decrease(
        itemId: $item->id,
        locationId: $location->id,
        qty: 1,
        movementType: StockMovementType::GoodsIssue,
        referenceType: 'goods_issues',
        referenceId: 2003,
        postedBy: $user->id,
    );
})->throws(StockRowNotFoundException::class);

it('transfers stock between locations and creates two ledger rows', function (): void {
    [$user, $item, $from, $to] = stockEngineFixture();

    Stock::query()->create(['item_id' => $item->id, 'location_id' => $from->id, 'qty_on_hand' => 10, 'qty_reserved' => 0]);

    [$outCard, $inCard] = app(StockEngine::class)->transfer(
        itemId: $item->id,
        fromLocationId: $from->id,
        toLocationId: $to->id,
        qty: 4,
        referenceType: 'stock_mutations',
        referenceId: 3001,
        referenceNo: 'MT-001',
        postedBy: $user->id,
        remarks: 'Mutasi antar gudang',
    );

    expect(Stock::query()->where('item_id', $item->id)->where('location_id', $from->id)->value('qty_on_hand'))->toBe(6)
        ->and(Stock::query()->where('item_id', $item->id)->where('location_id', $to->id)->value('qty_on_hand'))->toBe(4)
        ->and($outCard->direction)->toBe(StockDirection::Out)
        ->and($outCard->movement_type)->toBe(StockMovementType::MutationOut)
        ->and($inCard->direction)->toBe(StockDirection::In)
        ->and($inCard->movement_type)->toBe(StockMovementType::MutationIn)
        ->and(StockCard::query()->where('reference_no', 'MT-001')->count())->toBe(2);
});

it('rejects transfer to the same location and non positive qty', function (): void {
    [$user, $item, $location] = stockEngineFixture();

    app(StockEngine::class)->transfer(
        itemId: $item->id,
        fromLocationId: $location->id,
        toLocationId: $location->id,
        qty: 1,
        referenceType: 'stock_mutations',
        referenceId: 3002,
        postedBy: $user->id,
    );
})->throws(InvalidArgumentException::class, 'Lokasi asal dan tujuan mutasi tidak boleh sama');
