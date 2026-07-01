# Step 7A — Barang Masuk / Goods Receipt

Step ini mengaktifkan modul transaksi Barang Masuk dari draft sampai posting stok.

## Komponen yang diterapkan

- Migration `goods_receipts` dilengkapi header transaksi:
  - `receipt_no`
  - `source_type`
  - `supplier_id`
  - `warehouse_location_id`
  - `po_no`
  - `invoice_no`
  - `purchase_date`
  - `receipt_date`
  - `po_file_path`
  - `invoice_file_path`
  - `total_amount`
  - `status`
  - `remarks`
  - `created_by`
  - `posted_by`
  - `posted_at`
  - soft delete
- Migration `goods_receipt_items` dilengkapi detail item:
  - `goods_receipt_id`
  - `item_id`
  - `qty`
  - `unit_price`
  - `total_price`
  - `serial_numbers`
  - `warranty_until`
  - `condition_status`
  - `notes`
- Model `GoodsReceipt` dan `GoodsReceiptItem` dilengkapi casts dan relasi.
- `DocumentNumberService` memakai prefix ringkas:
  - `BM-YYYYMMDD-00001` untuk barang masuk.
- `StoreGoodsReceiptAction` membuat draft dan menghitung total transaksi.
- `PostGoodsReceiptAction` melakukan posting atomik ke `StockMovementService` / `StockEngine`.
- `GoodsReceiptController` menyediakan:
  - index
  - create
  - store draft
  - show detail
  - post
- Route aktif:
  - `goods-receipts.index`
  - `goods-receipts.create`
  - `goods-receipts.store`
  - `goods-receipts.show`
  - `goods-receipts.post`
- Blade UI aktif:
  - `resources/views/transactions/goods-receipts/index.blade.php`
  - `resources/views/transactions/goods-receipts/create.blade.php`
  - `resources/views/transactions/goods-receipts/show.blade.php`

## Aturan bisnis

- Draft barang masuk belum menambah stok.
- Posting barang masuk:
  - hanya bisa dilakukan saat status `draft`,
  - menambah `stocks.qty_on_hand`,
  - membuat ledger `stock_cards` direction `in`, movement type `goods_receipt`,
  - mengisi `posted_by` dan `posted_at`,
  - mengubah status menjadi `posted`.
- Posting ulang transaksi posted ditolak.
- Item serialized harus memiliki jumlah serial number yang sama dengan qty.
- Serial number dalam transaksi yang sama tidak boleh duplikat.

## Catatan teknis

- Untuk pengecekan status model cast enum, gunakan method `isDraft()` / `isPosted()` dan `getRawOriginal('status')` agar runtime benar dan PHPStan tidak false positive.
- File PO/invoice disimpan ke disk `local`:
  - `documents/goods-receipts/po`
  - `documents/goods-receipts/invoices`
- Jika simpan draft gagal setelah upload file, controller menghapus file yang sudah terlanjur tersimpan.

## Test coverage

`tests/Feature/Inventory/GoodsReceiptTest.php` mencakup:

- Admin Gudang bisa membuat draft barang masuk tanpa menambah stok.
- Posting barang masuk menambah stok dan menulis stock card.
- Transaksi posted tidak bisa diposting ulang.
- Validasi serial number untuk item serialized.

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

- Tests: 57 passed / 171 assertions
- Pint: PASS 187 files
- PHPStan: No errors
- Vite build: berhasil
