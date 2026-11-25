<?php
require_once __DIR__ . '/auth.php';

pesanan_batalkan_kedaluwarsa($pdo);

$kode = trim((string) ($_GET['kode'] ?? ''));
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
        $aksi = (string) ($_POST['aksi'] ?? 'status');
        if ($aksi === 'tolak_bukti') {
            if ($pesanan['metode_bayar'] === 'COD' || empty($pesanan['bukti_bayar'])) {
                flash_set('gagal', 'Pesanan ini tidak memiliki bukti pembayaran untuk ditolak.');
            } else {
                try {
                    $pdo->beginTransaction();
                    $stmtLock = $pdo->prepare('SELECT id, status, bukti_bayar, batas_bayar FROM pesanan WHERE id = ? FOR UPDATE');
                    $stmtLock->execute([$pesanan['id']]);
                    $terkunci = $stmtLock->fetch();
                    if (!$terkunci || empty($terkunci['bukti_bayar'])) {
                        $pdo->rollBack();
                        flash_set('gagal', 'Bukti pembayaran tidak ditemukan.');
                    } elseif ($terkunci['status'] === 'Dibatalkan' || $terkunci['status'] === 'Selesai') {
                        $pdo->rollBack();
                        flash_set('gagal', 'Bukti tidak dapat ditolak pada status ' . $terkunci['status'] . '.');
                    } else {
                        $batasBaru = (!empty($terkunci['batas_bayar']) && strtotime($terkunci['batas_bayar']) > time())
                            ? $terkunci['batas_bayar']
                            : date('Y-m-d H:i:s', time() + 86400);
                        $stmtUpd = $pdo->prepare("UPDATE pesanan SET bukti_bayar = NULL, dibayar_pada = NULL, status = 'Menunggu Pembayaran', batas_bayar = ? WHERE id = ?");
                        $stmtUpd->execute([$batasBaru, $pesanan['id']]);
                        $pdo->commit();

                        $berkasBukti = __DIR__ . '/../uploads/bukti/' . basename($terkunci['bukti_bayar']);
                        if (is_file($berkasBukti)) {
                            @unlink($berkasBukti);
                        }
                        flash_set('sukses', 'Bukti pembayaran ditolak dan dihapus. Pembeli dapat mengunggah bukti baru hingga ' . tanggal_wib($batasBaru) . '.');
                    }
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    error_log('[Rerajut Tolak Bukti Error] ' . $e->getMessage());
                    flash_set('gagal', 'Gagal menolak bukti pembayaran.');
                }
            }
        } else {
            $status = (string) ($_POST['status'] ?? '');
            $hasil = pesanan_ubah_status($pdo, $pesanan['id'], $status);
            flash_set($hasil['ok'] ? 'sukses' : 'gagal', $hasil['pesan']);
        }
    }
    redirect(BASE_URL . '/admin/pesanan.php?kode=' . urlencode($pesanan['kode']));
}

$stmt = $pdo->prepare('SELECT * FROM pesanan_item WHERE pesanan_id = ? ORDER BY id ASC');
$stmt->execute([$pesanan['id']]);
$itemPesanan = $stmt->fetchAll();

$judul = 'Detail Pesanan ' . $pesanan['kode'] . ' | Admin ' . SITE_NAMA;
$deskripsi = 'Detail pesanan pelanggan ' . SITE_NAMA . '.';
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
            <div><dt>Catatan</dt><dd><?= trim((string) ($pesanan['catatan'] ?? '')) !== '' ? nl2br(e($pesanan['catatan'])) : 'Tidak ada catatan' ?></dd></div>
            <div><dt>Tanggal</dt><dd><?= e(tanggal_wib($pesanan['dibuat_pada'])) ?></dd></div>
          </dl>
        </div>

        <div class="admin-panel">
          <h2>Pembayaran</h2>
          <dl class="admin-dl">
            <div><dt>Metode</dt><dd><?= e($pesanan['metode_bayar']) ?></dd></div>
            <div><dt>Status</dt><dd><?= e($pesanan['status']) ?></dd></div>
            <?php if (!empty($pesanan['batas_bayar'])): ?>
            <div><dt>Batas bayar</dt><dd><?= e(tanggal_wib($pesanan['batas_bayar'])) ?></dd></div>
            <?php endif; ?>
            <?php if (!empty($pesanan['dibayar_pada'])): ?>
            <div><dt>Diunggah pada</dt><dd><?= e(tanggal_wib($pesanan['dibayar_pada'])) ?></dd></div>
            <?php endif; ?>
            <div>
              <dt>Bukti bayar</dt>
              <dd>
                <?php if (!empty($pesanan['bukti_bayar'])): ?>
                <a class="tautan-bukti" href="<?= e(BASE_URL) ?>/uploads/bukti/<?= e($pesanan['bukti_bayar']) ?>" target="_blank" rel="noopener">
                  <img src="<?= e(BASE_URL) ?>/uploads/bukti/<?= e($pesanan['bukti_bayar']) ?>" alt="Bukti pembayaran <?= e($pesanan['kode']) ?>" width="180" height="240">
                </a>
                <?php if ($pesanan['status'] !== 'Selesai' && $pesanan['status'] !== 'Dibatalkan'): ?>
                <form action="<?= e(BASE_URL) ?>/admin/pesanan.php?kode=<?= e(urlencode($pesanan['kode'])) ?>" method="post" class="tombol-jarak" data-konfirmasi="Tolak dan hapus bukti pembayaran ini agar pembeli bisa mengunggah ulang?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="aksi" value="tolak_bukti">
                  <button type="submit" class="tombol tombol-kecil tombol-hapus">Tolak Bukti Pembayaran</button>
                </form>
                <?php endif; ?>
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
        <form action="<?= e(BASE_URL) ?>/admin/pesanan.php?kode=<?= e(urlencode($pesanan['kode'])) ?>" method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="aksi" value="status">
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
        <p class="ringkasan-catatan">Membatalkan pesanan akan mengembalikan stok produk secara otomatis.</p>
      </aside>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
