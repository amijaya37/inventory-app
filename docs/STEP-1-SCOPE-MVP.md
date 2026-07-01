# Step 1 — Scope MVP Tahap 1

Aplikasi: **Inventory Stock IT Kantor**  
Workspace lokal: `/Users/amijaya/Projects/inventory-app`  
Source: `/Users/amijaya/Projects/Inventory doc /step-1-chatgpt.md`

## 1. Tujuan MVP

MVP tahap 1 memastikan stok IT kantor bisa dicatat dengan:

- akurat,
- stabil,
- dapat diaudit,
- tidak menghasilkan stok minus,
- semua perubahan stok berasal dari transaksi resmi.

## 2. Prinsip Utama

1. Stok tidak boleh minus.
2. Semua perubahan stok harus berasal dari transaksi.
3. Semua transaksi stok harus memiliki nomor dokumen/transaksi.
4. Semua transaksi penting harus masuk audit log.
5. Upload dokumen hanya sebagai bukti transaksi.
6. Workflow approval kompleks belum dibangun di MVP.
7. Transaksi `draft` belum memengaruhi stok.
8. Stok berubah hanya saat transaksi di-`post`.
9. Transaksi `posted` tidak boleh diedit bebas.
10. Logic stok wajib terpusat, bukan tersebar di controller.

## 3. Modul Masuk MVP

| No | Modul | Status | Tujuan |
|---:|---|---|---|
| 1 | Auth | Wajib | Login, logout, proteksi akses aplikasi |
| 2 | Role Permission | Wajib | Membatasi akses Admin Gudang, Staff IT, Manager |
| 3 | User Management | Wajib | Kelola user internal aplikasi |
| 4 | Master Kategori | Wajib | Klasifikasi barang IT |
| 5 | Master Barang | Wajib | Data utama item seperti toner, hardisk, keyboard, mouse, TV |
| 6 | Master Supplier | Wajib | Data vendor/supplier pembelian barang |
| 7 | Master Lokasi | Wajib | Gudang, ruangan, cabang, user/lokasi tujuan |
| 8 | Barang Masuk | Wajib | Mencatat barang dari pembelian atau penerimaan awal |
| 9 | Stock Gudang | Wajib | Menampilkan saldo stok per barang dan lokasi |
| 10 | Barang Keluar | Wajib | Mencatat alokasi/distribusi barang |
| 11 | Barang Tarikan | Wajib | Mencatat barang kembali dari user/lokasi |
| 12 | Mutasi Basic | Wajib | Pindah stok antar lokasi |
| 13 | Laporan Dasar | Wajib | Laporan stok, barang masuk, barang keluar, tarikan, mutasi |
| 14 | Upload Dokumen | Wajib | Lampiran PO, invoice, foto, BA sederhana |
| 15 | Audit Log | Wajib | Merekam aktivitas penting dan posting transaksi |

## 4. Role MVP

| Role | Karakter Akses MVP |
|---|---|
| Admin Gudang | CRUD master, kelola user, buat/post transaksi, upload dokumen, export laporan, lihat audit log |
| Staff IT | View stok, request/view terbatas, bisa membuat draft tarikan bila diberi permission |
| Manager | View dashboard/laporan, export laporan, lihat audit log, tidak mengubah transaksi |

## 5. Permission Minimum

| Modul | Admin Gudang | Staff IT | Manager |
|---|---|---|---|
| Dashboard | View | View terbatas | View |
| Master Data | CRUD | View | View |
| User | CRUD | Tidak | View |
| Barang Masuk | Create, update draft, post, view | View | View |
| Stock | View | View | View |
| Barang Keluar | Create, post, view | Request/view terbatas | View |
| Barang Tarikan | Create, post, view | Create draft/view | View |
| Mutasi | Create, post, view | View | View |
| Laporan | Export | View terbatas | Export |
| Audit Log | View | Tidak | View |

## 6. Modul Tidak Masuk MVP

| Area | Status |
|---|---|
| Approval kompleks | Ditunda |
| Modul aset penuh | Ditunda |
| Nomor aset final | Ditunda |
| Repair management | Ditunda |
| Disposal management | Ditunda |
| Stock opname detail | Ditunda |
| QR/barcode | Ditunda |
| Mobile app | Ditunda |
| Integrasi procurement | Ditunda |
| Integrasi HRIS | Ditunda |
| Integrasi ERP | Ditunda |
| Dashboard analitik kompleks | Ditunda |
| Notifikasi email/WhatsApp | Ditunda |
| SLA repair | Ditunda |

## 7. Tabel Database MVP

### Auth dan Permission

- `users`
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

### Master Data

- `categories`
- `items`
- `suppliers`
- `locations`
- `document_sequences`

### Stock

- `stocks`
- `stock_movements`

### Transaksi

- `goods_receipts`
- `goods_receipt_items`
- `goods_issues`
- `goods_issue_items`
- `goods_returns`
- `goods_return_items`
- `stock_mutations`
- `stock_mutation_items`

### Dokumen dan Audit

- `documents`
- `audit_logs`

## 8. Endpoint Web MVP

### Auth

- `GET /login`
- `POST /login`
- `POST /logout`
- `GET /dashboard`

### User dan Permission

- `GET /users`
- `GET /users/create`
- `POST /users`
- `GET /users/{user}/edit`
- `PUT /users/{user}`
- `PATCH /users/{user}/toggle-active`
- `GET /roles`
- `PUT /roles/{role}/permissions`

### Master Data

- `/categories`
- `/items`
- `/suppliers`
- `/locations`

Masing-masing minimal mendukung list, create, update, toggle active.

### Transaksi

- `/goods-receipts`
- `/goods-issues`
- `/goods-returns`
- `/stock-mutations`

Masing-masing minimal mendukung list, create draft, edit draft, detail, post, upload dokumen, print sederhana bila diperlukan.

### Stock dan Laporan

- `GET /stocks`
- `GET /stocks/{item}/card`
- `GET /stocks/export`
- `/reports/stocks`
- `/reports/goods-receipts`
- `/reports/goods-issues`
- `/reports/goods-returns`
- `/reports/stock-mutations`

### Audit dan Dokumen

- `GET /audit-logs`
- `GET /documents/{document}/download`
- `DELETE /documents/{document}` untuk dokumen transaksi draft sesuai permission.

## 9. Acceptance Criteria Global

MVP tahap 1 dianggap selesai jika:

1. User bisa login sesuai role.
2. User nonaktif tidak bisa login.
3. Admin Gudang bisa kelola master barang, kategori, supplier, lokasi, dan user.
4. Barang masuk bisa menambah stok saat posting.
5. Barang keluar bisa mengurangi stok saat posting.
6. Barang tarikan bisa menambah stok jika final action `return_to_stock`.
7. Mutasi bisa memindahkan stok antar lokasi.
8. Sistem menolak stok minus.
9. Semua posting transaksi masuk kartu stok/stock movement.
10. Laporan stok, masuk, keluar, tarikan, dan mutasi bisa diexport Excel.
11. Dokumen transaksi bisa diupload ke private storage.
12. Aktivitas penting masuk audit log.
13. Data transaksi `posted` tidak bisa diedit bebas.
14. Tidak ada approval kompleks/aset penuh/repair/disposal/opname detail di MVP.

## 10. Urutan Implementasi Aman

```text
Auth
→ Role Permission
→ User Management
→ Master Data
→ Stock Engine
→ Barang Masuk
→ Stock Gudang
→ Barang Keluar
→ Barang Tarikan
→ Mutasi
→ Laporan
→ Audit + Upload hardening
→ UAT
```

## 11. Keputusan Step 1

Step 1 adalah tahap **scope freeze**. Belum menjalankan bootstrap Laravel atau coding fitur besar.

Output yang disiapkan pada step ini:

- Dokumen scope MVP terkunci di repo lokal.
- Daftar modul wajib dan modul ditunda jelas.
- Role, permission minimum, tabel, endpoint, dan acceptance criteria awal terdokumentasi.
- Project siap masuk Step 2: setup Laravel dan dependency.
