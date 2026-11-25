<?php
require_once __DIR__ . '/../includes/fungsi.php';

$galat = [];
if (!empty($_SESSION['admin'])) {
    redirect(BASE_URL . '/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $galat[] = 'Sesi tidak valid. Silakan coba lagi.';
    }
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password'])) {
        $galat[] = 'Username atau kata sandi admin salah.';
    }
    if (!$galat) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_nama'] = $user['nama'];
        redirect(BASE_URL . '/admin/index.php');
    }
}

$judul = 'Masuk Admin | Rerajut Thrift';
$deskripsi = 'Halaman masuk pengelola toko Rerajut Thrift.';
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
          <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
        </div>
        <div class="kolom-form">
          <label for="password">Kata sandi</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="tombol tombol-lebar">Masuk</button>
      </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
