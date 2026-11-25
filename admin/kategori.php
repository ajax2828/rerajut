<?php
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('gagal', 'Sesi tidak valid.');
        redirect(BASE_URL . '/admin/kategori.php');
    }

    $aksi = (string) ($_POST['aksi'] ?? '');
    if ($aksi === 'tambah') {
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $slug = slug_aman($nama, 75);
        if (mb_strlen($nama) < 2 || mb_strlen($nama) > 80 || $slug === '') {
            flash_set('gagal', 'Nama kategori harus antara 2 sampai 80 karakter.');
        } else {
            $stmtCek = $pdo->prepare('SELECT id FROM kategori WHERE slug = ? LIMIT 1');
            $stmtCek->execute([$slug]);
            if ($stmtCek->fetch()) {
                flash_set('gagal', 'Kategori dengan nama tersebut sudah ada.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO kategori (nama, slug) VALUES (?, ?)');
                $stmt->execute([$nama, $slug]);
                flash_set('sukses', 'Kategori ' . $nama . ' berhasil ditambahkan.');
            }
        }
    } elseif ($aksi === 'ubah') {
        $id = (int) ($_POST['id'] ?? 0);
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $slug = slug_aman($nama, 75);
        if ($id < 1 || mb_strlen($nama) < 2 || mb_strlen($nama) > 80 || $slug === '') {
            flash_set('gagal', 'Nama kategori tidak valid (2 sampai 80 karakter).');
        } else {
            $stmtCek = $pdo->prepare('SELECT id FROM kategori WHERE slug = ? AND id <> ? LIMIT 1');
            $stmtCek->execute([$slug, $id]);
            if ($stmtCek->fetch()) {
                flash_set('gagal', 'Slug kategori sudah digunakan kategori lain.');
            } else {
                $stmt = $pdo->prepare('UPDATE kategori SET nama = ?, slug = ? WHERE id = ?');
                $stmt->execute([$nama, $slug, $id]);
                flash_set('sukses', 'Kategori berhasil diperbarui.');
            }
        }
    } elseif ($aksi === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmtCek = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE kategori_id = ?');
        $stmtCek->execute([$id]);
        $dipakai = (int) $stmtCek->fetchColumn();
        if ($dipakai > 0) {
            flash_set('gagal', 'Kategori tidak dapat dihapus karena masih dipakai oleh ' . $dipakai . ' produk.');
        } else {
            $stmt = $pdo->prepare('DELETE FROM kategori WHERE id = ?');
            $stmt->execute([$id]);
            flash_set('sukses', 'Kategori berhasil dihapus.');
        }
    }
    redirect(BASE_URL . '/admin/kategori.php');
}

$kategoriList = kategori_all($pdo);

$judul = 'Kelola Kategori | Admin ' . SITE_NAMA;
$deskripsi = 'Kelola kategori produk ' . SITE_NAMA . '.';
$kanonikal = SITE_URL . '/admin/kategori.php';
$robots = 'noindex, nofollow';
$halaman = 'admin-kategori';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bagian">
  <div class="wadah wadah-sempit">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Panel pengelola</p>
        <h1>Kelola Kategori</h1>
      </div>
      <a class="tautan-lain" href="<?= e(BASE_URL) ?>/admin/produk.php">Lihat Produk</a>
    </div>

    <div class="admin-panel">
      <h2>Tambah Kategori Baru</h2>
      <form action="<?= e(BASE_URL) ?>/admin/kategori.php" method="post" class="admin-filter">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="tambah">
        <input type="text" name="nama" maxlength="80" placeholder="Nama kategori baru, misal: Jaket" required>
        <button type="submit" class="tombol tombol-kecil">Tambah</button>
      </form>
    </div>

    <div class="admin-panel">
      <h2>Daftar Kategori (<?= count($kategoriList) ?>)</h2>
      <?php if (!$kategoriList): ?>
      <p>Belum ada kategori.</p>
      <?php else: ?>
      <div class="tabel-gulir">
        <table class="tabel-admin">
          <thead>
            <tr>
              <th>Nama Kategori</th>
              <th>Slug</th>
              <th>Produk</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($kategoriList as $kat): ?>
            <tr>
              <td>
                <form action="<?= e(BASE_URL) ?>/admin/kategori.php" method="post" class="form-status">
                  <?= csrf_field() ?>
                  <input type="hidden" name="aksi" value="ubah">
                  <input type="hidden" name="id" value="<?= (int) $kat['id'] ?>">
                  <input type="text" name="nama" value="<?= e($kat['nama']) ?>" maxlength="80" aria-label="Nama kategori <?= e($kat['nama']) ?>" required>
                  <button type="submit" class="tombol tombol-kecil">Simpan</button>
                </form>
              </td>
              <td><code><?= e($kat['slug']) ?></code></td>
              <td><?= (int) $kat['jumlah'] ?></td>
              <td>
                <?php if ((int) $kat['jumlah'] > 0): ?>
                <small>Dipakai produk</small>
                <?php else: ?>
                <form action="<?= e(BASE_URL) ?>/admin/kategori.php" method="post" data-konfirmasi="Hapus kategori <?= e($kat['nama']) ?>?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="aksi" value="hapus">
                  <input type="hidden" name="id" value="<?= (int) $kat['id'] ?>">
                  <button type="submit" class="tombol tombol-kecil tombol-hapus">Hapus</button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
