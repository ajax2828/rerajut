<?php
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('gagal', 'Sesi tidak valid.');
        redirect(BASE_URL . '/admin/produk.php');
    }
    if (($_POST['aksi'] ?? '') === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM produk WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $produk = $stmt->fetch();
        if (!$produk) {
            flash_set('gagal', 'Produk tidak ditemukan.');
            redirect(BASE_URL . '/admin/produk.php');
        }
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE gambar = ? AND id <> ?');
        $stmt->execute([$produk['gambar'], $id]);
        $dipakai = (int) $stmt->fetchColumn();
        $stmt = $pdo->prepare('DELETE FROM produk WHERE id = ?');
        $stmt->execute([$id]);
        if ($dipakai === 0) {
            $berkas = __DIR__ . '/../images/' . basename($produk['gambar']);
            if (is_file($berkas)) {
                unlink($berkas);
            }
        }
        flash_set('sukses', 'Produk ' . $produk['nama'] . ' berhasil dihapus.');
        redirect(BASE_URL . '/admin/produk.php');
    }
}

$produkList = $pdo->query('SELECT p.*, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id ORDER BY p.id DESC')->fetchAll();

$judul = 'Kelola Produk | Admin Rerajut Thrift';
$deskripsi = 'Kelola daftar produk Rerajut Thrift.';
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
      <?php if (!$produkList): ?>
      <p>Belum ada produk. Tambahkan produk pertama melalui tombol tambah produk.</p>
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
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
