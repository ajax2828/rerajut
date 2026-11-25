<?php
require_once __DIR__ . '/auth.php';

$galat = [];
$stmtUser = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmtUser->execute([(int) $_SESSION['admin_id']]);
$user = $stmtUser->fetch();

if (!$user) {
    redirect(BASE_URL . '/admin/login.php');
}

$nama = $user['nama'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $galat[] = 'Sesi tidak valid. Silakan coba lagi.';
    } else {
        $aksi = (string) ($_POST['aksi'] ?? '');
        if ($aksi === 'profil') {
            $nama = trim((string) ($_POST['nama'] ?? ''));
            if (mb_strlen($nama) < 3 || mb_strlen($nama) > 120) {
                $galat[] = 'Nama tampilan harus antara 3 sampai 120 karakter.';
            }
            if (!$galat) {
                $stmt = $pdo->prepare('UPDATE users SET nama = ? WHERE id = ?');
                $stmt->execute([$nama, $user['id']]);
                $_SESSION['admin_nama'] = $nama;
                flash_set('sukses', 'Profil admin berhasil diperbarui.');
                redirect(BASE_URL . '/admin/akun.php');
            }
        } elseif ($aksi === 'sandi') {
            $sandiLama = (string) ($_POST['sandi_lama'] ?? '');
            $sandiBaru = (string) ($_POST['sandi_baru'] ?? '');
            $konfirmasi = (string) ($_POST['konfirmasi_sandi'] ?? '');

            if (!password_verify($sandiLama, $user['password'])) {
                $galat[] = 'Kata sandi saat ini tidak cocok.';
            }
            if (strlen($sandiBaru) < 8) {
                $galat[] = 'Kata sandi baru minimal 8 karakter.';
            }
            if ($sandiBaru !== $konfirmasi) {
                $galat[] = 'Konfirmasi kata sandi baru tidak cocok.';
            }
            if (!$galat) {
                $hashBaru = password_hash($sandiBaru, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([$hashBaru, $user['id']]);
                session_regenerate_id(true);
                unset($_SESSION['csrf']);
                flash_set('sukses', 'Kata sandi admin berhasil diperbarui.');
                redirect(BASE_URL . '/admin/akun.php');
            }
        }
    }
}

$judul = 'Akun Admin | ' . SITE_NAMA;
$deskripsi = 'Pengaturan akun pengelola ' . SITE_NAMA . '.';
$kanonikal = SITE_URL . '/admin/akun.php';
$robots = 'noindex, nofollow';
$halaman = 'admin-akun';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bagian">
  <div class="wadah wadah-sempit">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Keamanan pengelola</p>
        <h1>Pengaturan Akun Admin</h1>
      </div>
      <a class="tautan-lain" href="<?= e(BASE_URL) ?>/admin/index.php">Kembali ke dashboard</a>
    </div>

    <?php if ($galat): ?>
    <div class="kotak-galat">
      <h2>Periksa kembali</h2>
      <ul>
        <?php foreach ($galat as $g): ?>
        <li><?= e($g) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <div class="admin-panel">
      <h2>Profil Admin</h2>
      <form action="<?= e(BASE_URL) ?>/admin/akun.php" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="profil">
        <div class="kolom-form">
          <label for="username">Username</label>
          <input type="text" id="username" value="<?= e($user['username']) ?>" disabled>
        </div>
        <div class="kolom-form">
          <label for="nama">Nama tampilan</label>
          <input type="text" id="nama" name="nama" value="<?= e($nama) ?>" maxlength="120" required>
        </div>
        <button type="submit" class="tombol">Simpan Profil</button>
      </form>
    </div>

    <div class="admin-panel">
      <h2>Ganti Kata Sandi</h2>
      <p class="bantuan-form">Disarankan segera mengganti kata sandi bawaan setelah aplikasi dijalankan di server.</p>
      <form action="<?= e(BASE_URL) ?>/admin/akun.php" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="sandi">
        <div class="kolom-form">
          <label for="sandi_lama">Kata sandi saat ini</label>
          <input type="password" id="sandi_lama" name="sandi_lama" autocomplete="current-password" required>
        </div>
        <div class="kolom-form">
          <label for="sandi_baru">Kata sandi baru (minimal 8 karakter)</label>
          <input type="password" id="sandi_baru" name="sandi_baru" minlength="8" autocomplete="new-password" required>
        </div>
        <div class="kolom-form">
          <label for="konfirmasi_sandi">Ulangi kata sandi baru</label>
          <input type="password" id="konfirmasi_sandi" name="konfirmasi_sandi" minlength="8" autocomplete="new-password" required>
        </div>
        <button type="submit" class="tombol">Perbarui Kata Sandi</button>
      </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
