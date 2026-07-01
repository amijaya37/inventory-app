# Step 6 — Core Stock Engine

Step ini menerapkan core stock engine agar semua perubahan saldo stok berjalan atomic, terkunci, dan selalu menghasilkan ledger `stock_cards`.

## Komponen yang diterapkan

### Migration

- `stocks`
  - `qty_on_hand` unsigned integer
  - `qty_reserved` unsigned integer
  - `qty_available` generated column: `qty_on_hand - qty_reserved`
  - unique `item_id + location_id`
  - index `location_id + item_id`
  - constraint MySQL `qty_on_hand >= qty_reserved`
- `stock_cards`
  - `stock_id`
  - `item_id`
  - `location_id`
  - `trx_date`
  - `direction`
  - `movement_type`
  - `reference_type`
  - `reference_id`
  - `reference_no`
  - `qty`
  - `qty_before`
  - `qty_after`
  - `unit_cost`
  - `remarks`
  - `posted_by`
  - constraint MySQL `qty > 0`
  - constraint MySQL `direction IN ('in', 'out')`

Catatan test: constraint `ALTER TABLE ... ADD CONSTRAINT` hanya dijalankan ketika driver database adalah MySQL, karena test suite memakai SQLite in-memory dan SQLite tidak mendukung sintaks alter constraint yang sama.

### Enum

- `StockDirection`
  - `In = 'in'`
  - `Out = 'out'`
- `StockMovementType`
  - `GoodsReceipt = 'goods_receipt'`
  - `GoodsIssue = 'goods_issue'`
  - `GoodsReturn = 'return_in'`
  - `MutationOut = 'mutation_out'`
  - `MutationIn = 'mutation_in'`
  - `AdjustmentIn = 'adjustment_in'`
  - `AdjustmentOut = 'adjustment_out'`

Case enum lama dipertahankan agar Action Step 3/4 tidak rusak.

### Model

- `Stock`
  - fillable eksplisit
  - casts qty dan `last_movement_at`
  - relasi `item`, `location`, `cards`
- `StockCard`
  - fillable eksplisit
  - casts enum untuk `direction` dan `movement_type`
  - relasi `stock`, `item`, `location`, `postedBy`

### Exception

- `InsufficientStockException`
- `StockRowNotFoundException`

### Service

Service utama baru:

```text
app/Domain/Inventory/Services/StockEngine.php
```

Method:

- `increase(...)`
- `decrease(...)`
- `transfer(...)`
- `ensureStockRow(...)`

Aturan yang dijaga:

- qty harus positif,
- stok masuk boleh membuat row stok baru,
- stok keluar tidak membuat row stok baru,
- stok keluar wajib cek `qty_on_hand - qty_reserved`,
- semua update saldo memakai `DB::transaction(..., attempts: 3)`,
- saldo dikunci dengan `lockForUpdate()`,
- setiap perubahan stok menghasilkan `stock_cards`,
- mutasi antar lokasi menghasilkan dua ledger: OUT dan IN.

Compatibility wrapper lama tetap dipertahankan:

```text
app/Domain/Inventory/Services/StockMovementService.php
```

Wrapper ini sekarang meneruskan operasi ke `StockEngine`, sehingga action lama tidak perlu langsung diubah total pada step ini.

## Test coverage

File baru:

```text
tests/Feature/Inventory/StockEngineTest.php
```

Coverage:

- increase stok + ledger IN,
- decrease stok + ledger OUT,
- cegah stok keluar melebihi available,
- gagal decrease jika row stok tidak ada,
- transfer antar lokasi membuat dua ledger,
- reject transfer lokasi asal sama dengan tujuan.

Test lama `StockMovementServiceTest` disesuaikan dengan exception baru dari engine.

## Verifikasi

Berhasil dijalankan:

```bash
php artisan migrate:fresh --seed
php artisan test --compact
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=1G
npm run build
```

Hasil akhir:

- Test: 53 passed, 150 assertions
- Pint: PASS, 186 files
- PHPStan: No errors
- Vite build: success

## Catatan penting

- Migration `stock_cards` di-rename ke timestamp `2026_06_29_152638` agar dieksekusi setelah semua tabel parent transaksi dan `stocks` tersedia.
- `qty_available` adalah generated column, jangan diisi manual dari Eloquent.
- Jika nanti ada satuan parsial seperti meter/liter/kg, qty perlu diubah dari integer ke decimal.
