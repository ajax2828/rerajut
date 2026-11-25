<?php
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash_set('gagal', 'Sesi tidak valid.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        if (($_POST['aksi'] ?? '') === 'hapus' && $id > 0) {
            $stmt = $pdo->prepare('DELETE FROM pesan_kontak WHERE id = ?');
            $stmt->execute([$id]);
            flash_set('sukses', 'Pesan pelanggan dihapus.');
        }
    }
    redirect(BASE_URL . '/admin/pesan.php');
}

$pesanList = $pdo->query('SELECT * FROM pesan_kontak ORDER BY id DESC')->fetchAll();
$langgananList = $pdo->query('SELECT * FROM langganan ORDER BY id DESC')->fetchAll();

$judul = 'Pesan Pelanggan | Admin Rerajut Thrift';
$deskripsi = 'Pesan pelanggan dan daftar langganan Rerajut Thrift.';
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
      <h2>Daftar Pesan</h2>
      <?php if (!$pesanList): ?>
      <p>Belum ada pesan masuk.</p>
      <?php else: ?>
      <div class="daftar-pesan-admin">
        <?php foreach ($pesanList as $pesan): ?>
        <article class="pesan-admin">
          <div class="pesan-kepala">
            <div>
              <strong><?= e($pesan['subjek']) ?></strong>
              <p><?= e($pesan['nama']) ?> / <?= e($pesan['email']) ?> / <?= date('d-m-Y H:i', strtotime($pesan['dibuat_pada'])) ?></p>
            </div>
            <form action="<?= e(BASE_URL) ?>/admin/pesan.php" method="post">
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
      <h2>Daftar Langganan Email</h2>
      <?php if (!$langgananList): ?>
      <p>Belum ada pelanggan yang mendaftar.</p>
      <?php else: ?>
      <div class="tabel-gulir">
        <table class="tabel-admin">
          <thead><tr><th>Email</th><th>Tanggal daftar</th></tr></thead>
          <tbody>
            <?php foreach ($langgananList as $langganan): ?>
            <tr>
              <td><?= e($langganan['email']) ?></td>
              <td><?= date('d-m-Y H:i', strtotime($langganan['dibuat_pada'])) ?></td>
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
