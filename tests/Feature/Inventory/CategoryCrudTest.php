<?php

use App\Domain\Master\Models\Category;
use App\Domain\Master\Models\Item;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function categoryAdmin(): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Admin Gudang');

    return $user;
}

function categoryStaff(): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Staff IT');

    return $user;
}

it('admin can view category index', function (): void {
    Category::query()->create(['code' => 'TONER', 'name' => 'Toner Printer', 'is_active' => true]);

    $this->actingAs(categoryAdmin())
        ->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Master Kategori')
        ->assertSee('TONER');
});

it('admin can create category', function (): void {
    $this->actingAs(categoryAdmin())
        ->post(route('categories.store'), [
            'code' => 'NETWORK',
            'name' => 'Network Device',
            'description' => 'Perangkat jaringan',
            'is_active' => '1',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'code' => 'NETWORK',
        'name' => 'Network Device',
        'is_active' => true,
    ]);
});

it('validates unique category code and name', function (): void {
    Category::query()->create(['code' => 'TONER', 'name' => 'Toner Printer', 'is_active' => true]);

    $this->actingAs(categoryAdmin())
        ->from(route('categories.create'))
        ->post(route('categories.store'), [
            'code' => 'TONER',
            'name' => 'Toner Printer',
            'is_active' => '1',
        ])
        ->assertRedirect(route('categories.create'))
        ->assertSessionHasErrors(['code', 'name']);
});

it('admin can update category', function (): void {
    $category = Category::query()->create(['code' => 'MOUSE', 'name' => 'Mouse', 'is_active' => true]);

    $this->actingAs(categoryAdmin())
        ->put(route('categories.update', $category), [
            'code' => 'MOUSE',
            'name' => 'Mouse Wireless',
            'description' => 'Mouse kantor',
            'is_active' => '0',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Mouse Wireless',
        'is_active' => false,
    ]);
});

it('soft deletes category without linked items', function (): void {
    $category = Category::query()->create(['code' => 'TV', 'name' => 'TV', 'is_active' => true]);

    $this->actingAs(categoryAdmin())
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'));

    expect(Category::withTrashed()->find($category->id)?->trashed())->toBeTrue();
});

it('only deactivates category when linked to items', function (): void {
    $category = Category::query()->create(['code' => 'MONITOR', 'name' => 'Monitor', 'is_active' => true]);
    Item::query()->create(['category_id' => $category->id, 'sku' => 'MON-001', 'name' => 'Monitor 24 inch']);

    $this->actingAs(categoryAdmin())
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'));

    $category->refresh();

    expect($category->is_active)->toBeFalse()
        ->and($category->trashed())->toBeFalse();
});

it('staff can view but cannot create category', function (): void {
    $staff = categoryStaff();

    $this->actingAs($staff)
        ->get(route('categories.index'))
        ->assertOk();

    $this->actingAs($staff)
        ->get(route('categories.create'))
        ->assertForbidden();
});
