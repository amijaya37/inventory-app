# Deployment Intranet — Inventory Stock IT

Panduan ringkas deployment production internal/intranet.

## Prasyarat server
- PHP sesuai requirement Composer (`^8.3`) plus extension Laravel umum.
- MySQL 8+.
- Nginx/Apache diarahkan ke folder `public/` saja.
- User web server memiliki write access hanya ke `storage/` dan `bootstrap/cache/`.
- HTTPS internal certificate atau reverse proxy HTTPS aktif.

## Environment production minimum
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://inventory-internal.example.local
SESSION_SECURE_COOKIE=true
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

## Release command
```bash
cd /var/www/inventory-app
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan inventory:preflight --strict
sudo systemctl reload php-fpm
sudo systemctl reload nginx
```

## Smoke test setelah deploy
1. Buka `/login`.
2. Login sebagai Admin Gudang.
3. Buka dashboard.
4. Buat sample kategori/item/lokasi/supplier bila belum ada.
5. Buat draft barang masuk, post, cek stock bertambah dan stock card.
6. Buat barang keluar, post, cek stock berkurang dan BA bisa dibuka.
7. Upload dan download dokumen sample.
8. Buka laporan stock, barang masuk, barang keluar; export Excel.
9. Buka audit log dan cek aktivitas tercatat.
10. Cek `storage/logs/laravel.log` setelah smoke test.

## Rollback minimum
- Simpan tag/release sebelumnya.
- Backup database sebelum migrate.
- Backup folder dokumen private sebelum deploy bila sudah ada data.
- Jika rollback dibutuhkan: restore release sebelumnya, restore DB bila migration destructive, clear/cache ulang config-route-view.
