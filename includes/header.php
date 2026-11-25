<?php
require_once __DIR__ . '/fungsi.php';

$judul = $judul ?? SITE_NAMA;
$deskripsi = $deskripsi ?? SITE_DESKRIPSI;
$kanonikal = $kanonikal ?? SITE_URL . '/';
$og_gambar = $og_gambar ?? SITE_URL . '/images/produk-06.jpg';
$halaman = $halaman ?? '';
$robots = $robots ?? 'index, follow';
$flash = flash_ambil();

$skemaToko = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'OnlineStore',
    'name' => SITE_NAMA,
    'url' => SITE_URL,
    'logo' => SITE_URL . '/assets/img/logo.svg',
    'description' => SITE_DESKRIPSI,
    'email' => SITE_EMAIL,
    'telephone' => SITE_TELEPON,
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => SITE_ALAMAT,
        'addressCountry' => 'ID',
    ],
    'sameAs' => [
        'https://www.instagram.com/rerajut.thrift',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?></title>
<meta name="description" content="<?= e($deskripsi) ?>">
<meta name="keywords" content="thrift baju, baju thrift, thrift online, pakaian bekas berkualitas, thrift yogyakarta, rerajut thrift">
<meta name="author" content="<?= e(SITE_NAMA) ?>">
<meta name="robots" content="<?= e($robots) ?>">
<link rel="canonical" href="<?= e($kanonikal) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(SITE_NAMA) ?>">
<meta property="og:title" content="<?= e($judul) ?>">
<meta property="og:description" content="<?= e($deskripsi) ?>">
<meta property="og:url" content="<?= e($kanonikal) ?>">
<meta property="og:image" content="<?= e($og_gambar) ?>">
<meta property="og:locale" content="id_ID">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($judul) ?>">
<meta name="twitter:description" content="<?= e($deskripsi) ?>">
<meta name="twitter:image" content="<?= e($og_gambar) ?>">
<link rel="icon" href="<?= e(BASE_URL) ?>/assets/img/favicon.svg" type="image/svg+xml">
<link rel="sitemap" type="application/xml" href="<?= e(SITE_URL) ?>/sitemap.php">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
<script type="application/ld+json"><?= $skemaToko ?></script>
</head>
<body>
<a class="lewati" href="#konten">Lewati ke konten</a>
<header class="situs-header">
  <div class="wadah header-dalam">
    <a class="logo" href="<?= e(BASE_URL) ?>/index.php">
      <img src="<?= e(BASE_URL) ?>/assets/img/logo.svg" alt="Logo <?= e(SITE_NAMA) ?>" width="170" height="48">
    </a>
    <button class="tombol-menu" type="button" aria-label="Buka menu" aria-expanded="false" data-menu>
      <span></span><span></span><span></span>
    </button>
    <nav class="nav-utama" data-nav>
      <?php if (!empty($_SESSION['admin'])): ?>
      <span class="admin-pengguna">Halo, <?= e($_SESSION['admin_username'] ?? 'admin') ?></span>
      <a href="<?= e(BASE_URL) ?>/admin/index.php" class="<?= $halaman === 'admin-pesanan' ? 'aktif' : '' ?>">Pesanan</a>
      <a href="<?= e(BASE_URL) ?>/admin/produk.php" class="<?= $halaman === 'admin-produk' ? 'aktif' : '' ?>">Produk</a>
      <a href="<?= e(BASE_URL) ?>/admin/kategori.php" class="<?= $halaman === 'admin-kategori' ? 'aktif' : '' ?>">Kategori</a>
      <a href="<?= e(BASE_URL) ?>/admin/pesan.php" class="<?= $halaman === 'admin-pesan' ? 'aktif' : '' ?>">Pesan</a>
      <a href="<?= e(BASE_URL) ?>/admin/akun.php" class="<?= $halaman === 'admin-akun' ? 'aktif' : '' ?>">Akun</a>
      <a href="<?= e(BASE_URL) ?>/index.php">Lihat Toko</a>
      <form action="<?= e(BASE_URL) ?>/admin/logout.php" method="post" class="form-keluar-admin">
        <?= csrf_field() ?>
        <button type="submit" class="tautan-keluar-admin">Keluar</button>
      </form>
      <?php else: ?>
      <a href="<?= e(BASE_URL) ?>/index.php" class="<?= $halaman === 'beranda' ? 'aktif' : '' ?>">Beranda</a>
      <a href="<?= e(BASE_URL) ?>/produk.php" class="<?= $halaman === 'produk' ? 'aktif' : '' ?>">Produk</a>
      <a href="<?= e(BASE_URL) ?>/tentang.php" class="<?= $halaman === 'tentang' ? 'aktif' : '' ?>">Tentang</a>
      <a href="<?= e(BASE_URL) ?>/lacak.php" class="<?= $halaman === 'lacak' ? 'aktif' : '' ?>">Lacak Pesanan</a>
      <a class="tautan-pesanan-saya <?= $halaman === 'pesanan-saya' ? 'aktif' : '' ?>" href="<?= e(BASE_URL) ?>/pesanan-saya.php" hidden>Pesanan Saya</a>
      <a href="<?= e(BASE_URL) ?>/kontak.php" class="<?= $halaman === 'kontak' ? 'aktif' : '' ?>">Kontak</a>
      <a class="tautan-keranjang" href="<?= e(BASE_URL) ?>/keranjang.php">Keranjang <span class="lencana"><?= keranjang_jumlah() ?></span></a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php if ($flash): ?>
<div class="flash flash-<?= e($flash['tipe']) ?>" role="status">
  <div class="wadah"><?= e($flash['pesan']) ?></div>
</div>
<?php endif; ?>
<main id="konten">
