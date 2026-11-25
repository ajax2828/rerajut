<?php
require_once __DIR__ . '/includes/fungsi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/index.php');
}

$kode = trim($_POST['kode'] ?? '');
$kembali = BASE_URL . '/pesanan-sukses.php?kode=' . urlencode($kode);

if (!csrf_valid()) {
    flash_set('gagal', 'Sesi tidak valid. Silakan coba lagi.');
    redirect($kembali);
}

$stmt = $pdo->prepare('SELECT * FROM pesanan WHERE kode = ? LIMIT 1');
$stmt->execute([$kode]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    flash_set('gagal', 'Pesanan tidak ditemukan.');
    redirect(BASE_URL . '/index.php');
}

if ($pesanan['metode_bayar'] === 'COD') {
    flash_set('gagal', 'Pesanan COD tidak memerlukan bukti pembayaran.');
    redirect($kembali);
}

if ($pesanan['status'] !== 'Menunggu Pembayaran' || !empty($pesanan['bukti_bayar'])) {
    flash_set('gagal', 'Bukti pembayaran sudah diunggah atau status pesanan sudah berubah.');
    redirect($kembali);
}

if (!empty($pesanan['batas_bayar']) && time() > strtotime($pesanan['batas_bayar'])) {
    flash_set('gagal', 'Batas waktu upload bukti pembayaran sudah lewat. Silakan hubungi kami melalui halaman kontak.');
    redirect($kembali);
}

if (empty($_FILES['bukti']) || ($_FILES['bukti']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    flash_set('gagal', 'Pilih file bukti pembayaran terlebih dahulu.');
    redirect($kembali);
}

$file = $_FILES['bukti'];
if ($file['size'] > 2 * 1024 * 1024) {
    flash_set('gagal', 'Ukuran file maksimal 2 MB.');
    redirect($kembali);
}

$izin = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!isset($izin[$mime])) {
    flash_set('gagal', 'Format file harus JPG, PNG, atau WEBP.');
    redirect($kembali);
}

$folder = __DIR__ . '/uploads/bukti';
$namaFile = preg_replace('/[^A-Za-z0-9-]/', '', $pesanan['kode']) . '-' . bin2hex(random_bytes(5)) . '.' . $izin[$mime];
$tujuan = $folder . '/' . $namaFile;

if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
    flash_set('gagal', 'File gagal diunggah. Silakan coba lagi.');
    redirect($kembali);
}

$stmt = $pdo->prepare("UPDATE pesanan SET bukti_bayar = ?, dibayar_pada = NOW(), status = 'Pembayaran Diproses' WHERE id = ?");
$stmt->execute([$namaFile, $pesanan['id']]);

flash_set('sukses', 'Bukti pembayaran berhasil diunggah. Pesanan akan segera kami periksa.');
redirect(BASE_URL . '/pesanan-sukses.php?kode=' . urlencode($pesanan['kode']) . '#pembayaran');
