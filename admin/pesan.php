<?php
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('gagal', 'Sesi tidak valid.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $aksi = (string) ($_POST['aksi'] ?? '');
        if ($aksi === 'hapus' && $id > 0) {
            $stmt = $pdo->prepare('DELETE FROM pesan_kontak WHERE id = ?');
            $stmt->execute([$id]);
            flash_set('sukses', 'Pesan pelanggan dihapus.');
        } elseif ($aksi === 'hapus_langganan' && $id > 0) {
            $stmt = $pdo->prepare('DELETE FROM langganan WHERE id = ?');
            $stmt->execute([$id]);
            flash_set('sukses', 'Email langganan dihapus.');
        }
    }
    redirect(BASE_URL . '/admin/pesan.php');
}

$pesanList = $pdo->query('SELECT * FROM pesan_kontak ORDER BY id DESC')->fetchAll();
$langgananList = $pdo->query('SELECT * FROM langganan ORDER BY id DESC')->fetchAll();

$judul = 'Pesan Pelanggan | Admin ' . SITE_NAMA;
$deskripsi = 'Pesan pelanggan dan daftar langganan ' . SITE_NAMA . '.';
$kanonikal = SITE_URL . '/admin/pesan.php';
$robots = 'noindex, nofollow';
$halaman = 'admin-pesan';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Kotak masuk</p>
        <h1>Pesan Pelanggan</h1>
      </div>
    </div>

    <div class="admin-panel">
      <h2>Daftar Pesan (<?= count($pesanList) ?>)</h2>
      <?php if (!$pesanList): ?>
      <p>Belum ada pesan masuk.</p>
      <?php else: ?>
      <div class="daftar-pesan-admin">
        <?php foreach ($pesanList as $pesan): ?>
        <article class="pesan-admin">
          <div class="pesan-kepala">
            <div>
              <strong><?= e($pesan['subjek']) ?></strong>
              <p><?= e($pesan['nama']) ?> / <a href="mailto:<?= e($pesan['email']) ?>"><?= e($pesan['email']) ?></a> / <?= e(tanggal_wib($pesan['dibuat_pada'], false)) ?></p>
            </div>
            <form action="<?= e(BASE_URL) ?>/admin/pesan.php" method="post" data-konfirmasi="Hapus pesan dari <?= e($pesan['nama']) ?>?">
              <?= csrf_field() ?>
              <input type="hidden" name="aksi" value="hapus">
              <input type="hidden" name="id" value="<?= (int) $pesan['id'] ?>">
              <button type="submit" class="tautan-hapus">Hapus</button>
            </form>
          </div>
          <p class="pesan-isi"><?= nl2br(e($pesan['pesan'])) ?></p>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="admin-panel">
      <h2>Daftar Langganan Email (<?= count($langgananList) ?>)</h2>
      <?php if (!$langgananList): ?>
      <p>Belum ada pelanggan yang mendaftar.</p>
      <?php else: ?>
      <div class="tabel-gulir">
        <table class="tabel-admin">
          <thead><tr><th>Email</th><th>Tanggal daftar</th><th>Aksi</th></tr></thead>
          <tbody>
            <?php foreach ($langgananList as $langganan): ?>
            <tr>
              <td><?= e($langganan['email']) ?></td>
              <td><?= e(tanggal_wib($langganan['dibuat_pada'], false)) ?></td>
              <td>
                <form action="<?= e(BASE_URL) ?>/admin/pesan.php" method="post" data-konfirmasi="Hapus email <?= e($langganan['email']) ?> dari daftar langganan?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="aksi" value="hapus_langganan">
                  <input type="hidden" name="id" value="<?= (int) $langganan['id'] ?>">
                  <button type="submit" class="tautan-hapus">Hapus</button>
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
