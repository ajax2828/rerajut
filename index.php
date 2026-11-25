<?php
require_once __DIR__ . '/includes/fungsi.php';

$unggulan = $pdo->query('SELECT p.*, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id WHERE p.unggulan = 1 AND p.stok > 0 ORDER BY p.id ASC LIMIT 8')->fetchAll();
$terbaru = $pdo->query('SELECT p.*, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id WHERE p.stok > 0 ORDER BY p.id DESC LIMIT 4')->fetchAll();
$kategori = kategori_all($pdo);

$judul = 'Rerajut Thrift | Baju Thrift Pilihan Harga Mahasiswa';
$deskripsi = 'Toko baju thrift online dengan produk terkurasi, stok satuan, dan harga mulai Rp 45.000. Temukan rajutan, kemeja, outer, dan aksesoris thrift berkualitas di Rerajut Thrift.';
$kanonikal = SITE_URL . '/';
$halaman = 'beranda';
require_once __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="wadah hero-dalam">
    <div class="hero-teks">
      <p class="label-kecil">Kurasi terbatas setiap minggu</p>
      <h1>Baju thrift pilihan, gaya baru tanpa beli baru</h1>
      <p class="hero-deskripsi">Setiap potong sudah dicek kondisi dan kebersihannya. Stok hanya satu, jadi kamu tidak perlu takut gayanya pasaran.</p>
      <div class="hero-aksi">
        <a class="tombol" href="<?= e(BASE_URL) ?>/produk.php">Belanja Sekarang</a>
        <a class="tombol tombol-garis" href="<?= e(BASE_URL) ?>/tentang.php">Cara Kerja Kami</a>
      </div>
      <ul class="hero-poin">
        <li>Mulai Rp 45.000</li>
        <li>Gratis ongkir di atas Rp 250.000</li>
        <li>Bisa COD dan transfer bank</li>
      </ul>
    </div>
    <div class="hero-gambar">
      <img src="<?= e(BASE_URL) ?>/images/produk-13.jpg" alt="Setelan vest dan kemeja thrift pilihan Rerajut Thrift" width="1200" height="1600">
      <div class="hero-label">
        <strong>14 produk</strong>
        <span>siap dikirim</span>
      </div>
    </div>
  </div>
</section>

<section class="keunggulan">
  <div class="wadah keunggulan-grid">
    <div>
      <h2>Sudah dicuci</h2>
      <p>Produk dibersihkan sebelum dikirim ke pembeli.</p>
    </div>
    <div>
      <h2>Kondisi terkurasi</h2>
      <p>Kami memilih barang tanpa cacat berarti.</p>
    </div>
    <div>
      <h2>Stok satuan</h2>
      <p>Satu produk satu pembeli, gaya lebih eksklusif.</p>
    </div>
    <div>
      <h2>Kemasan rapi</h2>
      <p>Dikemas aman dan siap dijadikan hadiah.</p>
    </div>
  </div>
</section>

<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Pilih kebutuhanmu</p>
        <h2>Kategori produk</h2>
      </div>
      <a class="tautan-lain" href="<?= e(BASE_URL) ?>/produk.php">Lihat semua produk</a>
    </div>
    <div class="kategori-grid">
      <?php foreach ($kategori as $k): ?>
      <a class="kategori-kartu" href="<?= e(BASE_URL) ?>/produk.php?kategori=<?= e($k['slug']) ?>">
        <strong><?= e($k['nama']) ?></strong>
        <span><?= (int) $k['jumlah'] ?> produk</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="bagian bagian-putih">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Paling dicari</p>
        <h2>Produk unggulan</h2>
      </div>
      <a class="tautan-lain" href="<?= e(BASE_URL) ?>/produk.php">Lihat semua produk</a>
    </div>
    <div class="grid-produk">
      <?php foreach ($unggulan as $produk): ?>
      <?php require __DIR__ . '/includes/kartu-produk.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Baru masuk</p>
        <h2>Drop terbaru</h2>
      </div>
    </div>
    <div class="grid-produk grid-produk-empat">
      <?php foreach ($terbaru as $produk): ?>
      <?php require __DIR__ . '/includes/kartu-produk.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="promo">
  <div class="wadah promo-dalam">
    <div>
      <p class="label-kecil">Promo minggu ini</p>
      <h2>Gratis ongkir untuk belanja minimal Rp 250.000</h2>
      <p>Gabungkan beberapa produk favoritmu dalam satu pesanan dan nikmati pengiriman gratis ke seluruh Indonesia.</p>
    </div>
    <a class="tombol tombol-terang" href="<?= e(BASE_URL) ?>/produk.php">Cari Produk</a>
  </div>
</section>

<section class="bagian bagian-putih">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Mudah dan cepat</p>
        <h2>Cara belanja di Rerajut</h2>
      </div>
    </div>
    <div class="langkah-grid">
      <div class="langkah">
        <span>01</span>
        <h3>Pilih produk</h3>
        <p>Telusuri katalog dan cek ukuran serta kondisi produk.</p>
      </div>
      <div class="langkah">
        <span>02</span>
        <h3>Masukkan keranjang</h3>
        <p>Tambahkan produk favorit lalu lanjut ke halaman checkout.</p>
      </div>
      <div class="langkah">
        <span>03</span>
        <h3>Isi data pengiriman</h3>
        <p>Lengkapi nama, alamat, dan pilih metode pembayaran.</p>
      </div>
      <div class="langkah">
        <span>04</span>
        <h3>Pantau pesanan</h3>
        <p>Simpan kode pesanan untuk melacak status melalui halaman lacak.</p>
      </div>
    </div>
  </div>
</section>

<section class="bagian ringkas-seo">
  <div class="wadah ringkas-dalam">
    <div>
      <h2>Thrift bukan sekadar hemat</h2>
      <p>Rerajut Thrift adalah toko baju thrift online yang mengumpulkan pakaian layak pakai dari berbagai sumber terpercaya. Kami memeriksa setiap produk, mencuci, lalu memotretnya apa adanya agar kamu tahu kondisi barang sebelum membeli.</p>
      <p>Dengan membeli pakaian thrift, kamu ikut memperpanjang usia pakai pakaian dan mengurangi limbah tekstil. Setiap produk memiliki stok satuan sehingga kesempatan memiliki potongan yang sama sangat kecil.</p>
    </div>
    <div>
      <h2>Pengiriman dan pembayaran</h2>
      <p>Pesanan dikirim dari Yogyakarta melalui kurir reguler. Ongkir tetap Rp 15.000 dan gratis untuk pembelian minimal Rp 250.000. Pembayaran dapat dilakukan melalui transfer bank, e-wallet, atau bayar di tempat untuk area tertentu.</p>
      <p>Butuh bantuan memilih ukuran? Hubungi kami melalui halaman kontak atau WhatsApp pada jam operasional 08.00 sampai 20.00 WIB.</p>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
