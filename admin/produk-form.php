<?php
require_once __DIR__ . '/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$produk = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM produk WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $produk = $stmt->fetch();
    if (!$produk) {
        flash_set('gagal', 'Produk tidak ditemukan.');
        redirect(BASE_URL . '/admin/produk.php');
    }
}

$kategoriList = kategori_all($pdo);
$galat = [];
$data = [
    'nama' => $produk['nama'] ?? '',
    'kategori_id' => $produk['kategori_id'] ?? ($kategoriList[0]['id'] ?? ''),
    'deskripsi' => $produk['deskripsi'] ?? '',
    'harga' => $produk['harga'] ?? '',
    'harga_asli' => $produk['harga_asli'] ?? '',
    'ukuran' => $produk['ukuran'] ?? '',
    'kondisi' => $produk['kondisi'] ?? 'Sangat Baik',
    'stok' => $produk['stok'] ?? 1,
    'unggulan' => $produk['unggulan'] ?? 0,
];
$gambarLama = $produk['gambar'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $galat[] = 'Sesi tidak valid. Silakan coba lagi.';
    }
    $data['nama'] = trim($_POST['nama'] ?? '');
    $data['kategori_id'] = (int) ($_POST['kategori_id'] ?? 0);
    $data['deskripsi'] = trim($_POST['deskripsi'] ?? '');
    $data['harga'] = (int) preg_replace('/[^0-9]/', '', $_POST['harga'] ?? '');
    $data['harga_asli'] = (int) preg_replace('/[^0-9]/', '', $_POST['harga_asli'] ?? '');
    $data['ukuran'] = trim($_POST['ukuran'] ?? '');
    $data['kondisi'] = trim($_POST['kondisi'] ?? '');
    $data['stok'] = (int) ($_POST['stok'] ?? 0);
    $data['unggulan'] = isset($_POST['unggulan']) ? 1 : 0;

    if (mb_strlen($data['nama']) < 3) {
        $galat[] = 'Nama produk minimal 3 karakter.';
    }
    $stmt = $pdo->prepare('SELECT id FROM kategori WHERE id = ? LIMIT 1');
    $stmt->execute([$data['kategori_id']]);
    if (!$stmt->fetch()) {
        $galat[] = 'Kategori produk wajib dipilih.';
    }
    if (mb_strlen($data['deskripsi']) < 10) {
        $galat[] = 'Deskripsi produk minimal 10 karakter.';
    }
    if ($data['harga'] < 1) {
        $galat[] = 'Harga produk wajib diisi.';
    }
    if ($data['harga_asli'] < $data['harga']) {
        $galat[] = 'Harga asli tidak boleh lebih rendah dari harga jual.';
    }
    if ($data['ukuran'] === '') {
        $galat[] = 'Ukuran produk wajib diisi.';
    }
    if ($data['kondisi'] === '') {
        $galat[] = 'Kondisi produk wajib dipilih.';
    }
    if ($data['stok'] < 0) {
        $galat[] = 'Stok tidak boleh negatif.';
    }

    $gambarBaru = null;
    if (!empty($_FILES['gambar']['name'])) {
        $file = $_FILES['gambar'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $galat[] = 'File gambar gagal diunggah.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $galat[] = 'Ukuran gambar maksimal 2 MB.';
        } else {
            $izin = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if (!isset($izin[$mime])) {
                $galat[] = 'Format gambar harus JPG, PNG, atau WEBP.';
            } else {
                $gambarBaru = 'produk-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $izin[$mime];
                if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../images/' . $gambarBaru)) {
                    $galat[] = 'Gambar gagal disimpan.';
                    $gambarBaru = null;
                }
            }
        }
    }
    if (!$produk && !$gambarBaru) {
        $galat[] = 'Gambar produk wajib diunggah.';
    }

    if ($galat && $gambarBaru) {
        $berkasBaru = __DIR__ . '/../images/' . $gambarBaru;
        if (is_file($berkasBaru)) {
            unlink($berkasBaru);
        }
        $gambarBaru = null;
    }

    if (!$galat) {
        $dasar = slug_aman($data['nama']);
        if ($dasar === '') {
            $dasar = 'produk';
        }
        $slug = $dasar;
        $nomor = 2;
        while (true) {
            $stmt = $pdo->prepare('SELECT id FROM produk WHERE slug = ? AND id <> ? LIMIT 1');
            $stmt->execute([$slug, $id]);
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $dasar . '-' . $nomor;
            $nomor++;
        }

        if ($produk) {
            $gambarFinal = $gambarBaru ?: $gambarLama;
            $stmt = $pdo->prepare('UPDATE produk SET kategori_id = ?, nama = ?, slug = ?, deskripsi = ?, harga = ?, harga_asli = ?, ukuran = ?, kondisi = ?, stok = ?, gambar = ?, unggulan = ? WHERE id = ?');
            $stmt->execute([$data['kategori_id'], $data['nama'], $slug, $data['deskripsi'], $data['harga'], $data['harga_asli'], $data['ukuran'], $data['kondisi'], $data['stok'], $gambarFinal, $data['unggulan'], $id]);
            if ($gambarBaru && $gambarLama !== '' && $gambarLama !== $gambarBaru) {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE gambar = ?');
                $stmt->execute([$gambarLama]);
                if ((int) $stmt->fetchColumn() === 0) {
                    $berkas = __DIR__ . '/../images/' . basename($gambarLama);
                    if (is_file($berkas)) {
                        unlink($berkas);
                    }
                }
            }
            flash_set('sukses', 'Produk berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO produk (kategori_id, nama, slug, deskripsi, harga, harga_asli, ukuran, kondisi, stok, gambar, unggulan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$data['kategori_id'], $data['nama'], $slug, $data['deskripsi'], $data['harga'], $data['harga_asli'], $data['ukuran'], $data['kondisi'], $data['stok'], $gambarBaru, $data['unggulan']]);
            flash_set('sukses', 'Produk baru berhasil ditambahkan.');
        }
        redirect(BASE_URL . '/admin/produk.php');
    }
}

$judul = ($produk ? 'Ubah Produk' : 'Tambah Produk') . ' | Admin Rerajut Thrift';
$deskripsi = 'Form pengelolaan produk Rerajut Thrift.';
$kanonikal = SITE_URL . '/admin/produk-form.php';
$robots = 'noindex, nofollow';
$halaman = 'admin-produk';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bagian">
  <div class="wadah wadah-sempit">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Panel pengelola</p>
        <h1><?= $produk ? 'Ubah Produk' : 'Tambah Produk' ?></h1>
      </div>
      <a class="tautan-lain" href="<?= e(BASE_URL) ?>/admin/produk.php">Kembali ke daftar</a>
    </div>

    <?php if ($galat): ?>
    <div class="kotak-galat">
      <h2>Periksa kembali data berikut</h2>
      <ul>
        <?php foreach ($galat as $g): ?>
        <li><?= e($g) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <div class="admin-panel">
      <form action="<?= e(BASE_URL) ?>/admin/produk-form.php<?= $produk ? '?id=' . (int) $produk['id'] : '' ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="baris-form">
          <div class="kolom-form">
            <label for="nama">Nama produk</label>
            <input type="text" id="nama" name="nama" value="<?= e($data['nama']) ?>" required>
          </div>
          <div class="kolom-form">
            <label for="kategori_id">Kategori</label>
            <select id="kategori_id" name="kategori_id" required>
              <?php foreach ($kategoriList as $kategori): ?>
              <option value="<?= (int) $kategori['id'] ?>" <?= (int) $data['kategori_id'] === (int) $kategori['id'] ? 'selected' : '' ?>><?= e($kategori['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="kolom-form">
          <label for="deskripsi">Deskripsi</label>
          <textarea id="deskripsi" name="deskripsi" rows="5" required><?= e($data['deskripsi']) ?></textarea>
        </div>

        <div class="baris-form">
          <div class="kolom-form">
            <label for="harga">Harga jual</label>
            <input type="text" id="harga" name="harga" value="<?= e($data['harga']) ?>" inputmode="numeric" required>
          </div>
          <div class="kolom-form">
            <label for="harga_asli">Harga asli</label>
            <input type="text" id="harga_asli" name="harga_asli" value="<?= e($data['harga_asli']) ?>" inputmode="numeric" required>
          </div>
          <div class="kolom-form">
            <label for="stok">Stok</label>
            <input type="number" id="stok" name="stok" value="<?= (int) $data['stok'] ?>" min="0" required>
          </div>
        </div>

        <div class="baris-form">
          <div class="kolom-form">
            <label for="ukuran">Ukuran</label>
            <input type="text" id="ukuran" name="ukuran" value="<?= e($data['ukuran']) ?>" placeholder="Contoh: M atau All Size" required>
          </div>
          <div class="kolom-form">
            <label for="kondisi">Kondisi</label>
            <select id="kondisi" name="kondisi" required>
              <?php foreach (['Sangat Baik', 'Baik', 'Cukup'] as $kondisi): ?>
              <option value="<?= e($kondisi) ?>" <?= $data['kondisi'] === $kondisi ? 'selected' : '' ?>><?= e($kondisi) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="kolom-form">
          <label for="gambar">Gambar produk</label>
          <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp">
          <small class="bantuan-form">Format JPG, PNG, atau WEBP. Maksimal 2 MB. <?= $produk ? 'Biarkan kosong jika tidak ingin mengganti gambar.' : 'Wajib diunggah untuk produk baru.' ?></small>
        </div>

        <?php if ($gambarLama): ?>
        <div class="gambar-pratinjau">
          <img src="<?= e(BASE_URL) ?>/images/<?= e($gambarLama) ?>" alt="<?= e($data['nama']) ?>" width="180" height="240">
          <span>Gambar saat ini</span>
        </div>
        <?php endif; ?>

        <label class="centang-form">
          <input type="checkbox" name="unggulan" value="1" <?= (int) $data['unggulan'] === 1 ? 'checked' : '' ?>>
          Tampilkan sebagai produk unggulan
        </label>

        <button type="submit" class="tombol tombol-lebar"><?= $produk ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
      </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
