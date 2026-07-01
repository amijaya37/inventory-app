# Step 5 — Master Data Kategori

Step ini menerapkan CRUD penuh untuk Master Kategori sebagai pola awal modul master data Inventory Stock IT.

## Komponen yang diterapkan

- Migration `categories` dilengkapi:
  - `code` unique max 30
  - `name` unique max 100
  - `description`
  - `is_active` indexed
  - `created_by`
  - `updated_by`
  - `softDeletes()`
- Model domain `App\Domain\Master\Models\Category` dilengkapi:
  - fillable
  - casts `is_active`
  - soft delete
  - relasi `items`, `creator`, `updater`
  - scope `active()`
  - scope `search()`
- Form Request:
  - `StoreCategoryRequest`
  - `UpdateCategoryRequest`
- Controller:
  - `App\Http\Controllers\Web\Master\CategoryController`
- Blade views:
  - `resources/views/master-data/categories/index.blade.php`
  - `resources/views/master-data/categories/create.blade.php`
  - `resources/views/master-data/categories/edit.blade.php`
  - `resources/views/master-data/categories/_form.blade.php`
- Route eksplisit untuk CRUD kategori dengan permission middleware.
- Seeder contoh kategori IT.
- Feature test CRUD kategori.

## Route

```text
GET     /categories                 categories.index    categories.view
GET     /categories/create          categories.create   categories.create
POST    /categories                 categories.store    categories.create
GET     /categories/{category}/edit categories.edit     categories.update
PUT     /categories/{category}      categories.update   categories.update
DELETE  /categories/{category}      categories.destroy  categories.delete
```

## Seeder kategori awal

Seeder:

```text
database/seeders/CategorySeeder.php
```

Data awal:

- TONER — Toner Printer
- STORAGE — Storage
- KEYBOARD — Keyboard
- MOUSE — Mouse
- MONITOR — Monitor
- TV — TV
- NETWORK — Network Device
- SPAREPART — Sparepart IT
- CONSUMABLE — Consumable IT

## Delete strategy

- Jika kategori belum dipakai item: kategori di-soft delete.
- Jika kategori sudah dipakai item: kategori tidak dihapus, hanya `is_active = false`.

## Test

Test dibuat di:

```text
tests/Feature/Inventory/CategoryCrudTest.php
```

Skenario:

- Admin Gudang bisa melihat index kategori.
- Admin Gudang bisa membuat kategori.
- Validasi unique `code` dan `name` berjalan.
- Admin Gudang bisa update kategori.
- Kategori tanpa relasi item terkena soft delete.
- Kategori dengan relasi item hanya dinonaktifkan.
- Staff IT bisa view tetapi tidak bisa create kategori.

## Verification gate

```bash
php artisan migrate:fresh --seed
php artisan test --compact
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=1G
npm run build
```

Status: semua lulus.
