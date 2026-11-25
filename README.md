# Rerajut Thrift

Aplikasi web toko baju thrift berbasis **PHP Native** & MySQL (PDO) — **bukan Laravel** — siap dijalankan di shared hosting (cPanel/Apache), VPS (Apache/Nginx + PHP-FPM), container/PaaS, maupun lokal.

## Persyaratan Server
- PHP 7.4 atau 8.x (`pdo_mysql`, `json`, `fileinfo` disarankan, `mbstring` disarankan)
- MySQL 5.7+ / MySQL 8.0+ atau MariaDB 10.3+
- Apache (`mod_rewrite`, `mod_headers` disarankan) atau Nginx + PHP-FPM
- **Catatan Panel/Container Deploy:** Pilih runtime **PHP Native / PHP** (dengan document root `/`, **bukan** preset Laravel dan **bukan** `/public`), karena proyek ini tidak menggunakan Laravel (`artisan` / `APP_KEY`).

## Cara Deploy ke Server

### 1. Siapkan Database
1. Buat database baru (misalnya `rerajut_thrift`) di MySQL / phpMyAdmin / panel server.
2. Import file `rerajut_thrift.sql` ke database tersebut.

### 2. Konfigurasi Environment
Anda dapat mengisi environment variables langsung melalui panel container/server **atau** menyalin `config/env.sample` menjadi `.env`:

```bash
cp config/env.sample .env
```

Aplikasi otomatis mengenali format environment berikut:
- PHP Native: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
- Standar Laravel/PaaS: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Standar Docker/MySQL: `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD` atau `DATABASE_URL`

Catatan:
- `BASE_URL` dan `SITE_URL` terdeteksi otomatis baik saat dijalankan di root domain (`https://domainanda.com/`) maupun subfolder (`https://domainanda.com/triftweb/`).
- Jika di belakang reverse proxy khusus, Anda bisa mengisi `APP_URL=https://domainanda.com` dan `APP_BASE_URL=/` (atau `/subfolder`).

### 3. Izin Folder Upload (Linux / VPS)
Pastikan web server memiliki izin tulis pada folder `uploads/bukti` dan `images`:

```bash
mkdir -p uploads/bukti
chmod -R 755 images uploads
# Jika menggunakan Apache/Nginx di Linux (sesuaikan user web server, misal www-data):
# chown -R www-data:www-data images uploads
```

### 4. Contoh Konfigurasi Nginx (jika tidak memakai Apache)
Karena Nginx tidak membaca `.htaccess`, tambahkan blok keamanan berikut pada `server {}` Nginx Anda:

```nginx
location ~ /\.(?!well-known) {
    deny all;
}

location ~* \.(sql|env|log|bak|ini|sh|md|sample)$ {
    deny all;
}

location ~ ^/(config|includes)/ {
    deny all;
}

location ~ ^/(uploads|images)/.*\.(php|phtml|phar)$ {
    deny all;
}

location = /robots.txt {
    rewrite ^ /robots.php last;
}

location / {
    try_files $uri $uri/ /404.php;
}
```

### 5. Keamanan Setelah Online
- Masuk ke halaman admin (`/admin/login.php`) lalu buka menu **Akun** (`/admin/akun.php`) untuk segera mengganti kata sandi admin bawaan.
- Pastikan `APP_DEBUG=false` di server produksi.
- Hapus file `rerajut_thrift.sql` dari server setelah selesai di-import (sebagai lapisan keamanan tambahan di samping proteksi `.htaccess`).
