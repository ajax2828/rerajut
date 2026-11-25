<?php
require_once __DIR__ . '/../includes/fungsi.php';

if (empty($_SESSION['admin'])) {
    flash_set('gagal', 'Silakan masuk sebagai admin terlebih dahulu.');
    redirect(BASE_URL . '/admin/login.php');
}

function admin_status_pesanan() {
    return ['Menunggu Pembayaran', 'Pembayaran Diproses', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
}
