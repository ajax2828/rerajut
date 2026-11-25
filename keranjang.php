<?php
require_once __DIR__ . '/includes/fungsi.php';

no_cache_headers();
pesanan_batalkan_kedaluwarsa($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('gagal', 'Sesi tidak valid. Silakan coba lagi.');
        redirect(BASE_URL . '/keranjang.php');
    }
    $aksi = $_POST['aksi'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($aksi === 'tambah') {
        $stmt = $pdo->prepare('SELECT * FROM produk WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $produk = $stmt->fetch();
        if (!$produk || (int) $produk['stok'] < 1) {
            flash_set('gagal', 'Produk tidak tersedia atau sudah terjual.');
            redirect(BASE_URL . '/produk.php');
        }
        $jumlah = max(1, (int) ($_POST['jumlah'] ?? 1));
        $sekarang = $_SESSION['keranjang'][$id] ?? 0;
        $totalJumlah = min((int) $produk['stok'], $sekarang + $jumlah);
        keranjang_set($id, $totalJumlah);
        flash_set('sukses', $produk['nama'] . ' masuk ke keranjang.');
        if (($_POST['lanjut'] ?? '') === 'checkout') {
            redirect(BASE_URL . '/checkout.php');
        }
        redirect(BASE_URL . '/keranjang.php');
    }

    if ($aksi === 'ubah') {
        $stmt = $pdo->prepare('SELECT stok FROM produk WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $stok = (int) $stmt->fetchColumn();
        $jumlah = (int) ($_POST['jumlah'] ?? 1);
        if ($stok < 1) {
            keranjang_hapus($id);
            flash_set('gagal', 'Produk sudah terjual dan dihapus dari keranjang.');
        } else {
            keranjang_set($id, min($stok, max(1, $jumlah)));
            flash_set('sukses', 'Keranjang diperbarui.');
        }
        redirect(BASE_URL . '/keranjang.php');
    }

    if ($aksi === 'hapus') {
        keranjang_hapus($id);
        flash_set('sukses', 'Produk dihapus dari keranjang.');
        redirect(BASE_URL . '/keranjang.php');
    }

    flash_set('gagal', 'Aksi keranjang tidak valid.');
    redirect(BASE_URL . '/keranjang.php');
}

$penyesuaianKeranjang = [];
$items = keranjang_item($pdo, $penyesuaianKeranjang);
if ($penyesuaianKeranjang && empty($_SESSION['flash'])) {
    flash_set('gagal', implode(' ', $penyesuaianKeranjang));
}
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['subtotal'];
}
$ongkir = $items ? ongkir_hitung($subtotal) : 0;
$total = $subtotal + $ongkir;

$judul = 'Keranjang Belanja | Rerajut Thrift';
$deskripsi = 'Keranjang belanja Rerajut Thrift. Periksa produk pilihanmu sebelum melanjutkan ke checkout.';
$kanonikal = SITE_URL . '/keranjang.php';
$halaman = 'keranjang';
$robots = 'noindex, follow';
require_once __DIR__ . '/includes/header.php';
?>
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="<?= e(BASE_URL) ?>/index.php">Beranda</a>
      <span>/</span>
      <span>Keranjang</span>
    </nav>
    <h1>Keranjang Belanja</h1>
    <p>Periksa produk pilihanmu sebelum melanjutkan ke pembayaran.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah">
    <?php if (!$items): ?>
    <div class="kosong">
      <h2>Keranjang masih kosong</h2>
      <p>Yuk pilih baju thrift favoritmu dari katalog kami.</p>
      <a class="tombol" href="<?= e(BASE_URL) ?>/produk.php">Mulai Belanja</a>
    </div>
    <?php else: ?>
    <div class="keranjang-grid">
      <div class="keranjang-daftar">
        <?php foreach ($items as $item): ?>
        <article class="baris-keranjang">
          <a class="baris-gambar" href="<?= e(BASE_URL) ?>/detail.php?slug=<?= e($item['slug']) ?>">
            <img src="<?= e(BASE_URL) ?>/images/<?= e($item['gambar']) ?>" alt="<?= e($item['nama']) ?>" width="300" height="400" loading="lazy">
          </a>
          <div class="baris-info">
            <h2><a href="<?= e(BASE_URL) ?>/detail.php?slug=<?= e($item['slug']) ?>"><?= e($item['nama']) ?></a></h2>
            <p>Ukuran <?= e($item['ukuran']) ?> / <?= e($item['kondisi']) ?></p>
            <p class="baris-harga"><?= rupiah($item['harga']) ?></p>
            <form action="<?= e(BASE_URL) ?>/keranjang.php" method="post" class="form-jumlah">
              <?= csrf_field() ?>
              <input type="hidden" name="aksi" value="ubah">
              <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
              <label for="jumlah-<?= (int) $item['id'] ?>">Jumlah</label>
              <input type="number" id="jumlah-<?= (int) $item['id'] ?>" name="jumlah" value="<?= (int) $item['jumlah'] ?>" min="1" max="<?= (int) $item['stok'] ?>">
              <button type="submit" class="tombol tombol-kecil tombol-garis">Perbarui</button>
            </form>
          </div>
          <div class="baris-akhir">
            <p><?= rupiah($item['subtotal']) ?></p>
            <form action="<?= e(BASE_URL) ?>/keranjang.php" method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="aksi" value="hapus">
              <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
              <button type="submit" class="tautan-hapus">Hapus</button>
            </form>
          </div>
        </article>
        <?php endforeach; ?>
        <a class="tautan-lain" href="<?= e(BASE_URL) ?>/produk.php">Lanjut belanja</a>
      </div>
      <aside class="ringkasan">
        <h2>Ringkasan Pesanan</h2>
        <div class="ringkasan-baris">
          <span>Subtotal</span>
          <strong><?= rupiah($subtotal) ?></strong>
        </div>
        <div class="ringkasan-baris">
          <span>Ongkir</span>
          <strong><?= $ongkir === 0 ? 'Gratis' : rupiah($ongkir) ?></strong>
        </div>
        <?php if ($subtotal < GRATIS_ONGKIR_MIN): ?>
        <p class="catatan-ongkir">Tambah <?= rupiah(GRATIS_ONGKIR_MIN - $subtotal) ?> lagi untuk gratis ongkir.</p>
        <?php endif; ?>
        <div class="ringkasan-total">
          <span>Total</span>
          <strong><?= rupiah($total) ?></strong>
        </div>
        <a class="tombol tombol-lebar" href="<?= e(BASE_URL) ?>/checkout.php">Lanjut ke Checkout</a>
        <p class="ringkasan-catatan">Pembayaran dilakukan setelah data pesanan dikirim.</p>
      </aside>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
