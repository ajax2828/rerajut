@extends('layouts.app')

@section('content')
<section class="hero">
  <div class="wadah hero-dalam">
    <div class="hero-teks">
      <p class="label-kecil">Kurasi terbatas setiap minggu</p>
      <h1>{{ config('toko.tagline') }}</h1>
      <p class="hero-deskripsi">Setiap potong sudah dicek kondisi dan kebersihannya. Stok hanya satu, jadi kamu tidak perlu takut gayanya pasaran.</p>
      <div class="hero-aksi">
        <a class="tombol" href="{{ route('produk.index') }}">Belanja Sekarang</a>
        <a class="tombol tombol-garis" href="{{ route('tentang') }}">Cara Kerja Kami</a>
      </div>
      <ul class="hero-poin">
        <li>Mulai {{ rupiah($hargaTermurah) }}</li>
        <li>Gratis ongkir di atas {{ rupiah(config('toko.gratis_ongkir_min')) }}</li>
        <li>Bisa COD dan transfer bank</li>
      </ul>
    </div>
    <div class="hero-gambar">
      <img src="{{ asset('images/' . $heroGambar) }}" alt="Koleksi baju thrift pilihan {{ config('toko.nama') }}" width="1200" height="1600">
      <div class="hero-label">
        <strong>{{ $jumlahSiapKirim }} produk</strong>
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
      <a class="tautan-lain" href="{{ route('produk.index') }}">Lihat semua produk</a>
    </div>
    <div class="kategori-grid">
      @foreach ($kategori as $k)
      <a class="kategori-kartu" href="{{ route('produk.index', ['kategori' => $k->slug]) }}">
        <strong>{{ $k->nama }}</strong>
        <span>{{ (int) $k->produk_count }} produk</span>
      </a>
      @endforeach
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
      <a class="tautan-lain" href="{{ route('produk.index') }}">Lihat semua produk</a>
    </div>
    <div class="grid-produk">
      @foreach ($unggulan as $produk)
      @include('partials.kartu-produk', ['produk' => $produk])
      @endforeach
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
      @foreach ($terbaru as $produk)
      @include('partials.kartu-produk', ['produk' => $produk])
      @endforeach
    </div>
  </div>
</section>

<section class="promo">
  <div class="wadah promo-dalam">
    <div>
      <p class="label-kecil">Promo minggu ini</p>
      <h2>Gratis ongkir untuk belanja minimal {{ rupiah(config('toko.gratis_ongkir_min')) }}</h2>
      <p>Gabungkan beberapa produk favoritmu dalam satu pesanan dan nikmati pengiriman gratis ke seluruh Indonesia.</p>
    </div>
    <a class="tombol tombol-terang" href="{{ route('produk.index') }}">Cari Produk</a>
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
      <p>{{ config('toko.nama') }} adalah toko baju thrift online yang mengumpulkan pakaian layak pakai dari berbagai sumber terpercaya. Kami memeriksa setiap produk, mencuci, lalu memotretnya apa adanya agar kamu tahu kondisi barang sebelum membeli.</p>
      <p>Dengan membeli pakaian thrift, kamu ikut memperpanjang usia pakai pakaian dan mengurangi limbah tekstil. Setiap produk memiliki stok satuan sehingga kesempatan memiliki potongan yang sama sangat kecil.</p>
    </div>
    <div>
      <h2>Pengiriman dan pembayaran</h2>
      <p>Pesanan dikirim dari Yogyakarta melalui kurir reguler. Ongkir tetap {{ rupiah(config('toko.ongkir')) }} dan gratis untuk pembelian minimal {{ rupiah(config('toko.gratis_ongkir_min')) }}. Pembayaran dapat dilakukan melalui transfer bank, e-wallet, atau bayar di tempat untuk area tertentu.</p>
      <p>Butuh bantuan memilih ukuran? Hubungi kami melalui halaman kontak atau WhatsApp pada jam operasional 08.00 sampai 20.00 WIB.</p>
    </div>
  </div>
</section>
@endsection
