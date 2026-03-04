# SIMA — Sistem Informasi Manajemen Aset Daerah

Aplikasi manajemen aset berbasis Laravel + Inertia (Vue 3). Frontend dibangun dengan Vite. Untuk production direkomendasikan menggunakan Laravel Octane dengan FrankenPHP atau Swoole.

## Teknologi

- Backend: Laravel 12, PHP 8.2+
- Frontend: Vue 3 (Inertia.js), Vite, Tailwind CSS
- Runtime production: Laravel Octane (FrankenPHP / Swoole)
- Testing & lint:
    - PHP: Laravel Pint (`composer run lint`), Pest (`composer test`)
    - JS/TS: ESLint (`npm run lint`)

## Prasyarat

- PHP 8.2+
- Composer
- Node.js + npm
- Database: SQLite (default) atau MySQL/PostgreSQL (sesuaikan `.env`)

## Setup Awal

1. Install dependensi backend:

```bash
composer install
```

2. Siapkan environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Atur konfigurasi database di `.env`, lalu jalankan migrasi:

```bash
php artisan migrate
```

4. Install dependensi frontend:

```bash
npm install
```

Alternatif cepat (sekali jalan) tersedia:

```bash
composer run setup
```

## Menjalankan Mode Develop

Cara yang paling praktis adalah menggunakan script `dev` (akan menjalankan server, queue listener, log viewer, dan Vite sekaligus):

```bash
composer run dev
```

Jika membutuhkan SSR Inertia:

```bash
composer run dev:ssr
```

## Menjalankan Mode Production (Rekomendasi: Octane + FrankenPHP / Swoole)

### 1) Build & Optimasi

Pastikan environment production sudah benar (contoh nilai yang umum):

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://domain-anda.tld`
- `OCTANE_SERVER=frankenphp`

Lalu jalankan:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

Catatan:

- Project ini menggunakan `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, dan `CACHE_STORE=database` (lihat `.env.example`). Pastikan migrasi terkait sudah ada dan database siap.
- Jangan pernah menyimpan token/secret ke repository. Isi variabel seperti `FONNTE_TOKEN` melalui `.env` di server.

### 2) Menjalankan Octane dengan FrankenPHP (Direkomendasikan)

Jalankan Octane FrankenPHP:

```bash
php artisan octane:frankenphp --host=0.0.0.0 --port=8000
```

Jika ingin auto-reload saat file berubah (biasanya untuk staging / non-production):

```bash
php artisan octane:frankenphp --host=0.0.0.0 --port=8000 --watch
```

Untuk production, jalankan proses ini melalui process manager (systemd/supervisor) dan letakkan di belakang reverse proxy (Nginx/Caddy) sesuai kebutuhan.

### 3) Menjalankan Octane dengan Swoole (Alternatif)

Pastikan ekstensi Swoole sudah terpasang di PHP server Anda, set:

- `OCTANE_SERVER=swoole`

Lalu jalankan:

```bash
php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000
```

### 4) Menjalankan Queue Worker (Production)

Karena queue menggunakan `database`, jalankan worker terpisah:

```bash
php artisan queue:work --sleep=1 --tries=3 --timeout=90
```

Jalankan juga via process manager (systemd/supervisor).

## Perintah Berguna

```bash
composer run lint
composer test
npm run lint
```
