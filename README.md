# Inventory Stock IT

Aplikasi Inventory Stock IT Kantor — local-first development workspace.

## Status Saat Ini

- Step 1 selesai: scope MVP tahap 1 sudah diringkas dan difreeze di `docs/STEP-1-SCOPE-MVP.md`.
- Step 2 selesai: Laravel 13 + Livewire/Pest/MySQL sudah dibootstrap di lokal. Detail ada di `docs/STEP-2-SETUP-LARAVEL.md`.
- Catatan Step 2: `maatwebsite/excel` belum kompatibel dengan PHP 8.5/Laravel 13 saat instalasi, jadi export Excel akan ditangani ulang pada Step laporan.

## Source Blueprint

Source dokumen awal berada di:

```text
/Users/amijaya/Projects/Inventory doc /
```

Catatan: path source memiliki trailing space setelah `doc`, jadi gunakan quote saat menjalankan command shell.

## Prinsip MVP

- Stok tidak boleh minus.
- Semua perubahan stok berasal dari transaksi.
- Transaksi draft tidak memengaruhi stok.
- Stok berubah hanya saat posting.
- Transaksi posted tidak boleh diedit bebas.
- Semua posting masuk audit log dan stock movement/kartu stok.
- Approval kompleks, aset penuh, repair, disposal, opname detail, QR/barcode, dan integrasi eksternal ditunda.

## Urutan Step

1. Scope MVP tahap 1.
2. Setup Laravel, dependency, `.env`, database, struktur folder.
3. Arsitektur domain Laravel.
4. Auth + RBAC.
5. Master kategori.
6. Core stock engine.
7. Transaksi barang masuk, stock gudang, barang keluar, barang tarikan, mutasi.
8. Audit log + upload dokumen.
9. Laporan dasar + export Excel.
10. Test plan.
11. Belum ditentukan / hardening deployment intranet.
