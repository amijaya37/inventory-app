# Step 7B — Stock Gudang / Kartu Stok

Step ini mengaktifkan modul baca Stock Gudang. Modul ini tidak mengubah stok manual; semua saldo tetap berasal dari posting transaksi melalui `StockEngine`.

## Komponen yang diterapkan

- Controller:
  - `app/Http/Controllers/Web/Stock/StockController.php`
  - `app/Http/Controllers/Web/Stock/StockCardController.php`
- View:
  - `resources/views/stocks/index.blade.php`
  - `resources/views/stocks/cards/index.blade.php`
- Route:
  - `GET /stock` → `stock.index`
  - `GET /stock/{stock}/card` → `stock.card`
- Test:
  - `tests/Feature/Inventory/StockWarehouseTest.php`

## Fitur Stock Gudang

Halaman `stock.index` membaca saldo langsung dari tabel `stocks`, bukan menghitung ulang dari seluruh histori.

Fitur yang tersedia:

- Summary total:
  - total baris stock,
  - total qty on hand,
  - total reserved,
  - total available,
  - jumlah low stock,
  - jumlah stock kosong.
- Filter:
  - keyword SKU/nama barang,
  - kategori,
  - lokasi,
  - status stock: semua, low stock, kosong.
- Tabel saldo per item dan lokasi:
  - SKU,
  - nama barang,
  - kategori,
  - lokasi,
  - qty on hand,
  - qty reserved,
  - qty available,
  - minimum stock,
  - status aman/low/kosong,
  - last movement,
  - link kartu stok.

## Fitur Kartu Stok

Halaman `stock.card` menampilkan histori dari tabel `stock_cards` untuk satu kombinasi item dan lokasi.

Fitur:

- Ringkasan barang/lokasi/saldo saat ini.
- Filter tanggal `date_from` dan `date_to`.
- Histori urut terbaru:
  - tanggal,
  - nomor referensi,
  - movement type,
  - qty masuk,
  - qty keluar,
  - saldo awal,
  - saldo akhir,
  - user posting,
  - catatan.

## Permission

Route tetap mengikuti permission Step 4:

- `stock.view` untuk halaman stock gudang.
- `stock.card` untuk kartu stok.

## Catatan teknis

- Project memakai kolom item `minimum_stock`, bukan `min_stock`. Instruksi Step 7B menyebut `min_stock`, tetapi implementasi disesuaikan dengan schema project yang sudah berjalan.
- `qty_available` tetap generated column dari Step 6, sehingga tidak ditulis manual dari Eloquent.
- Route name dipertahankan sesuai struktur existing sidebar: `stock.index` dan `stock.card`.
- View menggunakan dark theme agar konsisten dengan UI project.

## Test coverage

`tests/Feature/Inventory/StockWarehouseTest.php` mencakup:

- halaman Stock Gudang menampilkan summary dan list stock,
- filter kategori/lokasi/keyword/status kosong,
- halaman Kartu Stok menampilkan histori dan filter tanggal,
- akses stock memerlukan permission.

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

- Tests: 61 passed / 189 assertions
- Pint: PASS 189 files
- PHPStan: No errors
- Vite build: berhasil
