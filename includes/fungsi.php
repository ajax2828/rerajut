<?php
require_once __DIR__ . '/../config/koneksi.php';

if (!function_exists('mb_strlen')) {
    function mb_strlen($str, $encoding = 'UTF-8') {
        $str = (string) $str;
        $count = @preg_match_all('/./us', $str, $matches);
        return $count === false ? strlen($str) : $count;
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr($str, $start, $length = null, $encoding = 'UTF-8') {
        $chars = @preg_split('//u', (string) $str, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($chars)) {
            return $length === null ? substr((string) $str, $start) : substr((string) $str, $start, $length);
        }
        return implode('', $length === null ? array_slice($chars, $start) : array_slice($chars, $start, $length));
    }
}

function e($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka) {
    return 'Rp ' . number_format((int) $angka, 0, ',', '.');
}

function tanggal_wib($waktu, $denganWib = true) {
    if ($waktu === null || trim((string) $waktu) === '') {
        return '-';
    }
    $ts = strtotime((string) $waktu);
    if ($ts === false) {
        return '-';
    }
    return date('d-m-Y H:i', $ts) . ($denganWib ? ' WIB' : '');
}

function no_cache_headers() {
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid() {
    $token = $_POST['csrf'] ?? '';
    return is_string($token)
        && !empty($_SESSION['csrf'])
        && is_string($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
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

function url_kembali_aman($referer, $fallback = null) {
    $fallback = $fallback ?? (BASE_URL . '/index.php');
    if (!is_string($referer) || trim($referer) === '') {
        return $fallback;
    }
    $ref = parse_url(trim($referer));
    $site = parse_url(SITE_URL);
    if (!is_array($ref) || !is_array($site)) {
        return $fallback;
    }
    $refScheme = strtolower((string) ($ref['scheme'] ?? ''));
    if ($refScheme !== 'http' && $refScheme !== 'https') {
        return $fallback;
    }
    $refHost = strtolower((string) ($ref['host'] ?? ''));
    $siteHost = strtolower((string) ($site['host'] ?? ''));
    if ($refHost === '' || $refHost !== $siteHost) {
        return $fallback;
    }
    if (($ref['port'] ?? null) !== ($site['port'] ?? null)) {
        return $fallback;
    }
    $path = (string) ($ref['path'] ?? '/');
    if ($path === '' || $path[0] !== '/' || strpos($path, '//') === 0) {
        return $fallback;
    }
    if (BASE_URL !== '' && $path !== BASE_URL && strpos($path, BASE_URL . '/') !== 0) {
        return $fallback;
    }
    $query = isset($ref['query']) && $ref['query'] !== '' ? '?' . $ref['query'] : '';
    return $path . $query;
}

function ukuran_ini_bytes($nilai) {
    $nilai = trim((string) $nilai);
    if ($nilai === '') {
        return 0;
    }
    $angka = (int) $nilai;
    $satuan = strtolower(substr($nilai, -1));
    if ($satuan === 'g') {
        return $angka * 1024 * 1024 * 1024;
    }
    if ($satuan === 'm') {
        return $angka * 1024 * 1024;
    }
    if ($satuan === 'k') {
        return $angka * 1024;
    }
    return $angka;
}

function post_terlalu_besar() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return false;
    }
    $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($contentLength <= 0 || !empty($_POST) || !empty($_FILES)) {
        return false;
    }
    $postMax = ukuran_ini_bytes(ini_get('post_max_size'));
    return $postMax > 0 && $contentLength > $postMax;
}

function client_ip() {
    $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'));
    return $ip !== '' ? $ip : '0.0.0.0';
}

function rate_limit_file($kunci) {
    $dir = rtrim(sys_get_temp_dir(), '/\\');
    return $dir . '/rerajut_rl_' . hash('sha256', $kunci . '|' . client_ip()) . '.json';
}

function rate_limit_baca($kunci, $jendelaDetik) {
    $sekarang = time();
    $file = rate_limit_file($kunci);
    $waktu = [];
    if (is_file($file) && is_readable($file)) {
        $json = @file_get_contents($file);
        $decoded = is_string($json) ? json_decode($json, true) : null;
        if (is_array($decoded)) {
            $waktu = $decoded;
        }
    } elseif (!empty($_SESSION['rate_limit'][$kunci]) && is_array($_SESSION['rate_limit'][$kunci])) {
        $waktu = $_SESSION['rate_limit'][$kunci];
    }
    $aktif = [];
    foreach ($waktu as $ts) {
        $ts = (int) $ts;
        if ($ts > $sekarang - $jendelaDetik) {
            $aktif[] = $ts;
        }
    }
    return $aktif;
}

function rate_limit_cek($kunci, $maksPercobaan, $jendelaDetik) {
    $aktif = rate_limit_baca($kunci, $jendelaDetik);
    if (count($aktif) < $maksPercobaan) {
        return ['terblokir' => false, 'sisa_detik' => 0];
    }
    $tertua = min($aktif);
    $sisa = max(1, ($tertua + $jendelaDetik) - time());
    return ['terblokir' => true, 'sisa_detik' => $sisa];
}

function rate_limit_catat($kunci, $jendelaDetik) {
    $aktif = rate_limit_baca($kunci, $jendelaDetik);
    $aktif[] = time();
    $_SESSION['rate_limit'][$kunci] = $aktif;
    $file = rate_limit_file($kunci);
    @file_put_contents($file, json_encode($aktif), LOCK_EX);
}

function rate_limit_reset($kunci) {
    unset($_SESSION['rate_limit'][$kunci]);
    $file = rate_limit_file($kunci);
    if (is_file($file)) {
        @unlink($file);
    }
}

function pastikan_folder($folder) {
    if (!is_dir($folder)) {
        if (!@mkdir($folder, 0755, true) && !is_dir($folder)) {
            return false;
        }
    }
    return is_writable($folder);
}

function validasi_gambar_upload($file, $maxBytes = 2097152) {
    if (!is_array($file) || !isset($file['error'])) {
        return ['ok' => false, 'pesan' => 'Pilih file gambar terlebih dahulu.'];
    }
    $err = (int) $file['error'];
    if ($err === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'pesan' => 'Pilih file gambar terlebih dahulu.'];
    }
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        return ['ok' => false, 'pesan' => 'Ukuran file terlalu besar. Maksimal 2 MB.'];
    }
    if ($err !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'pesan' => 'File gagal diunggah. Silakan coba lagi.'];
    }
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'pesan' => 'File unggahan tidak valid.'];
    }
    if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > $maxBytes) {
        return ['ok' => false, 'pesan' => 'Ukuran file maksimal ' . round($maxBytes / (1024 * 1024)) . ' MB.'];
    }

    $izin = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = null;
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
    }

    $infoGambar = @getimagesize($file['tmp_name']);
    if ($infoGambar === false || empty($infoGambar['mime'])) {
        return ['ok' => false, 'pesan' => 'File bukan gambar yang valid. Gunakan JPG, PNG, atau WEBP.'];
    }
    $mimeGambar = $infoGambar['mime'];
    if ($mime === null) {
        $mime = $mimeGambar;
    }

    if (!isset($izin[$mime]) || !isset($izin[$mimeGambar]) || $izin[$mime] !== $izin[$mimeGambar]) {
        return ['ok' => false, 'pesan' => 'Format file harus JPG, PNG, atau WEBP.'];
    }

    return [
        'ok' => true,
        'mime' => $mime,
        'ext' => $izin[$mime],
    ];
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

function keranjang_item(PDO $pdo, &$penyesuaian = null) {
    $penyesuaian = [];
    $isi = keranjang();
    if (!$isi) {
        return [];
    }
    $ids = array_map('intval', array_keys($isi));
    $placeholder = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id IN ($placeholder)");
    $stmt->execute($ids);
    $ditemukan = [];
    foreach ($stmt as $row) {
        $ditemukan[(int) $row['id']] = $row;
    }

    $produk = [];
    foreach ($isi as $id => $diminta) {
        $id = (int) $id;
        $diminta = (int) $diminta;
        if (!isset($ditemukan[$id])) {
            unset($_SESSION['keranjang'][$id]);
            $penyesuaian[] = 'Salah satu produk di keranjang sudah tidak tersedia dan dihapus otomatis.';
            continue;
        }
        $row = $ditemukan[$id];
        $stok = (int) $row['stok'];
        if ($stok < 1) {
            unset($_SESSION['keranjang'][$id]);
            $penyesuaian[] = $row['nama'] . ' sudah terjual dan dihapus dari keranjang.';
            continue;
        }
        $jumlah = min($stok, max(1, $diminta));
        if ($jumlah !== $diminta) {
            $_SESSION['keranjang'][$id] = $jumlah;
            $penyesuaian[] = 'Jumlah ' . $row['nama'] . ' disesuaikan mengikuti stok tersedia (' . $stok . ').';
        }
        $row['jumlah'] = $jumlah;
        $row['subtotal'] = (int) $row['harga'] * $jumlah;
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
    return 'RJT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function pesanan_status_daftar() {
    return ['Menunggu Pembayaran', 'Pembayaran Diproses', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
}

function pesanan_batalkan_kedaluwarsa(PDO $pdo) {
    static $sudahCek = false;
    if ($sudahCek) {
        return 0;
    }
    $sudahCek = true;

    try {
        $ids = $pdo->query("SELECT id FROM pesanan WHERE status = 'Menunggu Pembayaran' AND bukti_bayar IS NULL AND batas_bayar IS NOT NULL AND batas_bayar < NOW() LIMIT 20")->fetchAll(PDO::FETCH_COLUMN);
        if (!$ids) {
            return 0;
        }

        $dibatalkan = 0;
        $pdo->beginTransaction();
        $stmtLock = $pdo->prepare('SELECT id, status, bukti_bayar, batas_bayar FROM pesanan WHERE id = ? FOR UPDATE');
        $stmtItems = $pdo->prepare('SELECT produk_id, jumlah FROM pesanan_item WHERE pesanan_id = ?');
        $stmtStok = $pdo->prepare('UPDATE produk SET stok = stok + ? WHERE id = ?');
        $stmtBatal = $pdo->prepare("UPDATE pesanan SET status = 'Dibatalkan' WHERE id = ?");

        foreach ($ids as $id) {
            $stmtLock->execute([(int) $id]);
            $row = $stmtLock->fetch();
            if (!$row || $row['status'] !== 'Menunggu Pembayaran' || !empty($row['bukti_bayar'])) {
                continue;
            }
            if (empty($row['batas_bayar']) || strtotime($row['batas_bayar']) >= time()) {
                continue;
            }
            $stmtItems->execute([(int) $id]);
            foreach ($stmtItems->fetchAll() as $item) {
                $stmtStok->execute([(int) $item['jumlah'], (int) $item['produk_id']]);
            }
            $stmtBatal->execute([(int) $id]);
            $dibatalkan++;
        }
        $pdo->commit();
        return $dibatalkan;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[Rerajut Auto-Cancel Error] ' . $e->getMessage());
        return 0;
    }
}

function pesanan_ubah_status(PDO $pdo, $pesananId, $statusBaru) {
    $pesananId = (int) $pesananId;
    if ($pesananId < 1 || !in_array($statusBaru, pesanan_status_daftar(), true)) {
        return ['ok' => false, 'pesan' => 'Status pesanan tidak valid.'];
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT id, kode, status FROM pesanan WHERE id = ? FOR UPDATE');
        $stmt->execute([$pesananId]);
        $pesanan = $stmt->fetch();
        if (!$pesanan) {
            $pdo->rollBack();
            return ['ok' => false, 'pesan' => 'Pesanan tidak ditemukan.'];
        }

        $statusLama = $pesanan['status'];
        if ($statusLama === $statusBaru) {
            $pdo->commit();
            return ['ok' => true, 'pesan' => 'Status pesanan tetap ' . $statusBaru . '.'];
        }

        $stmtItems = $pdo->prepare('SELECT produk_id, nama_produk, jumlah FROM pesanan_item WHERE pesanan_id = ?');
        $stmtItems->execute([$pesananId]);
        $items = $stmtItems->fetchAll();

        if ($statusLama !== 'Dibatalkan' && $statusBaru === 'Dibatalkan') {
            $stmtKembalikan = $pdo->prepare('UPDATE produk SET stok = stok + ? WHERE id = ?');
            foreach ($items as $item) {
                $stmtKembalikan->execute([(int) $item['jumlah'], (int) $item['produk_id']]);
            }
        } elseif ($statusLama === 'Dibatalkan' && $statusBaru !== 'Dibatalkan') {
            $stmtCek = $pdo->prepare('SELECT id, nama, stok FROM produk WHERE id = ? FOR UPDATE');
            $stmtKurang = $pdo->prepare('UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?');
            foreach ($items as $item) {
                $stmtCek->execute([(int) $item['produk_id']]);
                $prod = $stmtCek->fetch();
                if (!$prod || (int) $prod['stok'] < (int) $item['jumlah']) {
                    $pdo->rollBack();
                    return ['ok' => false, 'pesan' => 'Pesanan tidak dapat diaktifkan kembali karena stok produk "' . $item['nama_produk'] . '" sudah habis atau tidak mencukupi.'];
                }
                $stmtKurang->execute([(int) $item['jumlah'], (int) $item['produk_id'], (int) $item['jumlah']]);
            }
        }

        $stmtUpdate = $pdo->prepare('UPDATE pesanan SET status = ? WHERE id = ?');
        $stmtUpdate->execute([$statusBaru, $pesananId]);
        $pdo->commit();

        $catatanStok = '';
        if ($statusLama !== 'Dibatalkan' && $statusBaru === 'Dibatalkan') {
            $catatanStok = ' Stok produk telah dikembalikan.';
        } elseif ($statusLama === 'Dibatalkan' && $statusBaru !== 'Dibatalkan') {
            $catatanStok = ' Stok produk telah dikurangi kembali.';
        }
        return ['ok' => true, 'pesan' => 'Status pesanan berhasil diperbarui menjadi ' . $statusBaru . '.' . $catatanStok];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[Rerajut Status Error] ' . $e->getMessage());
        return ['ok' => false, 'pesan' => 'Gagal memperbarui status pesanan. Silakan coba lagi.'];
    }
}

function slug_aman($teks, $maxLen = 160) {
    $teks = strtolower(trim((string) $teks));
    $teks = preg_replace('/[^a-z0-9]+/', '-', $teks);
    $teks = trim((string) $teks, '-');
    if ($maxLen > 0 && strlen($teks) > $maxLen) {
        $teks = rtrim(substr($teks, 0, $maxLen), '-');
    }
    return $teks;
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
    $order = '(p.stok > 0) DESC, p.dibuat_pada DESC, p.id DESC';
    if ($urut === 'termurah') {
        $order = '(p.stok > 0) DESC, p.harga ASC, p.id DESC';
    } elseif ($urut === 'termahal') {
        $order = '(p.stok > 0) DESC, p.harga DESC, p.id DESC';
    } elseif ($urut === 'nama') {
        $order = '(p.stok > 0) DESC, p.nama ASC';
    }
    $sql .= ' ORDER BY ' . $order . ' LIMIT ' . max(1, (int) $limit) . ' OFFSET ' . max(0, (int) $offset);
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
    $stmt = $pdo->prepare('SELECT p.*, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id WHERE p.kategori_id = ? AND p.id <> ? AND p.stok > 0 ORDER BY RAND() LIMIT ' . max(1, (int) $limit));
    $stmt->execute([$kategoriId, $id]);
    return $stmt->fetchAll();
}

function kategori_all(PDO $pdo) {
    return $pdo->query('SELECT k.id, k.nama, k.slug, COUNT(p.id) AS jumlah FROM kategori k LEFT JOIN produk p ON p.kategori_id = k.id GROUP BY k.id, k.nama, k.slug ORDER BY k.nama ASC')->fetchAll();
}
