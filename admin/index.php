<?php
require_once __DIR__ . '/auth.php';

pesanan_batalkan_kedaluwarsa($pdo);

$statusList = admin_status_pesanan();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kembali = url_kembali_aman($_SERVER['HTTP_REFERER'] ?? '', BASE_URL . '/admin/index.php');
    if (!csrf_valid()) {
        flash_set('gagal', 'Sesi tidak valid.');
        redirect($kembali);
    }
    $id = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    $hasil = pesanan_ubah_status($pdo, $id, $status);
    flash_set($hasil['ok'] ? 'sukses' : 'gagal', $hasil['pesan']);
    redirect($kembali);
}

$filterStatus = trim((string) ($_GET['status'] ?? ''));
if ($filterStatus !== '' && !in_array($filterStatus, $statusList, true)) {
    $filterStatus = '';
}
$cari = trim((string) ($_GET['q'] ?? ''));

$where = [];
$params = [];
if ($filterStatus !== '') {
    $where[] = 'status = ?';
    $params[] = $filterStatus;
}
if ($cari !== '') {
    $where[] = '(kode LIKE ? OR nama LIKE ? OR email LIKE ? OR telepon LIKE ?)';
    $pola = '%' . $cari . '%';
    $params[] = $pola;
    $params[] = $pola;
    $params[] = $pola;
    $params[] = $pola;
}
$sqlWhere = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$perHal = 20;
$stmtHitung = $pdo->prepare('SELECT COUNT(*) FROM pesanan' . $sqlWhere);
$stmtHitung->execute($params);
$totalHasil = (int) $stmtHitung->fetchColumn();
$totalHal = max(1, (int) ceil($totalHasil / $perHal));
$hal = min($totalHal, max(1, (int) ($_GET['hal'] ?? 1)));
$offset = ($hal - 1) * $perHal;

$stmtList = $pdo->prepare('SELECT * FROM pesanan' . $sqlWhere . ' ORDER BY id DESC LIMIT ' . $perHal . ' OFFSET ' . $offset);
$stmtList->execute($params);
$pesananList = $stmtList->fetchAll();

$totalPesanan = (int) $pdo->query('SELECT COUNT(*) FROM pesanan')->fetchColumn();
$menunggu = (int) $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Menunggu Pembayaran'")->fetchColumn();
$diproses = (int) $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status IN ('Pembayaran Diproses', 'Diproses', 'Dikirim')")->fetchColumn();
$omzet = (int) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pesanan WHERE status IN ('Diproses', 'Dikirim', 'Selesai')")->fetchColumn();
$pesanBaru = (int) $pdo->query('SELECT COUNT(*) FROM pesan_kontak')->fetchColumn();

$judul = 'Dashboard Admin | ' . SITE_NAMA;
$deskripsi = 'Dashboard pengelolaan pesanan ' . SITE_NAMA . '.';
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
      <div><strong><?= rupiah($omzet) ?></strong><span>Omzet terkonfirmasi</span></div>
      <div><strong><?= $pesanBaru ?></strong><span>Pesan pelanggan</span></div>
    </div>

    <div class="admin-panel">
      <h2>Daftar Pesanan</h2>
      <form action="<?= e(BASE_URL) ?>/admin/index.php" method="get" class="admin-filter">
        <input type="search" name="q" value="<?= e($cari) ?>" placeholder="Cari kode, nama, email, atau telepon">
        <select name="status" aria-label="Filter status pesanan">
          <option value="">Semua status</option>
          <?php foreach ($statusList as $st): ?>
          <option value="<?= e($st) ?>" <?= $filterStatus === $st ? 'selected' : '' ?>><?= e($st) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="tombol tombol-kecil">Filter</button>
        <?php if ($cari !== '' || $filterStatus !== ''): ?>
        <a class="tautan-lain" href="<?= e(BASE_URL) ?>/admin/index.php">Reset</a>
        <?php endif; ?>
      </form>

      <?php if (!$pesananList): ?>
      <p><?= ($cari !== '' || $filterStatus !== '') ? 'Tidak ada pesanan yang cocok dengan filter.' : 'Belum ada pesanan masuk. Pesanan akan muncul setelah pembeli menyelesaikan checkout.' ?></p>
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
              <td><a href="<?= e(BASE_URL) ?>/admin/pesanan.php?kode=<?= e(urlencode($row['kode'])) ?>"><?= e($row['kode']) ?></a></td>
              <td><?= e($row['nama']) ?><br><small><?= e($row['kota']) ?></small></td>
              <td><?= e(tanggal_wib($row['dibuat_pada'], false)) ?></td>
              <td>
                <?= e($row['metode_bayar']) ?>
                <?php if (!empty($row['bukti_bayar'])): ?>
                <br><span class="bukti-lencana">Bukti ada</span>
                <?php endif; ?>
              </td>
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
      <?php if ($totalHal > 1): ?>
      <nav class="paginasi" aria-label="Navigasi halaman pesanan">
        <?php for ($i = 1; $i <= $totalHal; $i++): ?>
        <?php
        $query = ['hal' => $i];
        if ($filterStatus !== '') {
            $query['status'] = $filterStatus;
        }
        if ($cari !== '') {
            $query['q'] = $cari;
        }
        ?>
        <a class="<?= $i === $hal ? 'aktif' : '' ?>" href="<?= e(BASE_URL) ?>/admin/index.php?<?= e(http_build_query($query)) ?>"><?= $i ?></a>
        <?php endfor; ?>
      </nav>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
