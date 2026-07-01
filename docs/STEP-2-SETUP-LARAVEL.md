# Step 2 — Setup Laravel, Dependency, Environment, dan Struktur Folder

Tanggal eksekusi: 2026-06-29  
Workspace: `/Users/amijaya/Projects/inventory-app`

## 1. Target Stack

Berdasarkan `step-2-chatgpt.md`, stack MVP yang disiapkan:

- PHP 8.4+ — lokal tersedia PHP 8.5.6.
- Laravel 13 — terpasang Laravel Framework 13.17.0.
- MySQL 8+ — lokal tersedia MySQL Homebrew dan server aktif.
- Blade + Tailwind + Livewire starter kit.
- Pest untuk testing.
- Pint untuk code style.
- Larastan/PHPStan untuk static analysis.

## 2. Bootstrap Project

Laravel project dibuat memakai Laravel Installer dengan opsi:

```bash
laravel new <temp> --livewire --pest --database=mysql --npm --git --no-boost --no-interaction
```

Hasil bootstrap disalin ke existing folder:

```text
/Users/amijaya/Projects/inventory-app
```

Dokumen Step 1, blueprint HTML, dan assets blueprint tetap dipertahankan.

## 3. Database Lokal

Database dibuat:

- `inventory_stock_it`
- `inventory_stock_it_testing`

User lokal:

- username: `inv_app`
- password: `InvLocal2026`

Hak akses:

- `inventory_stock_it`: SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES.
- `inventory_stock_it_testing`: ALL PRIVILEGES untuk testing lokal.

## 4. Environment

File yang disiapkan:

- `.env`
- `.env.testing`

Konfigurasi utama:

```dotenv
APP_NAME="Inventory Stock IT"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_stock_it
DB_USERNAME=inv_app
DB_PASSWORD=InvLocal2026
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

## 5. Package Terpasang

Berhasil terpasang:

- `spatie/laravel-permission` — role dan permission.
- `spatie/laravel-activitylog` — audit log.
- `barryvdh/laravel-dompdf` — PDF sederhana.
- `intervention/image-laravel` — optimasi gambar.
- `laravel/sanctum` — API token opsional dari `php artisan install:api`.
- `laravel/pint` — code style.
- `larastan/larastan` — static analysis.

Package yang belum bisa dipasang:

- `maatwebsite/excel`

Alasan: versi stable `maatwebsite/excel` saat ini belum kompatibel dengan kombinasi Laravel 13/PHP 8.5 karena dependency `phpoffice/phpspreadsheet` membatasi PHP `<8.5.0`. Untuk Step 2 package ini dicatat sebagai blocker non-kritis dan akan dipasang nanti jika sudah ada versi kompatibel, atau diganti dengan export berbasis `phpoffice/phpspreadsheet` versi baru/CSV pada Step laporan.

## 6. Publish Config/Migration

Sudah dipublish:

- `config/permission.php`
- migration Spatie Permission
- migration Spatie Activitylog
- `config/dompdf.php`
- `config/image.php`
- `routes/api.php`
- migration Sanctum personal access tokens

## 7. Struktur Folder Domain

Struktur utama yang dibuat:

```text
app/Domain/Inventory/{Models,Actions,DTOs,Enums,Policies,Services,Queries}
app/Domain/Master/{Models,Actions,Services}
app/Domain/Document/{Models,Services}
app/Domain/Report/{Exports,Services}
app/Http/Controllers/Web/{Dashboard,Master,Transaction,Stock,Report,Setting}
app/Http/Controllers/Api/V1
app/Http/Requests/{Master,Transaction,Document}
app/Support/{Numbering,Stock,Uploads}
resources/views/{layouts,components,dashboard,master,transactions,stock,reports,settings}
storage/app/private/{documents,exports,backups}
```

## 8. File Awal yang Dibuat

Model domain awal:

- Category
- Item
- Supplier
- Location
- Stock
- StockCard
- GoodsReceipt
- GoodsReceiptItem
- GoodsIssue
- GoodsIssueItem
- GoodsReturn
- GoodsReturnItem
- StockMutation
- StockMutationItem
- Document

Controller web awal:

- DashboardController
- CategoryController
- ItemController
- SupplierController
- LocationController
- GoodsReceiptController
- GoodsIssueController
- GoodsReturnController
- StockMutationController
- StockController
- ReportController

Form Request awal:

- Store/Update item/category/supplier/location
- Store/Post goods receipt
- Store/Post goods issue
- Store/Post goods return
- Store/Post stock mutation
- Store document

Blade awal:

- `resources/views/layouts/app.blade.php`
- `resources/views/components/sidebar-link.blade.php`
- `resources/views/dashboard/index.blade.php`

## 9. Catatan Teknis

Laravel 13 generator awalnya membuat model ke `app/Models/Domain/...`. File tersebut sudah dipindahkan dan namespace diperbaiki ke struktur target:

```text
app/Domain/...
```

Test starter awal gagal karena `tests/Pest.php` belum memakai `RefreshDatabase`, sementara `phpunit.xml` memakai SQLite `:memory:`. Sudah diperbaiki dengan mengaktifkan:

```php
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
```

Larastan awal menemukan 1 error di `config/sanctum.php` karena `env()` bisa bernilai `false|string`. Sudah diperbaiki dengan cast `(string)`.

## 10. Hasil Verifikasi

Command verifikasi yang sudah lulus:

```bash
php artisan migrate --force
npm run build
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=1G
```

Hasil:

- Laravel Framework 13.17.0 aktif.
- Migration lokal berhasil.
- Build Vite berhasil.
- Test Pest: 33 passed, 81 assertions.
- Pint: PASS, 139 files.
- PHPStan/Larastan: No errors.

## 11. Status Step 2

Step 2 selesai dengan 1 catatan blocker non-kritis: `maatwebsite/excel` belum kompatibel dengan PHP 8.5/Laravel 13 saat eksekusi ini.

Project siap masuk Step 3: arsitektur kode Laravel/domain pattern.
