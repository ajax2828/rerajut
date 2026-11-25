<?php
require_once __DIR__ . '/includes/fungsi.php';

http_response_code(404);
$judul = 'Halaman tidak ditemukan | Rerajut Thrift';
$deskripsi = 'Halaman yang kamu cari tidak tersedia di Rerajut Thrift.';
$kanonikal = SITE_URL . '/';
$halaman = '';
$robots = 'noindex, follow';
require_once __DIR__ . '/includes/header.php';
?>
<section class="bagian">
  <div class="wadah kosong">
    <h1>Halaman tidak ditemukan</h1>
    <p>Tautan mungkin sudah berubah atau produk sudah tidak tersedia.</p>
    <a class="tombol" href="<?= e(BASE_URL) ?>/index.php">Kembali ke Beranda</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
