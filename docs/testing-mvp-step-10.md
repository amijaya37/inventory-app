# Step 10 — MVP Test Plan & Coverage

Dokumen ini memetakan requirement `step-10-chatgpt.md` ke test suite yang berjalan di repo.

## Test command wajib sebelum lanjut step berikutnya

```bash
composer lint:check
vendor/bin/phpstan analyse --memory-limit=1G
php artisan test
npm run build
```

## Coverage matrix

| Area | Test file | Coverage utama |
|---|---|---|
| Auth login/logout/session | `tests/Feature/Auth/*` | Login valid, password salah, logout, email verification, 2FA challenge |
| Dashboard auth guard | `tests/Feature/DashboardTest.php` | Guest redirect login, authenticated dashboard access |
| Role/permission | `tests/Feature/Inventory/RolePermissionAccessTest.php` | Role MVP, akses Admin Gudang, Staff IT forbidden, Manager forbidden posting, inactive user |
| Master kategori | `tests/Feature/Inventory/CategoryCrudTest.php` | Create/update/unique validation/soft delete/deactivate/authorization |
| Stock engine core | `tests/Feature/Inventory/StockEngineTest.php` | Increase/decrease/transfer, anti stok minus, ledger append-only |
| Stock movement wrapper | `tests/Feature/Inventory/StockMovementServiceTest.php` | Service mencatat saldo + stock card, negative stock ditolak |
| Barang masuk | `tests/Feature/Inventory/GoodsReceiptTest.php` | Draft tidak mengubah stok, posting tambah stok, stock card IN, audit log post, anti double post, serial validation |
| Barang keluar | `tests/Feature/Inventory/GoodsIssueTest.php` | Draft tidak mengurangi stok, posting mengurangi stok, stock card OUT, audit log post, insufficient stock rollback, anti repost |
| Barang tarikan | `tests/Feature/Inventory/GoodsReturnTest.php` | Draft tidak menambah stok, return_to_stock tambah stok, scrap tidak masuk stok, audit log post, validation final action |
| Mutasi | `tests/Feature/Inventory/StockMutationTest.php` | Draft tidak pindah stok, atomic OUT+IN, audit log post, insufficient stock rollback, lokasi sama ditolak |
| Stock gudang/kartu stok | `tests/Feature/Inventory/StockWarehouseTest.php` | Summary stok, filter lokasi/kategori/keyword/low/empty, kartu stok date filter, permission stock view |
| Laporan/export | `tests/Feature/Reports/InventoryReportTest.php` | View laporan stock/goods-in/goods-out, export permission, Excel download response |
| Dokumen private | `tests/Feature/TransactionDocumentTest.php` | Upload private disk + audit, forbidden download tanpa permission, audit download |
| Settings baseline | `tests/Feature/Settings/*` | Profile/security/password baseline dari starter kit |

## Acceptance criteria Step 10

- Semua critical/high test lulus.
- Transaksi stok tidak bisa membuat stok minus.
- Posting barang masuk/keluar/tarikan/mutasi membuat `stock_cards` sesuai arah transaksi.
- Posting transaksi penting membuat `audit_logs` dengan event `post`.
- Export laporan hanya untuk user dengan `reports.export`.
- Upload dokumen masuk private disk dan download diaudit.
- Static analysis dan lint lulus.
- Build frontend lulus.

## Gap yang sengaja belum dibesarkan di MVP local

- CRUD item/supplier/location lengkap belum dibuat UI-nya; route saat ini masih placeholder/dashboard untuk sebagian master data. Test khusus dapat ditambah saat modul CRUD master data tersebut diimplementasikan.
- Excel content-level assertion belum dibedah cell-by-cell; saat ini test memastikan permission dan download response. Jika butuh audit export detail, gunakan `Excel::fake()`/temporary file assertion pada iterasi hardening.
