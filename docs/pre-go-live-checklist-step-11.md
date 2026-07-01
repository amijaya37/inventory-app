# Pre Go-Live Checklist — Inventory Stock IT

Sumber: `step-11-chatgpt.md`. Dokumen ini adalah checklist operasional sebelum hosting ke intranet/production internal.

## Informasi Go-Live

| Item | Keterangan |
|---|---|
| Nama Aplikasi | Inventory Stock IT |
| Environment | Production Internal / Intranet |
| Framework | Laravel + MySQL |
| Role Utama | Admin Gudang, Staff IT, Manager |
| Tanggal Go-Live | TBD |
| PIC Aplikasi | TBD |
| PIC Infrastruktur | TBD |
| PIC UAT Bisnis | TBD |

## Checklist wajib sebelum production

### Security Laravel
- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] `APP_KEY` production sudah generate dan stabil.
- [ ] `.env` tidak bisa diakses dari browser.
- [ ] `storage/` dan `bootstrap/cache/` writable oleh web server.
- [ ] Source code selain runtime folder tidak writable sembarangan.
- [x] Error page 403/404/419/500 tersedia di `resources/views/errors/`.
- [ ] `php artisan config:cache`, `route:cache`, `view:cache` berhasil di server.
- [ ] Tidak ada credential hardcode di source code.

### HTTPS/session/internal access
- [ ] HTTPS aktif dan HTTP redirect ke HTTPS.
- [ ] Cookie secure diset saat production HTTPS.
- [ ] Session timeout sesuai kebijakan internal.
- [ ] Login throttle aktif.
- [ ] Akses dibatasi jaringan internal/VPN bila dibutuhkan.
- [ ] File backup/log/export tidak bisa diakses publik.
- [ ] Akun default/testing dihapus atau dinonaktifkan sebelum go-live.

### Permission & role
- [x] Seeder role-permission idempotent.
- [x] Role dasar: Admin Gudang, Staff IT, Manager.
- [x] User tanpa permission mendapat 403 pada route protected.
- [ ] Setiap user production sudah mapping role final.
- [ ] Negative test permission UAT selesai.

### Data & stock integrity
- [x] Stock tidak boleh minus via StockEngine/StockMovementService.
- [x] Draft transaksi tidak mengubah stock.
- [x] Posting barang masuk/keluar/tarikan/mutasi atomic dan membuat `stock_cards`.
- [x] Posting transaksi penting membuat `audit_logs`.
- [ ] Rekonsiliasi stock vs ledger disiapkan bila volume produksi sudah besar.
- [ ] Concurrency test production-like dilakukan bila user posting paralel banyak.

### Performance/database
- [x] List utama memakai pagination.
- [x] Export Excel memakai query/chunk, bukan collection besar.
- [ ] Load test minimal: 1.000 item, 10.000 stock card, 5.000+ rows export.
- [ ] Slow query review di staging/production-like.

### Backup/restore
- [ ] Backup DB otomatis minimal harian.
- [ ] Backup dokumen private ikut disertakan.
- [ ] Retensi backup disepakati.
- [ ] Restore DB dan dokumen pernah diuji di staging.
- [ ] RTO/RPO disepakati.

### Logging/monitoring
- [x] Audit log transaksi, upload, download dokumen tersedia.
- [ ] Log rotation production disiapkan.
- [ ] Disk/database storage dimonitor.
- [ ] Notifikasi error/backup failure disiapkan.

### UAT minimum
- [ ] User bisa login/logout dan menu sesuai role.
- [ ] Master data sample dibuat.
- [ ] Barang masuk end-to-end lulus.
- [ ] Barang keluar end-to-end lulus, termasuk insufficient stock.
- [ ] Barang tarikan end-to-end lulus.
- [ ] Mutasi end-to-end lulus.
- [ ] Laporan dan export Excel lulus.
- [ ] Upload/download dokumen lulus.
- [ ] Audit log dicek oleh PIC.

## Command deployment Laravel production

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
php artisan storage:link # hanya jika asset non-sensitif perlu public link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan inventory:preflight --strict
```

## Go/No-Go ringkas

Go-live boleh dilakukan jika UAT sign-off, transaksi stok end-to-end lulus, stock tidak minus, permission aman, dokumen private aman, export utama berhasil, backup/restore sudah diuji, dan PIC support sudah ditentukan.

Go-live harus ditunda jika ada bug stok salah/minus, akses permission bocor, dokumen private bisa publik, backup/restore belum siap, posting bisa dobel, export utama gagal, atau UAT bisnis belum sign-off.
