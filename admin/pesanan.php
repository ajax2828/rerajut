<?php
require_once __DIR__ . '/auth.php';

$kode = trim($_GET['kode'] ?? '');
$statusList = admin_status_pesanan();
$stmt = $pdo->prepare('SELECT * FROM pesanan WHERE kode = ? LIMIT 1');
$stmt->execute([$kode]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    flash_set('gagal', 'Pesanan tidak ditemukan.');
    redirect(BASE_URL . '/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('gagal', 'Sesi tidak valid.');
    } else {
        $status = $_POST['status'] ?? '';
        if (in_array($status, $statusList, true)) {
            $stmt = $pdo->prepare('UPDATE pesanan SET status = ? WHERE id = ?');
            $stmt->execute([$status, $pesanan['id']]);
            flash_set('sukses', 'Status pesanan diperbarui menjadi ' . $status . '.');
        } else {
            flash_set('gagal', 'Status tidak valid.');
        }
    }
    redirect(BASE_URL . '/admin/pesanan.php?kode=' . urlencode($pesanan['kode']));
}

$stmt = $pdo->prepare('SELECT * FROM pesanan_item WHERE pesanan_id = ? ORDER BY id ASC');
$stmt->execute([$pesanan['id']]);
$itemPesanan = $stmt->fetchAll();

$judul = 'Detail Pesanan ' . $pesanan['kode'] . ' | Admin Rerajut Thrift';
$deskripsi = 'Detail pesanan pelanggan Rerajut Thrift.';
$kanonikal = SITE_URL . '/admin/pesanan.php';
$robots = 'noindex, nofollow';
$halaman = 'admin-pesanan';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Detail pesanan</p>
        <h1><?= e($pesanan['kode']) ?></h1>
      </div>
      <a class="tautan-lain" href="<?= e(BASE_URL) ?>/admin/index.php">Kembali ke daftar</a>
    </div>

    <div class="admin-detail-grid">
      <div>
        <div class="admin-panel">
          <h2>Produk Dipesan</h2>
          <div class="tabel-gulir">
            <table class="tabel-admin">
              <thead>
                <tr><th>Produk</th><th>Ukuran</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr>
              </thead>
              <tbody>
                <?php foreach ($itemPesanan as $item): ?>
                <tr>
                  <td><?= e($item['nama_produk']) ?></td>
                  <td><?= e($item['ukuran']) ?></td>
                  <td><?= rupiah($item['harga']) ?></td>
                  <td><?= (int) $item['jumlah'] ?></td>
                  <td><?= rupiah($item['subtotal']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
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

        <div class="admin-panel">
          <h2>Data Pembeli</h2>
          <dl class="admin-dl">
            <div><dt>Nama</dt><dd><?= e($pesanan['nama']) ?></dd></div>
            <div><dt>Email</dt><dd><?= e($pesanan['email']) ?></dd></div>
            <div><dt>Telepon</dt><dd><?= e($pesanan['telepon']) ?></dd></div>
            <div><dt>Alamat</dt><dd><?= nl2br(e($pesanan['alamat'])) ?></dd></div>
            <div><dt>Kota</dt><dd><?= e($pesanan['kota']) ?></dd></div>
            <div><dt>Provinsi</dt><dd><?= e($pesanan['provinsi']) ?></dd></div>
            <div><dt>Kode pos</dt><dd><?= e($pesanan['kode_pos']) ?></dd></div>
            <div><dt>Metode bayar</dt><dd><?= e($pesanan['metode_bayar']) ?></dd></div>
            <div><dt>Catatan</dt><dd><?= $pesanan['catatan'] !== '' ? nl2br(e($pesanan['catatan'])) : 'Tidak ada catatan' ?></dd></div>
            <div><dt>Tanggal</dt><dd><?= date('d-m-Y H:i', strtotime($pesanan['dibuat_pada'])) ?> WIB</dd></div>
          </dl>
        </div>

        <div class="admin-panel">
          <h2>Pembayaran</h2>
          <dl class="admin-dl">
            <div><dt>Metode</dt><dd><?= e($pesanan['metode_bayar']) ?></dd></div>
            <div><dt>Status</dt><dd><?= e($pesanan['status']) ?></dd></div>
            <?php if ($pesanan['batas_bayar']): ?>
            <div><dt>Batas bayar</dt><dd><?= date('d-m-Y H:i', strtotime($pesanan['batas_bayar'])) ?> WIB</dd></div>
            <?php endif; ?>
            <?php if ($pesanan['dibayar_pada']): ?>
            <div><dt>Diunggah pada</dt><dd><?= date('d-m-Y H:i', strtotime($pesanan['dibayar_pada'])) ?> WIB</dd></div>
            <?php endif; ?>
            <div>
              <dt>Bukti bayar</dt>
              <dd>
                <?php if ($pesanan['bukti_bayar']): ?>
                <a class="tautan-bukti" href="<?= e(BASE_URL) ?>/uploads/bukti/<?= e($pesanan['bukti_bayar']) ?>" target="_blank" rel="noopener">
                  <img src="<?= e(BASE_URL) ?>/uploads/bukti/<?= e($pesanan['bukti_bayar']) ?>" alt="Bukti pembayaran <?= e($pesanan['kode']) ?>" width="180" height="240">
                </a>
                <?php else: ?>
                Belum ada bukti pembayaran
                <?php endif; ?>
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <aside class="ringkasan">
        <h2>Ubah Status</h2>
        <p>Status saat ini: <strong><?= e($pesanan['status']) ?></strong></p>
        <form action="<?= e(BASE_URL) ?>/admin/pesanan.php?kode=<?= e($pesanan['kode']) ?>" method="post">
          <?= csrf_field() ?>
          <div class="kolom-form">
            <label for="status">Status pesanan</label>
            <select id="status" name="status">
              <?php foreach ($statusList as $status): ?>
              <option value="<?= e($status) ?>" <?= $pesanan['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="tombol tombol-lebar">Perbarui Status</button>
        </form>
        <p class="ringkasan-catatan">Status akan langsung terlihat oleh pembeli pada halaman lacak pesanan.</p>
      </aside>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
