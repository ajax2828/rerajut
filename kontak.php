<?php
require_once __DIR__ . '/includes/fungsi.php';

$data = ['nama' => '', 'email' => '', 'subjek' => '', 'pesan' => ''];
$galat = [];
$berhasil = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $galat[] = 'Sesi tidak valid. Silakan muat ulang halaman.';
    }
    foreach ($data as $kunci => $nilai) {
        $data[$kunci] = trim($_POST[$kunci] ?? '');
    }
    if (mb_strlen($data['nama']) < 3) {
        $galat[] = 'Nama minimal 3 karakter.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $galat[] = 'Alamat email tidak valid.';
    }
    if ($data['subjek'] === '') {
        $galat[] = 'Subjek pesan wajib diisi.';
    }
    if (mb_strlen($data['pesan']) < 10) {
        $galat[] = 'Pesan minimal 10 karakter.';
    }
    if (!$galat) {
        $stmt = $pdo->prepare('INSERT INTO pesan_kontak (nama, email, subjek, pesan) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['nama'], $data['email'], $data['subjek'], $data['pesan']]);
        $berhasil = true;
        $data = ['nama' => '', 'email' => '', 'subjek' => '', 'pesan' => ''];
    }
}

$skemaFaq = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'Apakah semua produk sudah dicuci?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ya. Setiap produk dicuci sebelum disimpan dan dikirim. Kami menggunakan detergen lembut agar warna dan bahan tetap terjaga.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Bisakah saya menukar produk?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Penukaran dapat dilakukan jika produk tidak sesuai deskripsi. Hubungi kami maksimal 2 x 24 jam setelah barang diterima dengan menyertakan foto dan kode pesanan.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Berapa lama pesanan dikirim?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Pesanan diproses maksimal 2 hari kerja setelah pembayaran dikonfirmasi. Estimasi pengiriman 2 sampai 5 hari kerja tergantung kota tujuan.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Apakah bisa bayar di tempat?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'COD tersedia untuk area tertentu. Pilih metode COD saat checkout, lalu tunggu konfirmasi dari tim kami melalui WhatsApp.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Bagaimana cara mengetahui ukuran yang tepat?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Baca bagian deskripsi dan tabel ukuran pada halaman produk. Jika masih ragu, kirim pesan dengan ukuran badanmu dan kami bantu perkirakan.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Apakah stok bisa lebih dari satu?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Hampir semua produk hanya tersedia satu unit karena berasal dari barang thrift. Jumlah stok tertera pada halaman produk.'],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

$judul = 'Hubungi Rerajut Thrift | Pertanyaan Produk dan Pesanan';
$deskripsi = 'Hubungi tim Rerajut Thrift untuk pertanyaan ukuran, kondisi produk, pembayaran, atau pengiriman baju thrift.';
$kanonikal = SITE_URL . '/kontak.php';
$halaman = 'kontak';
require_once __DIR__ . '/includes/header.php';
?>
<script type="application/ld+json"><?= $skemaFaq ?></script>
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="<?= e(BASE_URL) ?>/index.php">Beranda</a>
      <span>/</span>
      <span>Kontak</span>
    </nav>
    <h1>Hubungi Kami</h1>
    <p>Ada pertanyaan tentang ukuran atau pesanan? Kirim pesan, kami balas pada hari yang sama.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah kontak-grid">
    <div class="kontak-info">
      <h2>Informasi kontak</h2>
      <dl>
        <div>
          <dt>Alamat</dt>
          <dd><?= e(SITE_ALAMAT) ?></dd>
        </div>
        <div>
          <dt>Telepon dan WhatsApp</dt>
          <dd><?= e(SITE_TELEPON) ?></dd>
        </div>
        <div>
          <dt>Email</dt>
          <dd><?= e(SITE_EMAIL) ?></dd>
        </div>
        <div>
          <dt>Jam operasional</dt>
          <dd>Senin sampai Sabtu, 08.00 sampai 20.00 WIB</dd>
        </div>
        <div>
          <dt>Media sosial</dt>
          <dd>Instagram @rerajut.thrift</dd>
        </div>
      </dl>
      <div class="peta">
        <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=110.35%2C-7.82%2C110.40%2C-7.78&amp;layer=mapnik" title="Peta lokasi Rerajut Thrift" loading="lazy"></iframe>
      </div>
    </div>

    <div class="kontak-form">
      <?php if ($berhasil): ?>
      <div class="kotak-sukses">
        <h2>Pesan terkirim</h2>
        <p>Terima kasih sudah menghubungi kami. Tim Rerajut akan membalas melalui email dalam 1 x 24 jam.</p>
      </div>
      <?php endif; ?>
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
      <form action="<?= e(BASE_URL) ?>/kontak.php" method="post">
        <?= csrf_field() ?>
        <div class="baris-form">
          <div class="kolom-form">
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" value="<?= e($data['nama']) ?>" required>
          </div>
          <div class="kolom-form">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($data['email']) ?>" required>
          </div>
        </div>
        <div class="kolom-form">
          <label for="subjek">Subjek</label>
          <input type="text" id="subjek" name="subjek" value="<?= e($data['subjek']) ?>" required>
        </div>
        <div class="kolom-form">
          <label for="pesan">Pesan</label>
          <textarea id="pesan" name="pesan" rows="6" required><?= e($data['pesan']) ?></textarea>
        </div>
        <button type="submit" class="tombol">Kirim Pesan</button>
      </form>
    </div>
  </div>
</section>

<section class="bagian bagian-putih">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Pertanyaan umum</p>
        <h2>Pertanyaan yang sering diajukan</h2>
      </div>
    </div>
    <div class="faq-grid">
      <details>
        <summary>Apakah semua produk sudah dicuci?</summary>
        <p>Ya. Setiap produk dicuci sebelum disimpan dan dikirim. Kami menggunakan detergen lembut agar warna dan bahan tetap terjaga.</p>
      </details>
      <details>
        <summary>Bisakah saya menukar produk?</summary>
        <p>Penukaran dapat dilakukan jika produk tidak sesuai deskripsi. Hubungi kami maksimal 2 x 24 jam setelah barang diterima dengan menyertakan foto dan kode pesanan.</p>
      </details>
      <details>
        <summary>Berapa lama pesanan dikirim?</summary>
        <p>Pesanan diproses maksimal 2 hari kerja setelah pembayaran dikonfirmasi. Estimasi pengiriman 2 sampai 5 hari kerja tergantung kota tujuan.</p>
      </details>
      <details>
        <summary>Apakah bisa bayar di tempat?</summary>
        <p>COD tersedia untuk area tertentu. Pilih metode COD saat checkout, lalu tunggu konfirmasi dari tim kami melalui WhatsApp.</p>
      </details>
      <details>
        <summary>Bagaimana cara mengetahui ukuran yang tepat?</summary>
        <p>Baca bagian deskripsi dan tabel ukuran pada halaman produk. Jika masih ragu, kirim pesan dengan ukuran badanmu dan kami bantu perkirakan.</p>
      </details>
      <details>
        <summary>Apakah stok bisa lebih dari satu?</summary>
        <p>Hampir semua produk hanya tersedia satu unit karena berasal dari barang thrift. Jumlah stok tertera pada halaman produk.</p>
      </details>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
