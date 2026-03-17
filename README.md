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

### 4) Jalankan queue worker

```bash
php artisan queue:work --sleep=1 --tries=3 --timeout=90
```

Worker ini wajib aktif karena ada proses queue untuk notifikasi/reminder.

### 5) Jalankan scheduler

Gunakan salah satu:

- Cron + `php artisan schedule:run` tiap menit, atau
- Proses long-running:

```bash
php artisan schedule:work
```

## Perintah Berguna

```bash
composer lint
composer test:lint
composer test
npm run lint
npm run build
```
