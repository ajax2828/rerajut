<?php
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kembali = url_kembali_aman($_SERVER['HTTP_REFERER'] ?? '', BASE_URL . '/admin/produk.php');
    if (!csrf_valid()) {
        flash_set('gagal', 'Sesi tidak valid.');
        redirect($kembali);
    }
    if (($_POST['aksi'] ?? '') === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM produk WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $produk = $stmt->fetch();
        if (!$produk) {
            flash_set('gagal', 'Produk tidak ditemukan.');
            redirect($kembali);
        }
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE gambar = ? AND id <> ?');
        $stmt->execute([$produk['gambar'], $id]);
        $dipakai = (int) $stmt->fetchColumn();
        $stmt = $pdo->prepare('DELETE FROM produk WHERE id = ?');
        $stmt->execute([$id]);
        if ($dipakai === 0 && !empty($produk['gambar'])) {
            $berkas = __DIR__ . '/../images/' . basename($produk['gambar']);
            if (is_file($berkas)) {
                @unlink($berkas);
            }
        }
        flash_set('sukses', 'Produk ' . $produk['nama'] . ' berhasil dihapus.');
        redirect($kembali);
    }
}

$kategoriList = kategori_all($pdo);
$cari = trim((string) ($_GET['q'] ?? ''));
$filterKategori = (int) ($_GET['kategori'] ?? 0);
$filterStok = (string) ($_GET['stok'] ?? '');
if (!in_array($filterStok, ['', 'tersedia', 'habis'], true)) {
    $filterStok = '';
}

$where = [];
$params = [];
if ($cari !== '') {
    $where[] = '(p.nama LIKE ? OR p.ukuran LIKE ?)';
    $params[] = '%' . $cari . '%';
    $params[] = '%' . $cari . '%';
}
if ($filterKategori > 0) {
    $where[] = 'p.kategori_id = ?';
    $params[] = $filterKategori;
}
if ($filterStok === 'tersedia') {
    $where[] = 'p.stok > 0';
} elseif ($filterStok === 'habis') {
    $where[] = 'p.stok < 1';
}
$sqlWhere = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$perHal = 20;
$stmtHitung = $pdo->prepare('SELECT COUNT(*) FROM produk p' . $sqlWhere);
$stmtHitung->execute($params);
$totalHasil = (int) $stmtHitung->fetchColumn();
$totalHal = max(1, (int) ceil($totalHasil / $perHal));
$hal = min($totalHal, max(1, (int) ($_GET['hal'] ?? 1)));
$offset = ($hal - 1) * $perHal;

$stmt = $pdo->prepare('SELECT p.*, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id' . $sqlWhere . ' ORDER BY p.id DESC LIMIT ' . $perHal . ' OFFSET ' . $offset);
$stmt->execute($params);
$produkList = $stmt->fetchAll();

$judul = 'Kelola Produk | Admin ' . SITE_NAMA;
$deskripsi = 'Kelola daftar produk ' . SITE_NAMA . '.';
$kanonikal = SITE_URL . '/admin/produk.php';
$robots = 'noindex, nofollow';
$halaman = 'admin-produk';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Panel pengelola</p>
        <h1>Kelola Produk</h1>
      </div>
      <a class="tombol tombol-kecil" href="<?= e(BASE_URL) ?>/admin/produk-form.php">Tambah Produk</a>
    </div>

    <div class="admin-panel">
      <form action="<?= e(BASE_URL) ?>/admin/produk.php" method="get" class="admin-filter">
        <input type="search" name="q" value="<?= e($cari) ?>" placeholder="Cari nama atau ukuran produk">
        <select name="kategori" aria-label="Filter kategori">
          <option value="0">Semua kategori</option>
          <?php foreach ($kategoriList as $kat): ?>
          <option value="<?= (int) $kat['id'] ?>" <?= $filterKategori === (int) $kat['id'] ? 'selected' : '' ?>><?= e($kat['nama']) ?> (<?= (int) $kat['jumlah'] ?>)</option>
          <?php endforeach; ?>
        </select>
        <select name="stok" aria-label="Filter stok">
          <option value="">Semua stok</option>
          <option value="tersedia" <?= $filterStok === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
          <option value="habis" <?= $filterStok === 'habis' ? 'selected' : '' ?>>Terjual / Habis</option>
        </select>
        <button type="submit" class="tombol tombol-kecil">Filter</button>
        <?php if ($cari !== '' || $filterKategori > 0 || $filterStok !== ''): ?>
        <a class="tautan-lain" href="<?= e(BASE_URL) ?>/admin/produk.php">Reset</a>
        <?php endif; ?>
      </form>

      <?php if (!$produkList): ?>
      <p><?= ($cari !== '' || $filterKategori > 0 || $filterStok !== '') ? 'Tidak ada produk yang cocok dengan filter.' : 'Belum ada produk. Tambahkan produk pertama melalui tombol tambah produk.' ?></p>
      <?php else: ?>
      <div class="tabel-gulir">
        <table class="tabel-admin">
          <thead>
            <tr>
              <th>Gambar</th>
              <th>Nama Produk</th>
              <th>Kategori</th>
              <th>Harga</th>
              <th>Stok</th>
              <th>Unggulan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($produkList as $produk): ?>
            <tr>
              <td><img class="tabel-thumb" src="<?= e(BASE_URL) ?>/images/<?= e($produk['gambar']) ?>" alt="<?= e($produk['nama']) ?>" width="60" height="80"></td>
              <td>
                <strong><?= e($produk['nama']) ?></strong><br>
                <small>Ukuran <?= e($produk['ukuran']) ?> / <?= e($produk['kondisi']) ?></small>
              </td>
              <td><?= e($produk['kategori']) ?></td>
              <td><?= rupiah($produk['harga']) ?></td>
              <td><?= (int) $produk['stok'] ?></td>
              <td><?= (int) $produk['unggulan'] === 1 ? 'Ya' : 'Tidak' ?></td>
              <td>
                <div class="aksi-tabel">
                  <a class="tombol tombol-kecil tombol-garis" href="<?= e(BASE_URL) ?>/admin/produk-form.php?id=<?= (int) $produk['id'] ?>">Ubah</a>
                  <form action="<?= e(BASE_URL) ?>/admin/produk.php" method="post" data-konfirmasi="Hapus produk <?= e($produk['nama']) ?>?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= (int) $produk['id'] ?>">
                    <button type="submit" class="tombol tombol-kecil tombol-hapus">Hapus</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($totalHal > 1): ?>
      <nav class="paginasi" aria-label="Navigasi halaman produk">
        <?php for ($i = 1; $i <= $totalHal; $i++): ?>
        <?php
        $query = ['hal' => $i];
        if ($cari !== '') {
            $query['q'] = $cari;
        }
        if ($filterKategori > 0) {
            $query['kategori'] = $filterKategori;
        }
        if ($filterStok !== '') {
            $query['stok'] = $filterStok;
        }
        ?>
        <a class="<?= $i === $hal ? 'aktif' : '' ?>" href="<?= e(BASE_URL) ?>/admin/produk.php?<?= e(http_build_query($query)) ?>"><?= $i ?></a>
        <?php endfor; ?>
      </nav>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
