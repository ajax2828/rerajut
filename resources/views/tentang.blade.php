@extends('layouts.app')

@section('content')
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="{{ route('home') }}">Beranda</a>
      <span>/</span>
      <span>Tentang</span>
    </nav>
    <h1>Tentang {{ config('toko.nama') }}</h1>
    <p>Toko kecil yang percaya bahwa pakaian layak pakai masih punya banyak cerita.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah tentang-grid">
    <div>
      <img src="{{ asset('images/produk-06.jpg') }}" alt="Cardigan rajut motif kotak pilihan {{ config('toko.nama') }}" width="1200" height="1600">
    </div>
    <div class="tentang-teks">
      <p class="label-kecil">Cerita kami</p>
      <h2>Berawal dari lemari sendiri</h2>
      <p>{{ config('toko.nama') }} dimulai pada 2024 dari kebiasaan mengumpulkan pakaian layak pakai yang sudah tidak dipakai. Daripada menumpuk di lemari, pakaian tersebut kami bersihkan, foto, dan jual kembali dengan harga yang lebih masuk akal.</p>
      <p>Sampai sekarang kami fokus pada satu hal: memilih barang yang benar-benar layak dipakai. Setiap produk diperiksa bagian kancing, resleting, jahitan, dan noda. Produk yang tidak lolos pemeriksaan tidak kami jual.</p>
      <p>Kami percaya thrift bukan tren sesaat, melainkan cara sederhana untuk berbelanja lebih bijak dan mengurangi limbah tekstil.</p>
    </div>
  </div>
</section>

<section class="bagian bagian-putih">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Yang kami pegang</p>
        <h2>Tiga prinsip Rerajut</h2>
      </div>
    </div>
    <div class="langkah-grid">
      <div class="langkah">
        <span>01</span>
        <h3>Jujur pada kondisi</h3>
        <p>Deskripsi dan foto dibuat apa adanya. Jika ada kekurangan kecil, kami tuliskan.</p>
      </div>
      <div class="langkah">
        <span>02</span>
        <h3>Harga masuk akal</h3>
        <p>Harga kami sesuaikan dengan kondisi dan jenis bahan, bukan sekadar ikut tren.</p>
      </div>
      <div class="langkah">
        <span>03</span>
        <h3>Ramah lingkungan</h3>
        <p>Memperpanjang usia pakaian berarti mengurangi sampah tekstil yang berakhir di tempat pembuangan.</p>
      </div>
    </div>
  </div>
</section>

<section class="bagian">
  <div class="wadah statistik">
    <div>
      <strong>1.200+</strong>
      <span>pakaian terselamatkan</span>
    </div>
    <div>
      <strong>850+</strong>
      <span>pesanan dikirim</span>
    </div>
    <div>
      <strong>4,8 dari 5</strong>
      <span>penilaian pembeli</span>
    </div>
    <div>
      <strong>90 persen</strong>
      <span>pembeli kembali lagi</span>
    </div>
  </div>
</section>

<section class="bagian bagian-putih">
  <div class="wadah ringkas-dalam">
    <div>
      <h2>Cara kami memilih produk</h2>
      <p>Produk kami kumpulkan dari penjual perorangan, bazaar, dan penukaran pakaian. Setiap barang melewati pemeriksaan bahan, jahitan, dan kelengkapan aksesori sebelum masuk katalog.</p>
      <p>Pakaian dicuci dengan detergen lembut, lalu disetrika dan difoto di bawah cahaya alami. Kami tidak menggunakan filter warna agar pembeli melihat kondisi sebenarnya.</p>
    </div>
    <div>
      <h2>Komitmen layanan</h2>
      <p>Kami membalas pertanyaan pada hari yang sama di jam operasional. Pesanan dikemas dalam kemasan ramah lingkungan dan dikirim maksimal dua hari kerja setelah pembayaran dikonfirmasi.</p>
      <p>Jika ada kendala, hubungi kami melalui halaman kontak. Kami akan berusaha memberikan solusi terbaik sesuai ketentuan yang berlaku.</p>
    </div>
  </div>
</section>

<section class="promo">
  <div class="wadah promo-dalam">
    <div>
      <p class="label-kecil">Siap mulai</p>
      <h2>Temukan potongan yang cocok untukmu</h2>
      <p>Stok terus berganti setiap minggu. Simpan halaman katalog dan cek produk baru secara berkala.</p>
    </div>
    <a class="tombol tombol-terang" href="{{ route('produk.index') }}">Lihat Katalog</a>
  </div>
</section>
@endsection
