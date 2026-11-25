<?php
require_once __DIR__ . '/includes/fungsi.php';

$kode = trim($_GET['kode'] ?? '');
$pesanan = null;
$itemPesanan = [];
if ($kode !== '') {
    $stmt = $pdo->prepare('SELECT * FROM pesanan WHERE kode = ? LIMIT 1');
    $stmt->execute([$kode]);
    $pesanan = $stmt->fetch();
    if ($pesanan) {
        $stmt = $pdo->prepare('SELECT * FROM pesanan_item WHERE pesanan_id = ? ORDER BY id ASC');
        $stmt->execute([$pesanan['id']]);
        $itemPesanan = $stmt->fetchAll();
    }
}

$judul = $pesanan ? 'Pesanan ' . $pesanan['kode'] . ' Berhasil | Rerajut Thrift' : 'Pesanan Tidak Ditemukan | Rerajut Thrift';
$deskripsi = 'Konfirmasi pesanan Rerajut Thrift beserta instruksi pembayaran.';
$kanonikal = SITE_URL . '/pesanan-sukses.php';
$halaman = 'pesanan';
$robots = 'noindex, nofollow';
require_once __DIR__ . '/includes/header.php';
?>
<?php if (!$pesanan): ?>
<section class="bagian">
  <div class="wadah kosong">
    <h1>Pesanan tidak ditemukan</h1>
    <p>Kode pesanan tidak cocok. Periksa kembali kode yang kamu simpan.</p>
    <a class="tombol" href="<?= e(BASE_URL) ?>/lacak.php">Lacak Pesanan</a>
  </div>
</section>
<?php else: ?>
<section class="kepala-halaman kepala-sukses">
  <div class="wadah">
    <p class="label-kecil">Pesanan diterima</p>
    <h1>Terima kasih, <?= e($pesanan['nama']) ?></h1>
    <p>Pesananmu sudah kami catat. Simpan kode pesanan di bawah untuk melacak status.</p>
    <p class="kode-besar"><?= e($pesanan['kode']) ?></p>
  </div>
</section>

<?php
$metodeNonCod = in_array($pesanan['metode_bayar'], ['Transfer Bank', 'E-Wallet'], true);
$lewatBatas = !empty($pesanan['batas_bayar']) && time() > strtotime($pesanan['batas_bayar']);
?>
<section class="bagian" data-simpan-pesanan="<?= e($pesanan['kode']) ?>">
  <div class="wadah sukses-grid">
    <div class="sukses-utama">
      <div class="kotak-info" id="pembayaran">
        <h2>Instruksi pembayaran</h2>
        <?php if ($pesanan['metode_bayar'] === 'Transfer Bank'): ?>
        <p>Silakan transfer sebesar <strong><?= rupiah($pesanan['total']) ?></strong> ke rekening berikut.</p>
        <div class="rekening">
          <p><span>Bank</span><strong>BCA</strong></p>
          <p><span>Nomor rekening</span><strong>1234567890</strong></p>
          <p><span>Atas nama</span><strong>Rerajut Thrift</strong></p>
        </div>
        <?php elseif ($pesanan['metode_bayar'] === 'E-Wallet'): ?>
        <p>Silakan kirim pembayaran sebesar <strong><?= rupiah($pesanan['total']) ?></strong> ke akun e-wallet berikut.</p>
        <div class="rekening">
          <p><span>Layanan</span><strong>GoPay, OVO, DANA</strong></p>
          <p><span>Nomor</span><strong><?= e(SITE_TELEPON) ?></strong></p>
          <p><span>Atas nama</span><strong>Rerajut Thrift</strong></p>
        </div>
        <?php else: ?>
        <p>Pesananmu menggunakan metode bayar di tempat. Siapkan uang tunai sebesar <strong><?= rupiah($pesanan['total']) ?></strong> saat kurir tiba.</p>
        <p>Kurir akan menghubungi nomor <?= e($pesanan['telepon']) ?> sebelum melakukan pengiriman. Status pesanan saat ini sudah masuk proses.</p>
        <?php endif; ?>

        <?php if ($metodeNonCod): ?>
        <?php if ($pesanan['batas_bayar']): ?>
        <p class="batas-bayar">Batas upload bukti pembayaran: <strong><?= date('d-m-Y H:i', strtotime($pesanan['batas_bayar'])) ?> WIB</strong></p>
        <?php endif; ?>

        <?php if ($pesanan['bukti_bayar']): ?>
        <div class="bukti-terunggah">
          <h3>Bukti pembayaran sudah diunggah</h3>
          <p>Diunggah pada <?= date('d-m-Y H:i', strtotime($pesanan['dibayar_pada'])) ?> WIB. Pembayaran sedang diperiksa oleh admin.</p>
          <a href="<?= e(BASE_URL) ?>/uploads/bukti/<?= e($pesanan['bukti_bayar']) ?>" target="_blank" rel="noopener">
            <img src="<?= e(BASE_URL) ?>/uploads/bukti/<?= e($pesanan['bukti_bayar']) ?>" alt="Bukti pembayaran <?= e($pesanan['kode']) ?>" width="220" height="293">
          </a>
        </div>
        <?php elseif ($pesanan['status'] === 'Menunggu Pembayaran'): ?>
        <?php if ($lewatBatas): ?>
        <div class="kotak-galat">
          <p>Batas waktu upload bukti pembayaran sudah lewat. Silakan hubungi kami melalui halaman kontak dengan menyertakan kode pesanan.</p>
        </div>
        <?php else: ?>
        <div class="form-upload">
          <h3>Upload bukti pembayaran</h3>
          <p>Unggah foto atau tangkapan layar bukti transfer. Format JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.</p>
          <form action="<?= e(BASE_URL) ?>/upload-bukti.php" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="kode" value="<?= e($pesanan['kode']) ?>">
            <div class="kolom-form">
              <label for="bukti">File bukti pembayaran</label>
              <input type="file" id="bukti" name="bukti" accept="image/jpeg,image/png,image/webp" required>
            </div>
            <button type="submit" class="tombol">Kirim Bukti Pembayaran</button>
          </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
      </div>

      <div class="kotak-info">
        <h2>Rincian pesanan</h2>
        <ul class="daftar-pesanan">
          <?php foreach ($itemPesanan as $item): ?>
          <li>
            <span><?= e($item['nama_produk']) ?> ukuran <?= e($item['ukuran']) ?> x <?= (int) $item['jumlah'] ?></span>
            <strong><?= rupiah($item['subtotal']) ?></strong>
          </li>
          <?php endforeach; ?>
        </ul>
        <div class="ringkasan-baris">
          <span>Subtotal</span>
          <strong><?= rupiah($pesanan['subtotal']) ?></strong>
        </div>
        <div class="ringkasan-baris">
          <span>Ongkir</span>
          <strong><?= (int) $pesanan['ongkir'] === 0 ? 'Gratis' : rupiah($pesanan['ongkir']) ?></strong>
        </div>
        <div class="ringkasan-total">
          <span>Total</span>
          <strong><?= rupiah($pesanan['total']) ?></strong>
        </div>
      </div>
    </div>

    <aside class="ringkasan">
      <h2>Data Pengiriman</h2>
      <p><strong><?= e($pesanan['nama']) ?></strong></p>
      <p><?= e($pesanan['telepon']) ?><br><?= e($pesanan['email']) ?></p>
      <p><?= nl2br(e($pesanan['alamat'])) ?><br><?= e($pesanan['kota']) ?>, <?= e($pesanan['provinsi']) ?> <?= e($pesanan['kode_pos']) ?></p>
      <p><span>Metode bayar</span><br><strong><?= e($pesanan['metode_bayar']) ?></strong></p>
      <p><span>Status</span><br><strong><?= e($pesanan['status']) ?></strong></p>
      <p><span>Tanggal pesanan</span><br><?= date('d-m-Y H:i', strtotime($pesanan['dibuat_pada'])) ?> WIB</p>
      <a class="tombol tombol-lebar" href="<?= e(BASE_URL) ?>/lacak.php?kode=<?= e($pesanan['kode']) ?>">Lacak Pesanan</a>
      <a class="tombol tombol-lebar tombol-garis tombol-jarak" href="<?= e(BASE_URL) ?>/pesanan-saya.php">Pesanan Saya</a>
      <p class="ringkasan-catatan">Kode pesanan juga tersimpan otomatis di perangkat ini melalui halaman Pesanan Saya.</p>
    </aside>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
