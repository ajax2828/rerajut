<?php
define('BASE_URL', '/triftweb');
define('SITE_URL', 'http://localhost' . BASE_URL);
define('SITE_NAMA', 'Rerajut Thrift');
define('SITE_TAGLINE', 'Baju thrift pilihan, gaya baru tanpa beli baru');
define('SITE_DESKRIPSI', 'Rerajut Thrift menjual baju thrift pilihan dengan kualitas terkurasi, stok satuan, harga ramah mahasiswa, dan pengiriman ke seluruh Indonesia.');
define('SITE_EMAIL', 'halo@rerajut.id');
define('SITE_TELEPON', '0812-3456-7890');
define('SITE_ALAMAT', 'Jl. Melati No. 21, Yogyakarta');
define('ONGKIR', 15000);
define('GRATIS_ONGKIR_MIN', 250000);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

date_default_timezone_set('Asia/Jakarta');
