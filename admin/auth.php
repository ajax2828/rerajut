<?php
require_once __DIR__ . '/../includes/fungsi.php';

no_cache_headers();

$idleLimit = 7200; // 2 jam
if (!empty($_SESSION['admin_last_active']) && (time() - (int) $_SESSION['admin_last_active']) > $idleLimit) {
    unset($_SESSION['admin'], $_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_nama'], $_SESSION['admin_last_active']);
    session_regenerate_id(true);
    flash_set('gagal', 'Sesi admin telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.');
    redirect(BASE_URL . '/admin/login.php');
}

if (empty($_SESSION['admin']) || empty($_SESSION['admin_id'])) {
    flash_set('gagal', 'Silakan masuk sebagai admin terlebih dahulu.');
    redirect(BASE_URL . '/admin/login.php');
}

$stmtAdmin = $pdo->prepare('SELECT id, username, nama FROM users WHERE id = ? LIMIT 1');
$stmtAdmin->execute([(int) $_SESSION['admin_id']]);
$adminAktif = $stmtAdmin->fetch();
if (!$adminAktif) {
    unset($_SESSION['admin'], $_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_nama'], $_SESSION['admin_last_active']);
    session_regenerate_id(true);
    flash_set('gagal', 'Akun admin tidak ditemukan. Silakan masuk kembali.');
    redirect(BASE_URL . '/admin/login.php');
}

$_SESSION['admin_username'] = $adminAktif['username'];
$_SESSION['admin_nama'] = $adminAktif['nama'];
$_SESSION['admin_last_active'] = time();

function admin_status_pesanan() {
    return pesanan_status_daftar();
}
