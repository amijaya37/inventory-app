# Step 7C — Barang Keluar / Goods Issue

Step ini mengaktifkan modul Barang Keluar dari draft sampai posting dan dokumen serah terima.

## Komponen yang diterapkan

- Migration:
  - `database/migrations/2026_06_29_152632_create_goods_issues_table.php`
  - `database/migrations/2026_06_29_152635_create_goods_issue_items_table.php`
- Models:
  - `app/Domain/Inventory/Models/GoodsIssue.php`
  - `app/Domain/Inventory/Models/GoodsIssueItem.php`
- Requests:
  - `app/Http/Requests/Transaction/StoreGoodsIssueRequest.php`
  - `app/Http/Requests/Transaction/PostGoodsIssueRequest.php`
- Actions:
  - `app/Actions/Inventory/GoodsIssue/StoreGoodsIssueAction.php`
  - `app/Actions/Inventory/GoodsIssue/PostGoodsIssueAction.php`
- Services:
  - `app/Domain/Inventory/Services/GoodsIssueDocumentService.php`
- Controller:
  - `app/Http/Controllers/Web/Transaction/GoodsIssueController.php`
- Views:
  - `resources/views/transactions/goods-issues/index.blade.php`
  - `resources/views/transactions/goods-issues/create.blade.php`
  - `resources/views/transactions/goods-issues/show.blade.php`
  - `resources/views/transactions/goods-issues/handover-pdf.blade.php`
- Test:
  - `tests/Feature/Inventory/GoodsIssueTest.php`

## Flow utama

1. User membuat draft Barang Keluar.
2. Draft mendapat nomor transaksi `BK-YYYYMMDD-00001`.
3. Draft tidak mengubah stok.
4. User posting draft.
5. Sistem lock transaksi dan stock row lewat `StockEngine`.
6. Jika stok tidak cukup, posting gagal dan stok tetap.
7. Jika stok cukup, `qty_on_hand` berkurang dan `stock_cards` dibuat dengan direction `out`.
8. Status berubah menjadi `posted`.
9. Nomor dokumen serah terima dibuat dengan prefix `ST-`.
10. PDF serah terima dibuat di private storage.

## Routes

- `GET /goods-issues` → `goods-issues.index`
- `GET /goods-issues/create` → `goods-issues.create`
- `POST /goods-issues` → `goods-issues.store`
- `GET /goods-issues/{goodsIssue}` → `goods-issues.show`
- `POST /goods-issues/{goodsIssue}/post` → `goods-issues.post`
- `GET /goods-issues/{goodsIssue}/handover` → `goods-issues.handover`

## Permission

Mengikuti permission existing Step 4:

- `goods-out.view`
- `goods-out.create`
- `goods-out.post`

## Catatan teknis

- Struktur tetap di `app/Domain/...`, bukan `app/Models/...`.
- Posting memakai `PostGoodsIssueAction`, bukan controller langsung update stock.
- Pengurangan stok memakai `StockMovementService::decrease()` yang meneruskan ke `StockEngine` Step 6.
- `qty_available` adalah generated column; tidak pernah ditulis manual.
- Dokumen serah terima menggunakan nomor `ST-...` dari nomor transaksi `BK-...` agar tidak perlu migration `document_sequences` baru pada step ini.
- Form create menyediakan 5 baris item statis; request membersihkan baris kosong sebelum validasi.

## Test coverage

`tests/Feature/Inventory/GoodsIssueTest.php` mencakup:

- membuat draft barang keluar tanpa mengurangi stok,
- posting barang keluar mengurangi stok dan menulis stock card out,
- posting ditolak jika stok tidak cukup dan transaksi tetap draft,
- transaksi posted tidak bisa diposting ulang.

## Verification gate

Lulus:

```bash
php artisan migrate:fresh --seed
php artisan test --compact
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=1G
npm run build
```

Hasil utama:

- Tests: 65 passed / 201 assertions
- Pint: PASS 191 files
- PHPStan: No errors
- Vite build: berhasil
