<?php
require_once __DIR__ . '/includes/fungsi.php';

no_cache_headers();
header('Content-Type: application/json; charset=utf-8');

pesanan_batalkan_kedaluwarsa($pdo);

$kode = trim((string) ($_GET['kode'] ?? ''));
if ($kode === '' || strlen($kode) > 40) {
    echo json_encode(['ditemukan' => false]);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM pesanan WHERE kode = ? LIMIT 1');
$stmt->execute([$kode]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    echo json_encode(['ditemukan' => false]);
    exit;
}

$stmt = $pdo->prepare('SELECT nama_produk, ukuran, jumlah, subtotal FROM pesanan_item WHERE pesanan_id = ? ORDER BY id ASC');
$stmt->execute([$pesanan['id']]);
$items = $stmt->fetchAll();

$metodeNonCod = in_array($pesanan['metode_bayar'], ['Transfer Bank', 'E-Wallet'], true);
$lewatBatas = !empty($pesanan['batas_bayar']) && time() > strtotime($pesanan['batas_bayar']);
$punyaBukti = !empty($pesanan['bukti_bayar']);

echo json_encode([
    'ditemukan' => true,
    'kode' => $pesanan['kode'],
    'nama' => $pesanan['nama'],
    'tanggal' => tanggal_wib($pesanan['dibuat_pada'], false),
    'total' => (int) $pesanan['total'],
    'status' => $pesanan['status'],
    'metode' => $pesanan['metode_bayar'],
    'batas_bayar' => !empty($pesanan['batas_bayar']) ? tanggal_wib($pesanan['batas_bayar'], false) : null,
    'bukti' => $punyaBukti,
    'bisa_upload' => $metodeNonCod && $pesanan['status'] === 'Menunggu Pembayaran' && !$punyaBukti && !$lewatBatas,
    'lewat_batas' => $lewatBatas && !$punyaBukti && $pesanan['status'] !== 'Selesai' && $pesanan['status'] !== 'Diproses' && $pesanan['status'] !== 'Dikirim',
    'items' => array_map(function ($item) {
        return [
            'nama' => $item['nama_produk'],
            'ukuran' => $item['ukuran'],
            'jumlah' => (int) $item['jumlah'],
            'subtotal' => (int) $item['subtotal'],
        ];
    }, $items),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
