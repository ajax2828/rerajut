<?php
require_once __DIR__ . '/includes/fungsi.php';

pesanan_batalkan_kedaluwarsa($pdo);

$kategoriSlug = isset($_GET['kategori']) ? slug_aman($_GET['kategori']) : '';
$cari = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$urut = $_GET['urut'] ?? 'terbaru';
if (!in_array($urut, ['terbaru', 'termurah', 'termahal', 'nama'], true)) {
    $urut = 'terbaru';
}
$perHal = 12;
$total = produk_hitung($pdo, $kategoriSlug, $cari);
$totalHal = max(1, (int) ceil($total / $perHal));
$hal = min($totalHal, max(1, (int) ($_GET['hal'] ?? 1)));
$offset = ($hal - 1) * $perHal;
$produkList = produk_all($pdo, $kategoriSlug, $cari, $urut, $perHal, $offset);
$kategoriList = kategori_all($pdo);
$kategoriAktif = null;
foreach ($kategoriList as $k) {
    if ($k['slug'] === $kategoriSlug) {
        $kategoriAktif = $k;
    }
}

$judul = $kategoriAktif ? 'Produk ' . $kategoriAktif['nama'] . ' Thrift | ' . SITE_NAMA : 'Katalog Baju Thrift | ' . SITE_NAMA;
if ($cari !== '') {
    $judul = 'Hasil Pencarian ' . $cari . ' | ' . SITE_NAMA;
}
$deskripsi = 'Lihat koleksi baju thrift pilihan ' . SITE_NAMA . '. Stok satuan, kondisi terkurasi, dan pengiriman ke seluruh Indonesia.';
$kanonikal = SITE_URL . '/produk.php' . ($kategoriAktif ? '?kategori=' . urlencode($kategoriAktif['slug']) : '');
$halaman = 'produk';
require_once __DIR__ . '/includes/header.php';
?>
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="<?= e(BASE_URL) ?>/index.php">Beranda</a>
      <span>/</span>
      <a href="<?= e(BASE_URL) ?>/produk.php">Produk</a>
      <?php if ($kategoriAktif): ?>
      <span>/</span>
      <span><?= e($kategoriAktif['nama']) ?></span>
      <?php endif; ?>
    </nav>
    <h1><?= $kategoriAktif ? 'Produk ' . e($kategoriAktif['nama']) : 'Katalog Baju Thrift' ?></h1>
    <p>Pilih produk yang sesuai ukuranmu. Setiap barang hanya tersedia satu unit.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah katalog-dalam">
    <aside class="filter">
      <h2>Filter</h2>
      <form action="<?= e(BASE_URL) ?>/produk.php" method="get" class="form-filter">
        <label for="q">Cari produk</label>
        <input type="search" id="q" name="q" value="<?= e($cari) ?>" placeholder="Contoh: cardigan">
        <?php if ($kategoriSlug !== ''): ?>
        <input type="hidden" name="kategori" value="<?= e($kategoriSlug) ?>">
        <?php endif; ?>
        <label for="urut">Urutkan</label>
        <select id="urut" name="urut">
          <option value="terbaru" <?= $urut === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
          <option value="termurah" <?= $urut === 'termurah' ? 'selected' : '' ?>>Harga terendah</option>
          <option value="termahal" <?= $urut === 'termahal' ? 'selected' : '' ?>>Harga tertinggi</option>
          <option value="nama" <?= $urut === 'nama' ? 'selected' : '' ?>>Nama produk</option>
        </select>
        <button type="submit" class="tombol tombol-kecil">Terapkan</button>
      </form>
      <h3>Kategori</h3>
      <ul class="filter-kategori">
        <li><a class="<?= $kategoriSlug === '' ? 'aktif' : '' ?>" href="<?= e(BASE_URL) ?>/produk.php">Semua produk</a></li>
        <?php foreach ($kategoriList as $k): ?>
        <li><a class="<?= $kategoriSlug === $k['slug'] ? 'aktif' : '' ?>" href="<?= e(BASE_URL) ?>/produk.php?kategori=<?= e($k['slug']) ?>"><?= e($k['nama']) ?> (<?= (int) $k['jumlah'] ?>)</a></li>
        <?php endforeach; ?>
      </ul>
    </aside>
    <div class="katalog-utama">
      <div class="katalog-info">
        <p>Menampilkan <?= count($produkList) ?> dari <?= (int) $total ?> produk<?= $cari !== '' ? ' untuk pencarian ' . e($cari) : '' ?>.</p>
        <?php if ($cari !== '' || $kategoriSlug !== ''): ?>
        <a href="<?= e(BASE_URL) ?>/produk.php">Reset filter</a>
        <?php endif; ?>
      </div>
      <?php if (!$produkList): ?>
      <div class="kosong">
        <h2>Produk tidak ditemukan</h2>
        <p>Coba kata kunci lain atau lihat seluruh katalog kami.</p>
        <a class="tombol" href="<?= e(BASE_URL) ?>/produk.php">Lihat Semua Produk</a>
      </div>
      <?php else: ?>
      <div class="grid-produk">
        <?php foreach ($produkList as $produk): ?>
        <?php require __DIR__ . '/includes/kartu-produk.php'; ?>
        <?php endforeach; ?>
      </div>
      <?php if ($totalHal > 1): ?>
      <nav class="paginasi" aria-label="Navigasi halaman">
        <?php for ($i = 1; $i <= $totalHal; $i++): ?>
        <?php
        $query = ['hal' => $i];
        if ($kategoriSlug !== '') {
            $query['kategori'] = $kategoriSlug;
        }
        if ($cari !== '') {
            $query['q'] = $cari;
        }
        if ($urut !== 'terbaru') {
            $query['urut'] = $urut;
        }
        ?>
        <a class="<?= $i === $hal ? 'aktif' : '' ?>" href="<?= e(BASE_URL) ?>/produk.php?<?= e(http_build_query($query)) ?>"><?= $i ?></a>
        <?php endfor; ?>
      </nav>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="bagian bagian-putih">
  <div class="wadah ringkas-dalam">
    <div>
      <h2>Tips memilih baju thrift online</h2>
      <p>Perhatikan ukuran yang tertera pada setiap produk karena stok thrift tidak tersedia dalam beberapa ukuran. Baca deskripsi kondisi dengan teliti dan bandingkan dengan ukuran pakaian yang biasa kamu pakai.</p>
      <p>Jika ragu, kirim pesan melalui halaman kontak. Tim kami akan membantu mengukur bagian tertentu seperti lebar dada, panjang badan, dan panjang lengan.</p>
    </div>
    <div>
      <h2>Belanja thrift yang bertanggung jawab</h2>
      <p>Membeli pakaian bekas layak pakai membantu mengurangi produksi tekstil baru. Cucilah produk sebelum dipakai, dan rawat dengan baik agar bisa dipakai dalam waktu lama.</p>
      <p>Rerajut Thrift mencuci setiap produk sebelum dikirim dan memeriksa bagian kancing, resleting, serta jahitan agar kamu menerima barang yang siap pakai.</p>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
