<?php
require_once __DIR__ . '/auth.php';

$statusList = admin_status_pesanan();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('gagal', 'Sesi tidak valid.');
        redirect(BASE_URL . '/admin/index.php');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($id > 0 && in_array($status, $statusList, true)) {
        $stmt = $pdo->prepare('UPDATE pesanan SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        flash_set('sukses', 'Status pesanan berhasil diperbarui.');
    } else {
        flash_set('gagal', 'Status pesanan tidak valid.');
    }
    redirect(BASE_URL . '/admin/index.php');
}

$totalPesanan = (int) $pdo->query('SELECT COUNT(*) FROM pesanan')->fetchColumn();
$menunggu = (int) $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Menunggu Pembayaran'")->fetchColumn();
$diproses = (int) $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status IN ('Pembayaran Diproses', 'Diproses', 'Dikirim')")->fetchColumn();
$omzet = (int) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pesanan WHERE status <> 'Dibatalkan'")->fetchColumn();
$pesanBaru = (int) $pdo->query('SELECT COUNT(*) FROM pesan_kontak')->fetchColumn();
$pesananList = $pdo->query('SELECT * FROM pesanan ORDER BY id DESC LIMIT 50')->fetchAll();

$judul = 'Dashboard Admin | Rerajut Thrift';
$deskripsi = 'Dashboard pengelolaan pesanan Rerajut Thrift.';
$kanonikal = SITE_URL . '/admin/index.php';
$robots = 'noindex, nofollow';
$halaman = 'admin-pesanan';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Panel pengelola</p>
        <h1>Dashboard Pesanan</h1>
      </div>
      <a class="tombol tombol-kecil" href="<?= e(BASE_URL) ?>/admin/produk.php">Kelola Produk</a>
    </div>

    <div class="admin-statistik">
      <div><strong><?= $totalPesanan ?></strong><span>Total pesanan</span></div>
      <div><strong><?= $menunggu ?></strong><span>Menunggu pembayaran</span></div>
      <div><strong><?= $diproses ?></strong><span>Sedang diproses</span></div>
      <div><strong><?= rupiah($omzet) ?></strong><span>Total omzet</span></div>
      <div><strong><?= $pesanBaru ?></strong><span>Pesan pelanggan</span></div>
    </div>

    <div class="admin-panel">
      <h2>Daftar Pesanan</h2>
      <?php if (!$pesananList): ?>
      <p>Belum ada pesanan masuk. Pesanan akan muncul setelah pembeli menyelesaikan checkout.</p>
      <?php else: ?>
      <div class="tabel-gulir">
        <table class="tabel-admin">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Pembeli</th>
              <th>Tanggal</th>
              <th>Metode</th>
              <th>Total</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pesananList as $row): ?>
            <tr>
              <td><a href="<?= e(BASE_URL) ?>/admin/pesanan.php?kode=<?= e($row['kode']) ?>"><?= e($row['kode']) ?></a></td>
              <td><?= e($row['nama']) ?><br><small><?= e($row['kota']) ?></small></td>
              <td><?= date('d-m-Y H:i', strtotime($row['dibuat_pada'])) ?></td>
              <td><?= e($row['metode_bayar']) ?></td>
              <td><?= rupiah($row['total']) ?></td>
              <td><span class="status-lencana"><?= e($row['status']) ?></span></td>
              <td>
                <form action="<?= e(BASE_URL) ?>/admin/index.php" method="post" class="form-status">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                  <select name="status" aria-label="Ubah status <?= e($row['kode']) ?>">
                    <?php foreach ($statusList as $status): ?>
                    <option value="<?= e($status) ?>" <?= $row['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="tombol tombol-kecil">Simpan</button>
                </form>
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
