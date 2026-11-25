<?php
$gambarProduk = BASE_URL . '/images/' . $produk['gambar'];
?>
<article class="kartu-produk">
  <a class="kartu-gambar" href="<?= e(BASE_URL) ?>/detail.php?slug=<?= e($produk['slug']) ?>">
    <img src="<?= e($gambarProduk) ?>" alt="<?= e($produk['nama']) ?> ukuran <?= e($produk['ukuran']) ?> kondisi <?= e($produk['kondisi']) ?>" loading="lazy" width="600" height="800">
    <?php if ((int) $produk['stok'] < 1): ?>
    <span class="label-habis">Terjual</span>
    <?php endif; ?>
  </a>
  <div class="kartu-isi">
    <p class="kartu-kategori"><?= e($produk['kategori']) ?> / <?= e($produk['kondisi']) ?></p>
    <h3><a href="<?= e(BASE_URL) ?>/detail.php?slug=<?= e($produk['slug']) ?>"><?= e($produk['nama']) ?></a></h3>
    <p class="kartu-harga"><?= rupiah($produk['harga']) ?> <span><?= rupiah($produk['harga_asli']) ?></span></p>
    <p class="kartu-ukuran">Ukuran <?= e($produk['ukuran']) ?></p>
    <a class="tombol tombol-kecil" href="<?= e(BASE_URL) ?>/detail.php?slug=<?= e($produk['slug']) ?>">Lihat Produk</a>
  </div>
</article>
