</main>
<footer class="situs-footer">
  <div class="wadah footer-atas">
    <div class="footer-kolom footer-merek">
      <img src="<?= e(BASE_URL) ?>/assets/img/logo-putih.svg" alt="Logo Rerajut Thrift" width="170" height="48">
      <p>Baju thrift pilihan yang sudah dicek kualitasnya. Stok satuan, jadi gaya kamu tidak akan sama dengan orang lain.</p>
      <p class="footer-kontak"><?= e(SITE_ALAMAT) ?><br><?= e(SITE_TELEPON) ?><br><?= e(SITE_EMAIL) ?></p>
    </div>
    <div class="footer-kolom">
      <h2>Jelajahi</h2>
      <a href="<?= e(BASE_URL) ?>/produk.php">Semua Produk</a>
      <a href="<?= e(BASE_URL) ?>/produk.php?kategori=rajutan">Rajutan</a>
      <a href="<?= e(BASE_URL) ?>/produk.php?kategori=kemeja">Kemeja</a>
      <a href="<?= e(BASE_URL) ?>/produk.php?kategori=aksesoris">Aksesoris</a>
    </div>
    <div class="footer-kolom">
      <h2>Bantuan</h2>
      <a href="<?= e(BASE_URL) ?>/tentang.php">Tentang Kami</a>
      <a href="<?= e(BASE_URL) ?>/lacak.php">Lacak Pesanan</a>
      <a href="<?= e(BASE_URL) ?>/kontak.php">Hubungi Kami</a>
      <a href="<?= e(BASE_URL) ?>/keranjang.php">Keranjang Belanja</a>
    </div>
    <div class="footer-kolom footer-newsletter">
      <h2>Info Drop Terbaru</h2>
      <p>Daftar email untuk mendapat kabar produk baru dan promo mingguan.</p>
      <form action="<?= e(BASE_URL) ?>/langganan.php" method="post" class="form-langganan">
        <?= csrf_field() ?>
        <label class="sr-only" for="email-langganan">Email</label>
        <input type="email" id="email-langganan" name="email" placeholder="Email kamu" required>
        <button type="submit" class="tombol tombol-terang">Daftar</button>
      </form>
    </div>
  </div>
  <div class="wadah footer-bawah">
    <p>Hak cipta <?= date('Y') ?> <?= e(SITE_NAMA) ?>. Seluruh hak cipta dilindungi.</p>
    <p>Belanja hemat, bumi lebih ringan. <a class="tautan-admin" href="<?= e(BASE_URL) ?>/admin/login.php">Admin Toko</a></p>
  </div>
</footer>
<script src="<?= e(BASE_URL) ?>/assets/js/main.js"></script>
</body>
</html>
