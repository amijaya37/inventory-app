# Step 7D — Barang Tarikan / Goods Return

Step ini mengaktifkan modul Barang Tarikan untuk mencatat barang IT yang ditarik dari user/lokasi, dengan stok bertambah hanya jika final action `return_to_stock`.

## Komponen yang diterapkan

- Migration:
  - `database/migrations/2026_06_29_152632_create_goods_returns_table.php`
  - `database/migrations/2026_06_29_152636_create_goods_return_items_table.php`
  - `database/migrations/2026_06_29_152636_create_goods_return_photos_table.php`
- Enum:
  - `app/Domain/Inventory/Enums/ReturnFinalAction.php`
- Models:
  - `app/Domain/Inventory/Models/GoodsReturn.php`
  - `app/Domain/Inventory/Models/GoodsReturnItem.php`
  - `app/Domain/Inventory/Models/GoodsReturnPhoto.php`
- Requests:
  - `app/Http/Requests/Transaction/StoreGoodsReturnRequest.php`
  - `app/Http/Requests/Transaction/PostGoodsReturnRequest.php`
- Actions:
  - `app/Actions/Inventory/GoodsReturn/StoreGoodsReturnAction.php`
  - `app/Actions/Inventory/GoodsReturn/PostGoodsReturnAction.php`
- Controller:
  - `app/Http/Controllers/Web/Transaction/GoodsReturnController.php`
- Views:
  - `resources/views/transactions/goods-returns/index.blade.php`
  - `resources/views/transactions/goods-returns/create.blade.php`
  - `resources/views/transactions/goods-returns/show.blade.php`
- Test:
  - `tests/Feature/Inventory/GoodsReturnTest.php`

## Flow utama

1. User membuat draft Barang Tarikan.
2. Draft mendapat nomor transaksi `BT-YYYYMMDD-00001` dari `DocumentNumberService`.
3. Draft tidak mengubah stok.
4. User posting draft.
5. Sistem lock transaksi dengan `DB::transaction()`.
6. Setiap detail dicek `final_action`.
7. Hanya detail dengan `return_to_stock` yang memanggil `StockMovementService::increase()`.
8. Detail dengan `repair`, `scrap`, atau `dispose` hanya tercatat sebagai keputusan gudang dan tidak menambah stock.
9. Status berubah menjadi `posted` dan audit log dicatat.

## Routes

- `GET /goods-returns` → `goods-returns.index`
- `GET /goods-returns/create` → `goods-returns.create`
- `POST /goods-returns` → `goods-returns.store`
- `GET /goods-returns/{goodsReturn}` → `goods-returns.show`
- `POST /goods-returns/{goodsReturn}/post` → `goods-returns.post`

## Permission

Mengikuti permission existing Step 4:

- `returns.view`
- `returns.create`
- `returns.post`

## Validasi bisnis

- `origin_type` hanya `user` atau `location`.
- Jika asal user, `origin_user_id` wajib.
- Jika asal lokasi, `origin_location_id` wajib.
- Barang serialized wajib serial number.
- Kondisi `scrap` tidak boleh `return_to_stock`.
- Kondisi `rusak_berat` tidak boleh langsung `return_to_stock`.
- Foto item optional, maksimal 5 file per item, format `jpg/jpeg/png/webp`, maksimal 2 MB.

## Catatan teknis

- Struktur tetap di `app/Domain/...`.
- `StockMovementType::GoodsReturn` bernilai `return_in` sesuai Step 6.
- `qty_available` tetap generated column dan tidak ditulis manual.
- Upload foto memakai disk `local` di path `documents/goods-returns/photos` agar private.
- Form create memakai 5 baris item statis dan request membersihkan baris kosong sebelum validasi.

## Test coverage

`tests/Feature/Inventory/GoodsReturnTest.php` mencakup:

- membuat draft barang tarikan tanpa menambah stok,
- posting item `return_to_stock` menambah stock gudang dan membuat stock card in,
- posting item `scrap` tidak menambah stock dan tidak membuat stock card,
- validasi mencegah `scrap + return_to_stock`.

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

- Tests: 69 passed / 212 assertions
- Pint: PASS 194 files
- PHPStan: No errors
- Vite build: berhasil
