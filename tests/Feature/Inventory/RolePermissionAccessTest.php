<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('seeds the three MVP roles with granular permissions', function (): void {
    expect(spatieRole('Admin Gudang')->hasPermissionTo('goods-in.post'))->toBeTrue()
        ->and(spatieRole('Staff IT')->hasPermissionTo('returns.create'))->toBeTrue()
        ->and(spatieRole('Staff IT')->hasPermissionTo('goods-in.post'))->toBeFalse()
        ->and(spatieRole('Manager')->hasPermissionTo('reports.export'))->toBeTrue()
        ->and(spatieRole('Manager')->hasPermissionTo('mutations.post'))->toBeFalse();
});

it('admin gudang can access protected inventory routes', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Admin Gudang');

    $this->actingAs($user)
        ->get(route('items.index'))
        ->assertOk();
});

it('staff it cannot create master items', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Staff IT');

    $this->actingAs($user)
        ->get(route('items.create'))
        ->assertForbidden();
});

it('manager cannot post stock transactions', function (): void {
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Manager');

    expect($user->can('goods-in.post'))->toBeFalse()
        ->and($user->can('goods-out.post'))->toBeFalse()
        ->and($user->can('mutations.post'))->toBeFalse();
});

it('inactive user is logged out and forbidden from protected route', function (): void {
    $user = User::factory()->create(['is_active' => false]);
    $user->assignRole('Admin Gudang');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();

    $this->assertGuest();
});

function spatieRole(string $name): Role
{
    return Role::findByName($name);
}
