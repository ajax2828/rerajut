<?php
require_once __DIR__ . '/includes/fungsi.php';

$judul = 'Pesanan Saya | Rerajut Thrift';
$deskripsi = 'Daftar pesanan yang pernah kamu buat di Rerajut Thrift, lengkap dengan status dan tautan pembayaran.';
$kanonikal = SITE_URL . '/pesanan-saya.php';
$halaman = 'pesanan-saya';
$robots = 'noindex, follow';
require_once __DIR__ . '/includes/header.php';
?>
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="<?= e(BASE_URL) ?>/index.php">Beranda</a>
      <span>/</span>
      <span>Pesanan Saya</span>
    </nav>
    <h1>Pesanan Saya</h1>
    <p>Daftar pesanan yang tersimpan di perangkat ini. Data disimpan otomatis setelah checkout berhasil.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah wadah-sempit">
    <div id="pesanan-saya-muat" class="pesanan-saya-info">Memuat daftar pesanan.</div>
    <div id="pesanan-saya-kosong" class="kosong" hidden>
      <h2>Belum ada pesanan</h2>
      <p>Pesanan yang kamu buat akan muncul di halaman ini secara otomatis.</p>
      <a class="tombol" href="<?= e(BASE_URL) ?>/produk.php">Mulai Belanja</a>
    </div>
    <div id="daftar-pesanan-saya" class="daftar-pesanan-saya" data-api="<?= e(BASE_URL) ?>/api-pesanan.php" data-base="<?= e(BASE_URL) ?>"></div>
    <p class="catatan-perangkat">Daftar ini tersimpan di browser perangkat yang kamu pakai. Jika memakai perangkat lain, gunakan kode pesanan pada halaman lacak pesanan.</p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
