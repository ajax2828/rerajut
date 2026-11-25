<?php
require_once __DIR__ . '/../includes/fungsi.php';

no_cache_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    if (!empty($_SESSION['admin'])) {
        flash_set('gagal', 'Gunakan tombol Keluar pada menu admin.');
        redirect(BASE_URL . '/admin/index.php');
    }
    redirect(BASE_URL . '/index.php');
}

unset(
    $_SESSION['admin'],
    $_SESSION['admin_id'],
    $_SESSION['admin_username'],
    $_SESSION['admin_nama'],
    $_SESSION['admin_last_active'],
    $_SESSION['csrf']
);
session_regenerate_id(true);
flash_set('sukses', 'Kamu sudah keluar dari halaman admin.');
redirect(BASE_URL . '/index.php');
