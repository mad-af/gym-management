# Gym Management

Gym Management adalah aplikasi manajemen operasional gym berbasis Laravel 12 + Inertia (Vue 3 + TypeScript).
Fitur utamanya mencakup manajemen member, membership, kunjungan (visit), penjualan produk, pergerakan stok, kartu member PDF + QR, serta notifikasi WhatsApp.

## Tech Stack

- Backend: Laravel 12, PHP 8.2+
- Frontend: Inertia.js, Vue 3, TypeScript, Vite, Tailwind CSS
- Auth & permission: Laravel Fortify, Spatie Permission
- PDF & QR: mPDF, helper QR internal
- Runtime production: Laravel Octane + FrankenPHP

## Fitur Utama

- Master data: customer/member, membership package, product, role/user
- Transaksi: visits, membership transactions, sales, stock movements
- Export CSV untuk halaman transaksi
- Membership card PDF ukuran kartu (8.56 cm x 5.4 cm) + QR
- Kirim kartu member dan notifikasi via WhatsApp (Fonnte)
- Scheduled reminder membership expiry (H-3 dan H-day)

## Prasyarat

- PHP 8.2+
- Composer
- Node.js + npm
- Database: MySQL/PostgreSQL/SQLite (sesuaikan `.env`)

## Setup Lokal

### Opsi cepat

```bash
composer setup
```

### Opsi manual

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm install
```

`php artisan storage:link` wajib dijalankan agar file di `storage/app/public` bisa diakses publik lewat `public/storage`.

## Menjalankan Development

```bash
composer dev
```

Perintah di atas menjalankan server app, queue listener, log viewer, dan Vite secara bersamaan.

Jika butuh SSR:

```bash
composer dev:ssr
```

## Deploy Production (FrankenPHP)

Panduan ini menggunakan Laravel Octane dengan FrankenPHP.

### 1) Build dan optimasi

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

### 2) Konfigurasi `.env` production minimum

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.tld
OCTANE_SERVER=frankenphp

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Jika memakai WhatsApp/Fonnte, set juga variabel terkait di `.env` server (contoh: `FONNTE_TOKEN`).

### 3) Jalankan Octane + FrankenPHP

```bash
php artisan octane:frankenphp --host=0.0.0.0 --port=8000
```

Gunakan process manager (systemd/supervisor) untuk menjaga service tetap hidup.

### 4) Jalankan scheduler

Tambahkan cron job berikut ke server (jalankan `crontab -e`):

```bash
* * * * * cd /path-ke-project && php artisan schedule:run >> /dev/null 2>&1
```

Ganti `/path-ke-project` dengan path absolut ke project ini (misal `/var/www/gym-management`).

**Keterangan:**

- Cron job ini menjalankan `schedule:run` setiap 1 menit
- Laravel otomatis cek apakah ada scheduled command yang perlu jalan pada menit tersebut
- Cron job TIDAK perlu di-set ulang setelah server reboot (tersimpan permanen di crontab)
- Pastikan cron daemon aktif: `sudo systemctl enable cron`

**Verifikasi cron job sudah aktif:**

```bash
crontab -l
```

### 5) Jalankan queue worker

Queue worker wajib aktif untuk memproses notifikasi WhatsApp (reminder membership).

#### Opsi A: Supervisor (Production Recommended)

Buat file `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-ke-project/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/path-ke-project/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker
```

#### Opsi B: Systemd (Alternative Production)

Buat file `/etc/systemd/system/laravel-worker.service`:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
ExecStart=/usr/bin/php /path-ke-project/artisan queue:work --sleep=3 --tries=3 --timeout=90
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
```

#### Opsi C: Manual (Development / Sementara)

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

**Catatan:** Opsi C tidak survive server reboot, hanya untuk development atau testing sementara.

## Perintah Berguna

```bash
composer lint
composer test:lint
composer test
npm run lint
npm run build
```
