<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public const PERMISSIONS = ['dashboard.view', 'dashboard.operational', 'dashboard.managerial', 'items.view', 'items.create', 'items.update', 'items.delete', 'items.import', 'items.export', 'categories.view', 'categories.create', 'categories.update', 'categories.delete', 'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete', 'locations.view', 'locations.create', 'locations.update', 'locations.delete', 'users.view', 'users.create', 'users.update', 'users.deactivate', 'users.reset-password', 'users.assign-role', 'goods-in.view', 'goods-in.create', 'goods-in.update', 'goods-in.delete', 'goods-in.post', 'goods-in.print', 'goods-in.export', 'goods-out.view', 'goods-out.create', 'goods-out.update', 'goods-out.delete', 'goods-out.post', 'goods-out.print', 'goods-out.export', 'returns.view', 'returns.create', 'returns.update', 'returns.delete', 'returns.verify', 'returns.post', 'returns.print', 'returns.export', 'mutations.view', 'mutations.create', 'mutations.update', 'mutations.delete', 'mutations.post', 'mutations.print', 'mutations.export', 'stock.view', 'stock.card', 'stock.export', 'stock.recalculate', 'reports.view', 'reports.stock', 'reports.goods-in', 'reports.goods-out', 'reports.returns', 'reports.mutations', 'reports.export', 'audit-log.view', 'audit-log.export', 'documents.view', 'documents.upload', 'documents.download', 'documents.delete', 'goods-receipts.documents.upload', 'goods-receipts.documents.download', 'goods-issues.documents.upload', 'goods-issues.documents.download', 'roles.view', 'roles.manage'];

    public const ADMIN_GUDANG_PERMISSIONS = ['dashboard.view', 'dashboard.operational', 'dashboard.managerial', 'items.view', 'items.create', 'items.update', 'items.delete', 'items.import', 'items.export', 'categories.view', 'categories.create', 'categories.update', 'categories.delete', 'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete', 'locations.view', 'locations.create', 'locations.update', 'locations.delete', 'users.view', 'users.create', 'users.update', 'users.deactivate', 'users.reset-password', 'users.assign-role', 'goods-in.view', 'goods-in.create', 'goods-in.update', 'goods-in.delete', 'goods-in.post', 'goods-in.print', 'goods-in.export', 'goods-out.view', 'goods-out.create', 'goods-out.update', 'goods-out.delete', 'goods-out.post', 'goods-out.print', 'goods-out.export', 'returns.view', 'returns.create', 'returns.update', 'returns.delete', 'returns.verify', 'returns.post', 'returns.print', 'returns.export', 'mutations.view', 'mutations.create', 'mutations.update', 'mutations.delete', 'mutations.post', 'mutations.print', 'mutations.export', 'stock.view', 'stock.card', 'stock.export', 'stock.recalculate', 'reports.view', 'reports.stock', 'reports.goods-in', 'reports.goods-out', 'reports.returns', 'reports.mutations', 'reports.export', 'audit-log.view', 'documents.view', 'documents.upload', 'documents.download', 'documents.delete', 'goods-receipts.documents.upload', 'goods-receipts.documents.download', 'goods-issues.documents.upload', 'goods-issues.documents.download'];

    public const STAFF_IT_PERMISSIONS = ['dashboard.view', 'items.view', 'categories.view', 'locations.view', 'goods-in.view', 'goods-out.view', 'returns.view', 'returns.create', 'returns.update', 'mutations.view', 'stock.view', 'stock.card', 'reports.view', 'reports.stock', 'documents.view', 'documents.upload', 'documents.download'];

    public const MANAGER_PERMISSIONS = ['dashboard.view', 'dashboard.managerial', 'items.view', 'items.export', 'categories.view', 'suppliers.view', 'locations.view', 'users.view', 'goods-in.view', 'goods-in.print', 'goods-in.export', 'goods-out.view', 'goods-out.print', 'goods-out.export', 'returns.view', 'returns.print', 'returns.export', 'mutations.view', 'mutations.print', 'mutations.export', 'stock.view', 'stock.card', 'stock.export', 'reports.view', 'reports.stock', 'reports.goods-in', 'reports.goods-out', 'reports.returns', 'reports.mutations', 'reports.export', 'audit-log.view', 'audit-log.export', 'documents.view', 'documents.download', 'goods-receipts.documents.download', 'goods-issues.documents.download'];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $guard = 'web';
        foreach (self::PERMISSIONS as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }
        Role::query()->firstOrCreate(['name' => 'Admin Gudang', 'guard_name' => $guard])->syncPermissions(self::ADMIN_GUDANG_PERMISSIONS);
        Role::query()->firstOrCreate(['name' => 'Staff IT', 'guard_name' => $guard])->syncPermissions(self::STAFF_IT_PERMISSIONS);
        Role::query()->firstOrCreate(['name' => 'Manager', 'guard_name' => $guard])->syncPermissions(self::MANAGER_PERMISSIONS);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
