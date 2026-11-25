<?php
require_once __DIR__ . '/includes/fungsi.php';

$kembali = url_kembali_aman($_SERVER['HTTP_REFERER'] ?? '', BASE_URL . '/index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    flash_set('gagal', 'Permintaan tidak valid. Silakan coba lagi.');
    redirect($kembali);
}

$email = strtolower(trim((string) ($_POST['email'] ?? '')));
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    flash_set('gagal', 'Alamat email tidak valid.');
    redirect($kembali);
}

try {
    $stmt = $pdo->prepare('SELECT id FROM langganan WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        flash_set('sukses', 'Email tersebut sudah terdaftar. Terima kasih.');
        redirect($kembali);
    }

    $stmt = $pdo->prepare('INSERT INTO langganan (email) VALUES (?)');
    $stmt->execute([$email]);
    flash_set('sukses', 'Pendaftaran berhasil. Info produk baru akan dikirim ke emailmu.');
} catch (PDOException $e) {
    if ((string) $e->getCode() === '23000') {
        flash_set('sukses', 'Email tersebut sudah terdaftar. Terima kasih.');
    } else {
        error_log('[Rerajut Langganan Error] ' . $e->getMessage());
        flash_set('gagal', 'Pendaftaran gagal diproses. Silakan coba lagi.');
    }
}
redirect($kembali);
