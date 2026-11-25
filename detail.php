<?php
require_once __DIR__ . '/includes/fungsi.php';

$slug = isset($_GET['slug']) ? slug_aman($_GET['slug']) : '';
$produk = $slug !== '' ? produk_by_slug($pdo, $slug) : null;
if (!$produk) {
    http_response_code(404);
    $judul = 'Produk tidak ditemukan | Rerajut Thrift';
    $deskripsi = 'Produk yang kamu cari tidak tersedia atau sudah terjual.';
    $kanonikal = SITE_URL . '/produk.php';
    $halaman = 'produk';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="bagian"><div class="wadah kosong"><h1>Produk tidak ditemukan</h1><p>Produk mungkin sudah terjual atau tautannya berubah.</p><a class="tombol" href="' . e(BASE_URL) . '/produk.php">Kembali ke Katalog</a></div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$terkait = produk_terkait($pdo, $produk['kategori_id'], $produk['id']);
$habis = (int) $produk['stok'] < 1;
$diskon = $produk['harga_asli'] > $produk['harga'] ? (int) round(100 - ($produk['harga'] / $produk['harga_asli'] * 100)) : 0;
$gambarUrl = SITE_URL . '/images/' . $produk['gambar'];

$judul = $produk['nama'] . ' Ukuran ' . $produk['ukuran'] . ' | Rerajut Thrift';
$deskripsi = mb_substr(strip_tags($produk['deskripsi']), 0, 150);
$kanonikal = SITE_URL . '/detail.php?slug=' . $produk['slug'];
$og_gambar = $gambarUrl;
$halaman = 'produk';

$skemaProduk = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $produk['nama'],
    'image' => [$gambarUrl],
    'description' => $produk['deskripsi'],
    'sku' => 'RJT-' . str_pad((string) $produk['id'], 4, '0', STR_PAD_LEFT),
    'brand' => ['@type' => 'Brand', 'name' => SITE_NAMA],
    'itemCondition' => 'https://schema.org/UsedCondition',
    'offers' => [
        '@type' => 'Offer',
        'url' => $kanonikal,
        'priceCurrency' => 'IDR',
        'price' => $produk['harga'],
        'availability' => $habis ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
        'itemCondition' => 'https://schema.org/UsedCondition',
        'seller' => ['@type' => 'Organization', 'name' => SITE_NAMA],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

$skemaRemah = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => SITE_URL . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Produk', 'item' => SITE_URL . '/produk.php'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $produk['kategori'], 'item' => SITE_URL . '/produk.php?kategori=' . $produk['kategori_slug']],
        ['@type' => 'ListItem', 'position' => 4, 'name' => $produk['nama'], 'item' => $kanonikal],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

require_once __DIR__ . '/includes/header.php';
?>
<script type="application/ld+json"><?= $skemaProduk ?></script>
<script type="application/ld+json"><?= $skemaRemah ?></script>
<section class="kepala-halaman kepala-sempit">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="<?= e(BASE_URL) ?>/index.php">Beranda</a>
      <span>/</span>
      <a href="<?= e(BASE_URL) ?>/produk.php">Produk</a>
      <span>/</span>
      <a href="<?= e(BASE_URL) ?>/produk.php?kategori=<?= e($produk['kategori_slug']) ?>"><?= e($produk['kategori']) ?></a>
      <span>/</span>
      <span><?= e($produk['nama']) ?></span>
    </nav>
  </div>
</section>

<section class="bagian bagian-detail">
  <div class="wadah detail-grid">
    <div class="detail-gambar">
      <img src="<?= e(BASE_URL) ?>/images/<?= e($produk['gambar']) ?>" alt="<?= e($produk['nama']) ?> ukuran <?= e($produk['ukuran']) ?> kondisi <?= e($produk['kondisi']) ?>" width="1200" height="1600">
      <div class="detail-catatan">
        <p>Produk yang difoto adalah produk yang akan kamu terima.</p>
      </div>
    </div>
    <div class="detail-info">
      <p class="label-kecil"><?= e($produk['kategori']) ?> / <?= e($produk['kondisi']) ?></p>
      <h1><?= e($produk['nama']) ?></h1>
      <div class="detail-harga">
        <strong><?= rupiah($produk['harga']) ?></strong>
        <?php if ($diskon > 0): ?>
        <span class="harga-coret"><?= rupiah($produk['harga_asli']) ?></span>
        <span class="label-diskon">Hemat <?= $diskon ?> persen</span>
        <?php endif; ?>
      </div>
      <p class="detail-ringkas"><?= e($produk['deskripsi']) ?></p>

      <dl class="detail-spesifikasi">
        <div><dt>Ukuran</dt><dd><?= e($produk['ukuran']) ?></dd></div>
        <div><dt>Kondisi</dt><dd><?= e($produk['kondisi']) ?></dd></div>
        <div><dt>Kategori</dt><dd><?= e($produk['kategori']) ?></dd></div>
        <div><dt>Stok</dt><dd><?= $habis ? 'Habis' : (int) $produk['stok'] . ' unit' ?></dd></div>
      </dl>

      <?php if ($habis): ?>
      <p class="stok-habis">Produk ini sudah terjual. Lihat produk lain dengan kategori serupa di bawah.</p>
      <?php else: ?>
      <form action="<?= e(BASE_URL) ?>/keranjang.php" method="post" class="form-beli">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="tambah">
        <input type="hidden" name="id" value="<?= (int) $produk['id'] ?>">
        <div class="form-beli-baris">
          <label for="jumlah">Jumlah</label>
          <input type="number" id="jumlah" name="jumlah" value="1" min="1" max="<?= (int) $produk['stok'] ?>">
        </div>
        <div class="form-beli-aksi">
          <button type="submit" class="tombol">Tambah ke Keranjang</button>
          <button type="submit" name="lanjut" value="checkout" class="tombol tombol-garis">Beli Sekarang</button>
        </div>
      </form>
      <?php endif; ?>

      <div class="detail-info-box">
        <h2>Info pengiriman</h2>
        <ul>
          <li>Dikirim dari Yogyakarta dalam 1 sampai 2 hari kerja.</li>
          <li>Ongkir tetap Rp <?= number_format(ONGKIR, 0, ',', '.') ?> dan gratis di atas Rp <?= number_format(GRATIS_ONGKIR_MIN, 0, ',', '.') ?>.</li>
          <li>Tersedia pembayaran transfer bank, e-wallet, dan COD.</li>
          <li>Barang sudah dicuci dan diperiksa sebelum dikirim.</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="bagian bagian-putih">
  <div class="wadah detail-extra">
    <div>
      <h2>Panduan ukuran</h2>
      <p>Ukuran pada label bisa berbeda antar merek. Kami mencantumkan ukuran sesuai label dan kondisi barang. Jika kamu biasa memakai ukuran M pada merek tertentu, tetap periksa deskripsi karena potongan thrift bisa lebih kecil atau lebih longgar.</p>
      <table class="tabel-ukuran">
        <caption>Perkiraan ukuran badan</caption>
        <thead>
          <tr><th>Label</th><th>Lebar dada</th><th>Panjang badan</th></tr>
        </thead>
        <tbody>
          <tr><td>S</td><td>44 cm</td><td>58 cm</td></tr>
          <tr><td>M</td><td>48 cm</td><td>62 cm</td></tr>
          <tr><td>L</td><td>52 cm</td><td>66 cm</td></tr>
        </tbody>
      </table>
    </div>
    <div>
      <h2>Perawatan pakaian thrift</h2>
      <p>Cuci produk dengan air dingin dan detergen lembut sebelum dipakai pertama kali. Hindari pemutih agar warna tetap terjaga. Untuk produk rajutan, cucilah dengan tangan dan keringkan di tempat teduh.</p>
      <p>Simpan pakaian dengan cara digantung atau dilipat rapi. Dengan perawatan sederhana, pakaian thrift bisa dipakai bertahun-tahun dan membantu mengurangi limbah tekstil.</p>
    </div>
  </div>
</section>

<?php if ($terkait): ?>
<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Kamu mungkin suka</p>
        <h2>Produk serupa</h2>
      </div>
      <a class="tautan-lain" href="<?= e(BASE_URL) ?>/produk.php?kategori=<?= e($produk['kategori_slug']) ?>">Lihat kategori <?= e($produk['kategori']) ?></a>
    </div>
    <div class="grid-produk grid-produk-empat">
      <?php foreach ($terkait as $produk): ?>
      <?php require __DIR__ . '/includes/kartu-produk.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
