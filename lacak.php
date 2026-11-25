<?php
require_once __DIR__ . '/includes/fungsi.php';

$kode = trim($_GET['kode'] ?? '');
$pesanan = null;
$itemPesanan = [];
$sudahCari = $kode !== '';

if ($sudahCari) {
    $stmt = $pdo->prepare('SELECT * FROM pesanan WHERE kode = ? LIMIT 1');
    $stmt->execute([$kode]);
    $pesanan = $stmt->fetch();
    if ($pesanan) {
        $stmt = $pdo->prepare('SELECT * FROM pesanan_item WHERE pesanan_id = ? ORDER BY id ASC');
        $stmt->execute([$pesanan['id']]);
        $itemPesanan = $stmt->fetchAll();
    }
}

$urutanStatus = ['Menunggu Pembayaran', 'Pembayaran Diproses', 'Diproses', 'Dikirim', 'Selesai'];
if ($pesanan && $pesanan['metode_bayar'] === 'COD') {
    $urutanStatus = ['Diproses', 'Dikirim', 'Selesai'];
}
$indeksStatus = $pesanan ? array_search($pesanan['status'], $urutanStatus, true) : false;
if ($indeksStatus === false) {
    $indeksStatus = $pesanan && $pesanan['status'] === 'Dibatalkan' ? -1 : 0;
}

$judul = 'Lacak Pesanan | Rerajut Thrift';
$deskripsi = 'Cek status pesanan baju thrift kamu di Rerajut Thrift dengan memasukkan kode pesanan.';
$kanonikal = SITE_URL . '/lacak.php';
$halaman = 'lacak';
$robots = 'noindex, follow';
require_once __DIR__ . '/includes/header.php';
?>
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="<?= e(BASE_URL) ?>/index.php">Beranda</a>
      <span>/</span>
      <span>Lacak Pesanan</span>
    </nav>
    <h1>Lacak Pesanan</h1>
    <p>Masukkan kode pesanan yang kamu terima setelah checkout.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah wadah-sempit">
    <form action="<?= e(BASE_URL) ?>/lacak.php" method="get" class="form-lacak">
      <label for="kode">Kode pesanan</label>
      <div class="form-lacak-baris">
        <input type="text" id="kode" name="kode" value="<?= e($kode) ?>" placeholder="Contoh: RJT-20261119-ABCDE" required>
        <button type="submit" class="tombol">Cek Status</button>
      </div>
    </form>

    <?php if ($sudahCari && !$pesanan): ?>
    <div class="kotak-galat">
      <h2>Pesanan tidak ditemukan</h2>
      <p>Pastikan kode pesanan ditulis dengan benar. Kode terdiri dari huruf dan angka tanpa spasi.</p>
    </div>
    <?php endif; ?>

    <?php if ($pesanan): ?>
    <div class="lacak-hasil">
      <div class="lacak-kepala">
        <div>
          <p class="label-kecil">Kode pesanan</p>
          <h2><?= e($pesanan['kode']) ?></h2>
        </div>
        <span class="status-lencana"><?= e($pesanan['status']) ?></span>
      </div>
      <ol class="linimasa">
        <?php foreach ($urutanStatus as $i => $status): ?>
        <li class="<?= $i <= $indeksStatus ? 'selesai' : '' ?>">
          <span class="titik"></span>
          <div>
            <strong><?= e($status) ?></strong>
            <?php if ($i === 0 && $pesanan['metode_bayar'] !== 'COD'): ?>
            <small>Pesanan dibuat pada <?= date('d-m-Y H:i', strtotime($pesanan['dibuat_pada'])) ?> WIB</small>
            <?php endif; ?>
          </div>
        </li>
        <?php endforeach; ?>
      </ol>
      <?php if ($pesanan['metode_bayar'] === 'COD'): ?>
      <p class="checkout-catatan">Pembayaran dilakukan secara tunai saat pesanan diterima.</p>
      <?php endif; ?>

      <div class="lacak-grid">
        <div>
          <h3>Produk yang dipesan</h3>
          <ul class="daftar-pesanan">
            <?php foreach ($itemPesanan as $item): ?>
            <li>
              <span><?= e($item['nama_produk']) ?> ukuran <?= e($item['ukuran']) ?> x <?= (int) $item['jumlah'] ?></span>
              <strong><?= rupiah($item['subtotal']) ?></strong>
            </li>
            <?php endforeach; ?>
          </ul>
          <div class="ringkasan-baris">
            <span>Ongkir</span>
            <strong><?= (int) $pesanan['ongkir'] === 0 ? 'Gratis' : rupiah($pesanan['ongkir']) ?></strong>
          </div>
          <div class="ringkasan-total">
            <span>Total</span>
            <strong><?= rupiah($pesanan['total']) ?></strong>
          </div>
        </div>
        <div>
          <h3>Alamat pengiriman</h3>
          <p><strong><?= e($pesanan['nama']) ?></strong><br><?= e($pesanan['telepon']) ?></p>
          <p><?= nl2br(e($pesanan['alamat'])) ?><br><?= e($pesanan['kota']) ?>, <?= e($pesanan['provinsi']) ?> <?= e($pesanan['kode_pos']) ?></p>
          <p>Metode bayar: <strong><?= e($pesanan['metode_bayar']) ?></strong></p>
          <?php if ($pesanan['batas_bayar']): ?>
          <p>Batas upload bukti: <strong><?= date('d-m-Y H:i', strtotime($pesanan['batas_bayar'])) ?> WIB</strong></p>
          <?php endif; ?>
          <?php if ($pesanan['bukti_bayar']): ?>
          <p>Bukti pembayaran: <a class="tautan-lain" href="<?= e(BASE_URL) ?>/uploads/bukti/<?= e($pesanan['bukti_bayar']) ?>" target="_blank" rel="noopener">Lihat bukti</a></p>
          <?php elseif (in_array($pesanan['metode_bayar'], ['Transfer Bank', 'E-Wallet'], true)): ?>
          <p>Bukti pembayaran belum diunggah.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
