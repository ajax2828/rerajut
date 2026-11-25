<?php
require_once __DIR__ . '/includes/fungsi.php';

$items = keranjang_item($pdo);
if (!$items) {
    flash_set('gagal', 'Keranjang masih kosong. Pilih produk terlebih dahulu.');
    redirect(BASE_URL . '/produk.php');
}

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['subtotal'];
}
$ongkir = ongkir_hitung($subtotal);
$total = $subtotal + $ongkir;

$galat = [];
$data = [
    'nama' => '',
    'email' => '',
    'telepon' => '',
    'alamat' => '',
    'kota' => '',
    'provinsi' => '',
    'kode_pos' => '',
    'catatan' => '',
    'metode_bayar' => 'Transfer Bank',
];
$metodeBayar = ['Transfer Bank', 'E-Wallet', 'COD'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $galat[] = 'Sesi tidak valid. Silakan muat ulang halaman.';
    }
    foreach ($data as $kunci => $nilai) {
        $data[$kunci] = trim($_POST[$kunci] ?? '');
    }
    if (mb_strlen($data['nama']) < 3) {
        $galat[] = 'Nama lengkap minimal 3 karakter.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $galat[] = 'Alamat email tidak valid.';
    }
    if (mb_strlen(preg_replace('/[^0-9]/', '', $data['telepon'])) < 9) {
        $galat[] = 'Nomor telepon minimal 9 digit.';
    }
    if (mb_strlen($data['alamat']) < 10) {
        $galat[] = 'Alamat lengkap minimal 10 karakter.';
    }
    if ($data['kota'] === '') {
        $galat[] = 'Kota atau kabupaten wajib diisi.';
    }
    if ($data['provinsi'] === '') {
        $galat[] = 'Provinsi wajib diisi.';
    }
    if (!preg_match('/^[0-9]{5}$/', $data['kode_pos'])) {
        $galat[] = 'Kode pos harus terdiri dari 5 angka.';
    }
    if (!in_array($data['metode_bayar'], $metodeBayar, true)) {
        $galat[] = 'Metode pembayaran tidak valid.';
    }

    if (!$galat) {
        try {
            $pdo->beginTransaction();
            $subtotalDb = 0;
            $itemDb = [];
            foreach ($items as $item) {
                $stmt = $pdo->prepare('SELECT id, nama, harga, ukuran, stok FROM produk WHERE id = ? FOR UPDATE');
                $stmt->execute([$item['id']]);
                $row = $stmt->fetch();
                if (!$row || (int) $row['stok'] < (int) $item['jumlah']) {
                    throw new RuntimeException('Stok produk ' . $item['nama'] . ' tidak mencukupi.');
                }
                $barisSubtotal = (int) $row['harga'] * (int) $item['jumlah'];
                $subtotalDb += $barisSubtotal;
                $itemDb[] = [
                    'produk_id' => $row['id'],
                    'nama' => $row['nama'],
                    'ukuran' => $row['ukuran'],
                    'harga' => (int) $row['harga'],
                    'jumlah' => (int) $item['jumlah'],
                    'subtotal' => $barisSubtotal,
                ];
            }
            $ongkirDb = ongkir_hitung($subtotalDb);
            $totalDb = $subtotalDb + $ongkirDb;
            $kode = kode_pesanan();
            $statusAwal = $data['metode_bayar'] === 'COD' ? 'Diproses' : 'Menunggu Pembayaran';
            $batasBayar = $data['metode_bayar'] === 'COD' ? null : date('Y-m-d H:i:s', time() + 86400);
            $stmt = $pdo->prepare('INSERT INTO pesanan (kode, nama, email, telepon, alamat, kota, provinsi, kode_pos, catatan, metode_bayar, subtotal, ongkir, total, status, batas_bayar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $kode,
                $data['nama'],
                $data['email'],
                $data['telepon'],
                $data['alamat'],
                $data['kota'],
                $data['provinsi'],
                $data['kode_pos'],
                $data['catatan'],
                $data['metode_bayar'],
                $subtotalDb,
                $ongkirDb,
                $totalDb,
                $statusAwal,
                $batasBayar,
            ]);
            $pesananId = (int) $pdo->lastInsertId();
            $stmtItem = $pdo->prepare('INSERT INTO pesanan_item (pesanan_id, produk_id, nama_produk, ukuran, harga, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmtStok = $pdo->prepare('UPDATE produk SET stok = stok - ? WHERE id = ?');
            foreach ($itemDb as $item) {
                $stmtItem->execute([$pesananId, $item['produk_id'], $item['nama'], $item['ukuran'], $item['harga'], $item['jumlah'], $item['subtotal']]);
                $stmtStok->execute([$item['jumlah'], $item['produk_id']]);
            }
            $pdo->commit();
            keranjang_reset();
            redirect(BASE_URL . '/pesanan-sukses.php?kode=' . urlencode($kode));
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $galat[] = $e instanceof RuntimeException ? $e->getMessage() : 'Pesanan gagal diproses. Silakan coba lagi.';
        }
    }
}

$judul = 'Checkout Pesanan | Rerajut Thrift';
$deskripsi = 'Lengkapi data pengiriman dan pilih metode pembayaran untuk menyelesaikan pesanan di Rerajut Thrift.';
$kanonikal = SITE_URL . '/checkout.php';
$halaman = 'checkout';
$robots = 'noindex, nofollow';
require_once __DIR__ . '/includes/header.php';
?>
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="<?= e(BASE_URL) ?>/index.php">Beranda</a>
      <span>/</span>
      <a href="<?= e(BASE_URL) ?>/keranjang.php">Keranjang</a>
      <span>/</span>
      <span>Checkout</span>
    </nav>
    <h1>Checkout</h1>
    <p>Isi data pengiriman dengan lengkap agar pesanan tidak tertunda.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah checkout-grid">
    <div class="checkout-form">
      <?php if ($galat): ?>
      <div class="kotak-galat">
        <h2>Periksa kembali data berikut</h2>
        <ul>
          <?php foreach ($galat as $g): ?>
          <li><?= e($g) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <form action="<?= e(BASE_URL) ?>/checkout.php" method="post" id="form-checkout">
        <?= csrf_field() ?>
        <fieldset>
          <legend>Data Pembeli</legend>
          <div class="baris-form">
            <div class="kolom-form">
              <label for="nama">Nama lengkap</label>
              <input type="text" id="nama" name="nama" value="<?= e($data['nama']) ?>" required>
            </div>
            <div class="kolom-form">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" value="<?= e($data['email']) ?>" required>
            </div>
          </div>
          <div class="kolom-form">
            <label for="telepon">Nomor telepon atau WhatsApp</label>
            <input type="tel" id="telepon" name="telepon" value="<?= e($data['telepon']) ?>" placeholder="081234567890" required>
          </div>
        </fieldset>

        <fieldset>
          <legend>Alamat Pengiriman</legend>
          <div class="kolom-form">
            <label for="alamat">Alamat lengkap</label>
            <textarea id="alamat" name="alamat" rows="4" placeholder="Nama jalan, nomor rumah, RT, RW, kelurahan, kecamatan" required><?= e($data['alamat']) ?></textarea>
          </div>
          <div class="baris-form">
            <div class="kolom-form">
              <label for="kota">Kota atau kabupaten</label>
              <input type="text" id="kota" name="kota" value="<?= e($data['kota']) ?>" required>
            </div>
            <div class="kolom-form">
              <label for="provinsi">Provinsi</label>
              <input type="text" id="provinsi" name="provinsi" value="<?= e($data['provinsi']) ?>" required>
            </div>
            <div class="kolom-form">
              <label for="kode_pos">Kode pos</label>
              <input type="text" id="kode_pos" name="kode_pos" value="<?= e($data['kode_pos']) ?>" inputmode="numeric" maxlength="5" required>
            </div>
          </div>
          <div class="kolom-form">
            <label for="catatan">Catatan untuk penjual</label>
            <textarea id="catatan" name="catatan" rows="3" placeholder="Opsional, misalnya patokan alamat"><?= e($data['catatan']) ?></textarea>
          </div>
        </fieldset>

        <fieldset>
          <legend>Metode Pembayaran</legend>
          <div class="metode-grid">
            <?php foreach ($metodeBayar as $metode): ?>
            <label class="metode-kartu">
              <input type="radio" name="metode_bayar" value="<?= e($metode) ?>" <?= $data['metode_bayar'] === $metode ? 'checked' : '' ?>>
              <span>
                <strong><?= e($metode) ?></strong>
                <?php if ($metode === 'Transfer Bank'): ?>
                <small>BCA, BRI, atau Mandiri</small>
                <?php elseif ($metode === 'E-Wallet'): ?>
                <small>GoPay, OVO, atau DANA</small>
                <?php else: ?>
                <small>Bayar saat barang diterima</small>
                <?php endif; ?>
              </span>
            </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <button type="submit" class="tombol tombol-lebar">Buat Pesanan</button>
        <p class="checkout-catatan">Dengan menekan tombol ini, kamu menyetujui data yang diisi sudah benar.</p>
      </form>
    </div>

    <aside class="ringkasan ringkasan-checkout">
      <h2>Ringkasan Pesanan</h2>
      <ul class="ringkasan-produk">
        <?php foreach ($items as $item): ?>
        <li>
          <img src="<?= e(BASE_URL) ?>/images/<?= e($item['gambar']) ?>" alt="<?= e($item['nama']) ?>" width="120" height="160" loading="lazy">
          <div>
            <strong><?= e($item['nama']) ?></strong>
            <span>Ukuran <?= e($item['ukuran']) ?> x <?= (int) $item['jumlah'] ?></span>
          </div>
          <span><?= rupiah($item['subtotal']) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
      <div class="ringkasan-baris">
        <span>Subtotal</span>
        <strong><?= rupiah($subtotal) ?></strong>
      </div>
      <div class="ringkasan-baris">
        <span>Ongkir</span>
        <strong><?= $ongkir === 0 ? 'Gratis' : rupiah($ongkir) ?></strong>
      </div>
      <div class="ringkasan-total">
        <span>Total bayar</span>
        <strong><?= rupiah($total) ?></strong>
      </div>
      <a class="tautan-lain" href="<?= e(BASE_URL) ?>/keranjang.php">Ubah keranjang</a>
    </aside>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
