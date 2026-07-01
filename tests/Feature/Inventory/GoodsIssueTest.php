<?php

use App\Actions\Inventory\GoodsIssue\PostGoodsIssueAction;
use App\Domain\Inventory\Enums\StockDirection;
use App\Domain\Inventory\Models\GoodsIssue;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockCard;
use App\Domain\Master\Models\Category;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function goodsIssueAdmin(object $test): User
{
    $test->seed(RolePermissionSeeder::class);
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Admin Gudang');

    return $user;
}

function goodsIssueFixture(int $qty = 10): array
{
    $category = Category::query()->create(['code' => uniqid('CAT'), 'name' => uniqid('Kategori'), 'is_active' => true]);
    $source = Location::query()->create(['code' => uniqid('SRC'), 'name' => 'Gudang IT', 'is_active' => true]);
    $target = Location::query()->create(['code' => uniqid('DST'), 'name' => 'Area Office', 'is_active' => true]);
    $item = Item::query()->create([
        'category_id' => $category->id,
        'sku' => uniqid('SKU'),
        'name' => 'Laptop Test',
        'unit' => 'pcs',
        'minimum_stock' => 1,
        'is_active' => true,
        'is_serialized' => false,
    ]);
    $stock = Stock::query()->create([
        'item_id' => $item->id,
        'location_id' => $source->id,
        'qty_on_hand' => $qty,
        'qty_reserved' => 0,
        'last_movement_at' => now(),
    ])->refresh();

    return [$source, $target, $item, $stock];
}

it('creates goods issue draft without decreasing stock', function (): void {
    $admin = goodsIssueAdmin($this);
    [$source, $target, $item, $stock] = goodsIssueFixture();

    $response = $this->actingAs($admin)->post(route('goods-issues.store'), [
        'issue_date' => now()->toDateString(),
        'source_location_id' => $source->id,
        'recipient_type' => 'location',
        'recipient_name' => 'IT Support Area 1',
        'target_location_id' => $target->id,
        'pic_user_id' => $admin->id,
        'items' => [[
            'item_id' => $item->id,
            'qty' => 3,
            'condition_status' => 'good',
        ]],
    ]);

    $response->assertRedirect();
    expect((int) $stock->fresh()->qty_available)->toBe(10);
    expect(GoodsIssue::query()->first()?->items()->count())->toBe(1);
});

it('posts goods issue and decreases stock with stock card out', function (): void {
    $admin = goodsIssueAdmin($this);
    [$source, $target, $item, $stock] = goodsIssueFixture();

    $issue = GoodsIssue::query()->create([
        'issue_no' => 'BK-20260630-00001',
        'issue_date' => now()->toDateString(),
        'source_location_id' => $source->id,
        'recipient_type' => 'location',
        'recipient_name' => 'IT Support Area 1',
        'target_location_id' => $target->id,
        'pic_user_id' => $admin->id,
        'requested_by' => $admin->id,
        'status' => 'draft',
    ]);
    $issue->items()->create(['item_id' => $item->id, 'qty' => 4, 'condition_status' => 'good']);

    app(PostGoodsIssueAction::class)->execute($issue, $admin);

    expect((int) $stock->fresh()->qty_available)->toBe(6)
        ->and(StockCard::query()->where('item_id', $item->id)->where('direction', StockDirection::Out)->count())->toBe(1)
        ->and($issue->fresh()->isPosted())->toBeTrue()
        ->and($issue->fresh()->document_no)->toStartWith('ST-');

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'post',
        'module' => 'goods_issue',
        'reference_type' => GoodsIssue::class,
        'reference_id' => $issue->id,
        'reference_no' => 'BK-20260630-00001',
        'user_id' => $admin->id,
    ]);
});

it('rejects posting if stock is insufficient and keeps draft unchanged', function (): void {
    $admin = goodsIssueAdmin($this);
    [$source, $target, $item, $stock] = goodsIssueFixture(2);

    $issue = GoodsIssue::query()->create([
        'issue_no' => 'BK-20260630-00002',
        'issue_date' => now()->toDateString(),
        'source_location_id' => $source->id,
        'recipient_type' => 'location',
        'recipient_name' => 'IT Support Area 1',
        'target_location_id' => $target->id,
        'pic_user_id' => $admin->id,
        'requested_by' => $admin->id,
        'status' => 'draft',
    ]);
    $issue->items()->create(['item_id' => $item->id, 'qty' => 5, 'condition_status' => 'good']);

    try {
        app(PostGoodsIssueAction::class)->execute($issue, $admin);
        $this->fail('Posting seharusnya gagal karena stok tidak cukup.');
    } catch (Throwable $exception) {
        expect($exception->getMessage())->not->toBe('');
    }

    expect((int) $stock->fresh()->qty_available)->toBe(2)
        ->and($issue->fresh()->isDraft())->toBeTrue()
        ->and(StockCard::query()->count())->toBe(0);
});

it('prevents reposting posted goods issue', function (): void {
    $admin = goodsIssueAdmin($this);
    [$source, $target, $item] = goodsIssueFixture();

    $issue = GoodsIssue::query()->create([
        'issue_no' => 'BK-20260630-00003',
        'issue_date' => now()->toDateString(),
        'source_location_id' => $source->id,
        'recipient_type' => 'location',
        'recipient_name' => 'IT Support Area 1',
        'target_location_id' => $target->id,
        'pic_user_id' => $admin->id,
        'requested_by' => $admin->id,
        'status' => 'posted',
        'document_no' => 'ST-20260630-00003',
    ]);
    $issue->items()->create(['item_id' => $item->id, 'qty' => 1, 'condition_status' => 'good']);

    $this->expectException(RuntimeException::class);
    app(PostGoodsIssueAction::class)->execute($issue, $admin);
});
