<?php
require_once __DIR__ . '/includes/fungsi.php';

no_cache_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/index.php');
}

if (post_terlalu_besar()) {
    flash_set('gagal', 'Ukuran file terlalu besar. Pastikan ukuran bukti pembayaran maksimal 2 MB.');
    redirect(url_kembali_aman($_SERVER['HTTP_REFERER'] ?? '', BASE_URL . '/index.php'));
}

$kode = trim((string) ($_POST['kode'] ?? ''));
$kembali = $kode !== '' ? (BASE_URL . '/pesanan-sukses.php?kode=' . urlencode($kode)) : (BASE_URL . '/index.php');

if (!csrf_valid()) {
    flash_set('gagal', 'Sesi tidak valid. Silakan coba lagi.');
    redirect($kembali);
}

pesanan_batalkan_kedaluwarsa($pdo);

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

$hasilValidasi = validasi_gambar_upload($_FILES['bukti'] ?? null, 2 * 1024 * 1024);
if (!$hasilValidasi['ok']) {
    flash_set('gagal', $hasilValidasi['pesan']);
    redirect($kembali);
}

$folder = __DIR__ . '/uploads/bukti';
if (!pastikan_folder($folder)) {
    error_log('[Rerajut Upload Error] Folder tidak dapat ditulis: ' . $folder);
    flash_set('gagal', 'Folder penyimpanan bukti pembayaran belum siap. Silakan hubungi admin.');
    redirect($kembali);
}

$namaFile = preg_replace('/[^A-Za-z0-9-]/', '', $pesanan['kode']) . '-' . bin2hex(random_bytes(5)) . '.' . $hasilValidasi['ext'];
$tujuan = $folder . '/' . $namaFile;

if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $tujuan)) {
    error_log('[Rerajut Upload Error] Gagal memindahkan file ke: ' . $tujuan);
    flash_set('gagal', 'File gagal diunggah. Silakan coba lagi.');
    redirect($kembali);
}

try {
    $stmt = $pdo->prepare("UPDATE pesanan SET bukti_bayar = ?, dibayar_pada = NOW(), status = 'Pembayaran Diproses' WHERE id = ? AND status = 'Menunggu Pembayaran' AND bukti_bayar IS NULL");
    $stmt->execute([$namaFile, $pesanan['id']]);
    if ($stmt->rowCount() !== 1) {
        @unlink($tujuan);
        flash_set('gagal', 'Bukti pembayaran sudah diunggah atau status pesanan sudah berubah.');
        redirect($kembali);
    }
} catch (Throwable $e) {
    @unlink($tujuan);
    error_log('[Rerajut Upload DB Error] ' . $e->getMessage());
    flash_set('gagal', 'Gagal menyimpan bukti pembayaran. Silakan coba lagi.');
    redirect($kembali);
}

flash_set('sukses', 'Bukti pembayaran berhasil diunggah. Pesanan akan segera kami periksa.');
redirect(BASE_URL . '/pesanan-sukses.php?kode=' . urlencode($pesanan['kode']) . '#pembayaran');
