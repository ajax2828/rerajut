# Rerajut Thrift (Laravel 11)

Aplikasi web toko baju thrift **Rerajut Thrift** berbasis **Laravel 11** & MySQL, siap dideploy ke container/PaaS Laravel, VPS (Apache/Nginx + PHP-FPM), maupun lokal.

## Persyaratan Server
- PHP 8.2+ (`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `json`, `tokenizer`, `xml`)
- Composer 2
- MySQL 8.0+ / MariaDB 10.3+
- DocumentRoot diarahkan ke folder `public/`

## Cara Deploy ke Server

1. **Install dependensi PHP:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. **Siapkan `.env` & `APP_KEY`:**
   ```bash
   cp .env.example .env
   php artisan key:generate --force
   ```
   Sesuaikan konfigurasi `APP_URL`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (juga otomatis mengenali `DB_NAME`/`DB_USER`/`DB_PASS`, `MYSQL_*`, dan `DATABASE_URL`).
3. **Migrasi & Seed Database (idempoten):**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```
   > Jika database sudah terisi dari dump `database/rerajut_thrift.sql`, perintah `php artisan migrate --force` tetap aman dijalankan karena migrasi memeriksa keberadaan tabel terlebih dahulu.
4. **Optimasi cache produksi (opsional):**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. **Izin folder tulis:**
   Pastikan `storage/`, `bootstrap/cache/`, `public/images/`, dan `public/uploads/bukti/` dapat ditulis oleh user web server.

## Endpoint Utama
- Toko: `/`, `/produk`, `/produk/{slug}`, `/keranjang`, `/checkout`, `/lacak`, `/pesanan-saya`, `/kontak`, `/tentang`
- Admin: `/admin/login`, `/admin`, `/admin/produk`, `/admin/kategori`, `/admin/pesan`, `/admin/akun`
- Healthcheck container: `/up`
- SEO: `/sitemap.xml`, `/robots.txt`

## Keamanan Setelah Online
- Masuk ke `/admin/login` lalu buka menu **Akun** (`/admin/akun`) untuk segera mengganti kata sandi admin bawaan.
- Pastikan `APP_DEBUG=false` di lingkungan produksi.
