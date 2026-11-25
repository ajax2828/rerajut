<?php
require_once __DIR__ . '/includes/fungsi.php';

header('Content-Type: application/json; charset=utf-8');

$kode = trim($_GET['kode'] ?? '');
if ($kode === '') {
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

echo json_encode([
    'ditemukan' => true,
    'kode' => $pesanan['kode'],
    'nama' => $pesanan['nama'],
    'tanggal' => date('d-m-Y H:i', strtotime($pesanan['dibuat_pada'])),
    'total' => (int) $pesanan['total'],
    'status' => $pesanan['status'],
    'metode' => $pesanan['metode_bayar'],
    'batas_bayar' => $pesanan['batas_bayar'] ? date('d-m-Y H:i', strtotime($pesanan['batas_bayar'])) : null,
    'bukti' => (bool) $pesanan['bukti_bayar'],
    'bisa_upload' => $metodeNonCod && $pesanan['status'] === 'Menunggu Pembayaran' && !$lewatBatas,
    'lewat_batas' => $lewatBatas,
    'items' => array_map(function ($item) {
        return [
            'nama' => $item['nama_produk'],
            'ukuran' => $item['ukuran'],
            'jumlah' => (int) $item['jumlah'],
            'subtotal' => (int) $item['subtotal'],
        ];
    }, $items),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
