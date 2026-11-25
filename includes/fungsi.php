<?php
require_once __DIR__ . '/../config/koneksi.php';

function e($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka) {
    return 'Rp ' . number_format((int) $angka, 0, ',', '.');
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid() {
    $token = $_POST['csrf'] ?? '';
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function flash_set($tipe, $pesan) {
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function flash_ambil() {
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function keranjang() {
    return $_SESSION['keranjang'] ?? [];
}

function keranjang_tambah($id, $jumlah = 1) {
    $id = (int) $id;
    $jumlah = max(1, (int) $jumlah);
    $_SESSION['keranjang'][$id] = ($_SESSION['keranjang'][$id] ?? 0) + $jumlah;
}

function keranjang_set($id, $jumlah) {
    $id = (int) $id;
    $jumlah = (int) $jumlah;
    if ($jumlah < 1) {
        unset($_SESSION['keranjang'][$id]);
        return;
    }
    $_SESSION['keranjang'][$id] = $jumlah;
}

function keranjang_hapus($id) {
    unset($_SESSION['keranjang'][(int) $id]);
}

function keranjang_reset() {
    unset($_SESSION['keranjang']);
}

function keranjang_jumlah() {
    return array_sum(keranjang());
}

function keranjang_item(PDO $pdo) {
    $isi = keranjang();
    if (!$isi) {
        return [];
    }
    $ids = array_keys($isi);
    $placeholder = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id IN ($placeholder)");
    $stmt->execute($ids);
    $produk = [];
    foreach ($stmt as $row) {
        $row['jumlah'] = $isi[$row['id']];
        $row['subtotal'] = $row['harga'] * $row['jumlah'];
        $produk[] = $row;
    }
    usort($produk, function ($a, $b) {
        return $a['id'] <=> $b['id'];
    });
    return $produk;
}

function keranjang_subtotal(PDO $pdo) {
    $total = 0;
    foreach (keranjang_item($pdo) as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

function ongkir_hitung($subtotal) {
    return $subtotal >= GRATIS_ONGKIR_MIN ? 0 : ONGKIR;
}

function kode_pesanan() {
    return 'RJT-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

function slug_aman($teks) {
    $teks = strtolower(trim($teks));
    $teks = preg_replace('/[^a-z0-9]+/', '-', $teks);
    return trim($teks, '-');
}

function produk_all(PDO $pdo, $kategori = '', $cari = '', $urut = 'terbaru', $limit = 12, $offset = 0) {
    $where = [];
    $params = [];
    if ($kategori !== '') {
        $where[] = 'k.slug = ?';
        $params[] = $kategori;
    }
    if ($cari !== '') {
        $where[] = '(p.nama LIKE ? OR p.deskripsi LIKE ?)';
        $params[] = '%' . $cari . '%';
        $params[] = '%' . $cari . '%';
    }
    $sql = 'SELECT p.*, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $order = 'p.dibuat_pada DESC, p.id DESC';
    if ($urut === 'termurah') {
        $order = 'p.harga ASC';
    } elseif ($urut === 'termahal') {
        $order = 'p.harga DESC';
    } elseif ($urut === 'nama') {
        $order = 'p.nama ASC';
    }
    $sql .= ' ORDER BY ' . $order . ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function produk_hitung(PDO $pdo, $kategori = '', $cari = '') {
    $where = [];
    $params = [];
    if ($kategori !== '') {
        $where[] = 'k.slug = ?';
        $params[] = $kategori;
    }
    if ($cari !== '') {
        $where[] = '(p.nama LIKE ? OR p.deskripsi LIKE ?)';
        $params[] = '%' . $cari . '%';
        $params[] = '%' . $cari . '%';
    }
    $sql = 'SELECT COUNT(*) FROM produk p JOIN kategori k ON k.id = p.kategori_id';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function produk_by_slug(PDO $pdo, $slug) {
    $stmt = $pdo->prepare('SELECT p.*, k.nama AS kategori, k.slug AS kategori_slug FROM produk p JOIN kategori k ON k.id = p.kategori_id WHERE p.slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function produk_terkait(PDO $pdo, $kategoriId, $id, $limit = 4) {
    $stmt = $pdo->prepare('SELECT p.*, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id WHERE p.kategori_id = ? AND p.id <> ? AND p.stok > 0 ORDER BY RAND() LIMIT ' . (int) $limit);
    $stmt->execute([$kategoriId, $id]);
    return $stmt->fetchAll();
}

function kategori_all(PDO $pdo) {
    return $pdo->query('SELECT k.*, COUNT(p.id) AS jumlah FROM kategori k LEFT JOIN produk p ON p.kategori_id = k.id GROUP BY k.id ORDER BY k.nama ASC')->fetchAll();
}
