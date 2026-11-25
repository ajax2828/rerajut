@extends('layouts.app')

@section('content')
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="{{ route('home') }}">Beranda</a>
      <span>/</span>
      <a href="{{ route('produk.index') }}">Produk</a>
      @if ($kategoriAktif)
      <span>/</span>
      <span>{{ $kategoriAktif->nama }}</span>
      @endif
    </nav>
    <h1>{{ $kategoriAktif ? 'Produk ' . $kategoriAktif->nama : 'Katalog Baju Thrift' }}</h1>
    <p>Pilih produk yang sesuai ukuranmu. Setiap barang hanya tersedia satu unit.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah katalog-dalam">
    <aside class="filter">
      <h2>Filter</h2>
      <form action="{{ route('produk.index') }}" method="get" class="form-filter">
        <label for="q">Cari produk</label>
        <input type="search" id="q" name="q" value="{{ $cari }}" placeholder="Contoh: cardigan">
        @if ($kategoriSlug !== '')
        <input type="hidden" name="kategori" value="{{ $kategoriSlug }}">
        @endif
        <label for="urut">Urutkan</label>
        <select id="urut" name="urut">
          <option value="terbaru" {{ $urut === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
          <option value="termurah" {{ $urut === 'termurah' ? 'selected' : '' }}>Harga terendah</option>
          <option value="termahal" {{ $urut === 'termahal' ? 'selected' : '' }}>Harga tertinggi</option>
          <option value="nama" {{ $urut === 'nama' ? 'selected' : '' }}>Nama produk</option>
        </select>
        <button type="submit" class="tombol tombol-kecil">Terapkan</button>
      </form>
      <h3>Kategori</h3>
      <ul class="filter-kategori">
        <li><a class="{{ $kategoriSlug === '' ? 'aktif' : '' }}" href="{{ route('produk.index') }}">Semua produk</a></li>
        @foreach ($kategoriList as $k)
        <li><a class="{{ $kategoriSlug === $k->slug ? 'aktif' : '' }}" href="{{ route('produk.index', ['kategori' => $k->slug]) }}">{{ $k->nama }} ({{ (int) $k->produk_count }})</a></li>
        @endforeach
      </ul>
    </aside>
    <div class="katalog-utama">
      <div class="katalog-info">
        <p>Menampilkan {{ $produkList->count() }} dari {{ (int) $total }} produk{{ $cari !== '' ? ' untuk pencarian ' . $cari : '' }}.</p>
        @if ($cari !== '' || $kategoriSlug !== '')
        <a href="{{ route('produk.index') }}">Reset filter</a>
        @endif
      </div>
      @if ($produkList->isEmpty())
      <div class="kosong">
        <h2>Produk tidak ditemukan</h2>
        <p>Coba kata kunci lain atau lihat seluruh katalog kami.</p>
        <a class="tombol" href="{{ route('produk.index') }}">Lihat Semua Produk</a>
      </div>
      @else
      <div class="grid-produk">
        @foreach ($produkList as $produk)
        @include('partials.kartu-produk', ['produk' => $produk])
        @endforeach
      </div>
      @if ($totalHal > 1)
      <nav class="paginasi" aria-label="Navigasi halaman">
        @for ($i = 1; $i <= $totalHal; $i++)
        @php
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
        @endphp
        <a class="{{ $i === $hal ? 'aktif' : '' }}" href="{{ route('produk.index', $query) }}">{{ $i }}</a>
        @endfor
      </nav>
      @endif
      @endif
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
      <p>{{ config('toko.nama') }} mencuci setiap produk sebelum dikirim dan memeriksa bagian kancing, resleting, serta jahitan agar kamu menerima barang yang siap pakai.</p>
    </div>
  </div>
</section>
@endsection
