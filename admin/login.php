<?php
require_once __DIR__ . '/../includes/fungsi.php';

no_cache_headers();

$galat = [];
if (!empty($_SESSION['admin']) && !empty($_SESSION['admin_id'])) {
    redirect(BASE_URL . '/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $limit = rate_limit_cek('admin_login', 5, 300);
    if ($limit['terblokir']) {
        $menit = (int) ceil($limit['sisa_detik'] / 60);
        $galat[] = 'Terlalu banyak percobaan masuk. Coba lagi dalam ' . $menit . ' menit.';
    }
    if (!csrf_valid()) {
        $galat[] = 'Sesi tidak valid. Silakan coba lagi.';
    }
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($username === '' || $password === '') {
        $galat[] = 'Username dan kata sandi wajib diisi.';
    }

    if (!$galat) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password'])) {
            rate_limit_catat('admin_login', 300);
            $galat[] = 'Username atau kata sandi admin salah.';
        } else {
            rate_limit_reset('admin_login');
            session_regenerate_id(true);
            unset($_SESSION['csrf']);
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = (int) $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_nama'] = $user['nama'];
            $_SESSION['admin_last_active'] = time();
            redirect(BASE_URL . '/admin/index.php');
        }
    }
}

$judul = 'Masuk Admin | ' . SITE_NAMA;
$deskripsi = 'Halaman masuk pengelola toko ' . SITE_NAMA . '.';
$kanonikal = SITE_URL . '/admin/login.php';
$robots = 'noindex, nofollow';
$halaman = '';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bagian">
  <div class="wadah wadah-sempit">
    <div class="admin-masuk">
      <h1>Masuk Admin</h1>
      <p>Gunakan akun pengelola untuk memproses pesanan dan mengelola produk.</p>
      <?php if ($galat): ?>
      <div class="kotak-galat">
        <ul>
          <?php foreach ($galat as $g): ?>
          <li><?= e($g) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <form action="<?= e(BASE_URL) ?>/admin/login.php" method="post">
        <?= csrf_field() ?>
        <div class="kolom-form">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" required autofocus>
        </div>
        <div class="kolom-form">
          <label for="password">Kata sandi</label>
          <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="tombol tombol-lebar">Masuk</button>
      </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
