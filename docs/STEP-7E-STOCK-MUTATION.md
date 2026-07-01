# Step 7E — Mutasi Barang / Stock Mutation

Step ini mengaktifkan modul Mutasi Barang untuk memindahkan stok antar lokasi/gudang dengan prinsip atomic: stok asal berkurang dan stok tujuan bertambah dalam satu transaksi database.

## Komponen yang diterapkan

- Migration:
  - `database/migrations/2026_06_29_152633_create_stock_mutations_table.php`
  - `database/migrations/2026_06_29_152637_create_stock_mutation_items_table.php`
- Models:
  - `app/Domain/Inventory/Models/StockMutation.php`
  - `app/Domain/Inventory/Models/StockMutationItem.php`
- Requests:
  - `app/Http/Requests/Transaction/StoreStockMutationRequest.php`
  - `app/Http/Requests/Transaction/PostStockMutationRequest.php`
- Actions:
  - `app/Actions/Inventory/StockMutation/StoreStockMutationAction.php`
  - `app/Actions/Inventory/StockMutation/PostStockMutationAction.php`
- Controller:
  - `app/Http/Controllers/Web/Transaction/StockMutationController.php`
- Views:
  - `resources/views/transactions/stock-mutations/index.blade.php`
  - `resources/views/transactions/stock-mutations/create.blade.php`
  - `resources/views/transactions/stock-mutations/show.blade.php`
- Test:
  - `tests/Feature/Inventory/StockMutationTest.php`

## Flow utama

1. User membuat draft mutasi.
2. Draft mendapat nomor transaksi dari `DocumentNumberService::next('stock_mutation')`.
3. Draft tidak mengubah stok.
4. User posting draft.
5. Sistem menjalankan `DB::transaction()` dan lock header mutasi.
6. Sistem memvalidasi:
   - status harus `draft`,
   - lokasi asal dan tujuan tidak boleh sama,
   - detail tidak boleh kosong,
   - stok asal harus cukup lewat `StockEngine`.
7. Untuk setiap item yang digabung per `item_id`:
   - `StockMovementService::decrease()` di lokasi asal dengan `StockMovementType::MutationOut`,
   - `StockMovementService::increase()` di lokasi tujuan dengan `StockMovementType::MutationIn`.
8. Sistem membuat dua ledger/kartu stok:
   - OUT dari lokasi asal,
   - IN ke lokasi tujuan.
9. Status berubah menjadi `posted` dan audit log dicatat.

## Routes

- `GET /stock-mutations` → `stock-mutations.index`
- `GET /stock-mutations/create` → `stock-mutations.create`
- `POST /stock-mutations` → `stock-mutations.store`
- `GET /stock-mutations/{stockMutation}` → `stock-mutations.show`
- `POST /stock-mutations/{stockMutation}/post` → `stock-mutations.post`

## Permission

Mengikuti permission existing Step 4:

- `mutations.view`
- `mutations.create`
- `mutations.post`

## Validasi bisnis

- Lokasi asal wajib aktif.
- Lokasi tujuan wajib aktif.
- Lokasi asal dan tujuan tidak boleh sama.
- Minimal satu item mutasi.
- Qty minimal 1.
- Barang harus aktif.
- Barang serialized wajib serial number.
- Validasi stok di FormRequest hanya untuk feedback cepat.
- Validasi stok utama tetap terjadi di `StockEngine::decrease()` dengan row lock dan exception rollback.

## Catatan teknis

- Struktur tetap mengikuti project: `app/Domain/...`, `app/Actions/...`, dan controller tipis.
- Tidak membuat tabel `document_counters` baru karena project sudah memakai `DocumentNumberService`.
- Nomor mutasi saat ini memakai prefix existing `MT`:
  - contoh: `MT-20260630-00001`
- Dokumen Step 7E memberi contoh prefix `MUT`; di project ini dipakai `MT` supaya konsisten dengan service existing.
- `StockMovementType::MutationOut` bernilai `mutation_out`.
- `StockMovementType::MutationIn` bernilai `mutation_in`.
- Tidak pernah menulis `qty_available` manual karena generated column dari `qty_on_hand - qty_reserved`.
- Jika stok asal kurang, `StockEngine` melempar `InsufficientStockException` dan seluruh transaksi rollback.

## Test coverage

`tests/Feature/Inventory/StockMutationTest.php` mencakup:

- membuat draft mutasi tanpa memindahkan stok,
- posting mutasi menghasilkan OUT asal dan IN tujuan,
- stok asal berkurang,
- stok tujuan bertambah,
- stock card mencatat `mutation_out` dan `mutation_in`,
- posting gagal jika stok asal kurang,
- validasi gagal jika lokasi asal dan tujuan sama.

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

- Tests: 73 passed / 224 assertions
- Pint: PASS 195 files
- PHPStan: No errors
- Vite build: berhasil
