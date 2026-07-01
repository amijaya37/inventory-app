# Step 4 — Authentication & RBAC Spatie

Step ini menerapkan fondasi authentication dan authorization untuk Inventory Stock IT.

## Komponen yang diterapkan

- Auth starter kit Laravel/Fortify yang sudah ada dari bootstrap project tetap digunakan.
- `spatie/laravel-permission` digunakan sebagai RBAC utama.
- Model `User` memakai trait `HasRoles`.
- Field user internal ditambahkan:
  - `username`
  - `employee_no`
  - `location_id`
  - `is_active`
  - `last_login_at`
- Middleware `active.user` dibuat untuk memblokir user nonaktif.
- Middleware Spatie didaftarkan di `bootstrap/app.php`:
  - `role`
  - `permission`
  - `role_or_permission`
- Route dashboard dan placeholder route modul dilindungi permission.
- Sidebar memakai Blade `@can` / `@canany`.

## Role MVP

- Admin Gudang
- Staff IT
- Manager

## Seeder

Seeder yang dibuat:

```text
database/seeders/RolePermissionSeeder.php
database/seeders/InitialUserSeeder.php
```

`DatabaseSeeder` menjalankan keduanya.

Akun awal lokal:

| Role | Email | Username | Password |
|---|---|---|---|
| Admin Gudang | admin.gudang@example.local | admin.gudang | Password123! |
| Staff IT | staff.it@example.local | staff.it | Password123! |
| Manager | manager@example.local | manager | Password123! |

Catatan: password default ini hanya untuk lokal/testing. Untuk production wajib diganti.

## Permission penting

Contoh permission granular yang sudah diseed:

- `dashboard.view`
- `items.view`, `items.create`, `items.update`, `items.delete`
- `goods-in.view`, `goods-in.create`, `goods-in.post`
- `goods-out.view`, `goods-out.create`, `goods-out.post`
- `returns.view`, `returns.create`, `returns.post`
- `mutations.view`, `mutations.create`, `mutations.post`
- `stock.view`, `stock.card`, `stock.export`
- `reports.view`, `reports.export`
- `audit-log.view`, `audit-log.export`
- `documents.upload`, `documents.download`

## Policy scaffold

Policy scaffold dibuat untuk:

- Item
- Category
- Supplier
- Location
- User
- GoodsReceipt
- GoodsIssue
- GoodsReturn
- StockMutation
- Stock
- Document
- AuditLog

Detail business rule yang lebih spesifik akan diperdalam saat Step 5–7 ketika CRUD dan transaksi aktual dibuat.

## Test Step 4

Test RBAC dibuat di:

```text
tests/Feature/Inventory/RolePermissionAccessTest.php
```

Skenario:

- role MVP dan permission granular berhasil diseed,
- Admin Gudang bisa akses route inventory protected,
- Staff IT tidak bisa create master item,
- Manager tidak bisa posting transaksi stok,
- user nonaktif logout otomatis dan mendapat forbidden.
