@extends('layouts.app')

@push('head')
<script type="application/ld+json">{!! $skemaProduk !!}</script>
<script type="application/ld+json">{!! $skemaRemah !!}</script>
@endpush

@section('content')
<section class="kepala-halaman kepala-sempit">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="{{ route('home') }}">Beranda</a>
      <span>/</span>
      <a href="{{ route('produk.index') }}">Produk</a>
      <span>/</span>
      <a href="{{ route('produk.index', ['kategori' => $produk->kategori?->slug ?? '']) }}">{{ $produk->kategori?->nama ?? 'Produk' }}</a>
      <span>/</span>
      <span>{{ $produk->nama }}</span>
    </nav>
  </div>
</section>

<section class="bagian bagian-detail">
  <div class="wadah detail-grid">
    <div class="detail-gambar">
      <img src="{{ asset('images/' . $produk->gambar) }}" alt="{{ $produk->nama }} ukuran {{ $produk->ukuran }} kondisi {{ $produk->kondisi }}" width="1200" height="1600">
      <div class="detail-catatan">
        <p>Produk yang difoto adalah produk yang akan kamu terima.</p>
      </div>
    </div>
    <div class="detail-info">
      <p class="label-kecil">{{ $produk->kategori?->nama ?? 'Thrift' }} / {{ $produk->kondisi }}</p>
      <h1>{{ $produk->nama }}</h1>
      <div class="detail-harga">
        <strong>{{ rupiah($produk->harga) }}</strong>
        @if ($diskon > 0)
        <span class="harga-coret">{{ rupiah($produk->harga_asli) }}</span>
        <span class="label-diskon">Hemat {{ $diskon }} persen</span>
        @endif
      </div>
      <p class="detail-ringkas">{{ $produk->deskripsi }}</p>

      <dl class="detail-spesifikasi">
        <div><dt>Ukuran</dt><dd>{{ $produk->ukuran }}</dd></div>
        <div><dt>Kondisi</dt><dd>{{ $produk->kondisi }}</dd></div>
        <div><dt>Kategori</dt><dd>{{ $produk->kategori?->nama ?? '-' }}</dd></div>
        <div><dt>Stok</dt><dd>{{ $habis ? 'Habis' : (int) $produk->stok . ' unit' }}</dd></div>
      </dl>

      @if ($habis)
      <p class="stok-habis">Produk ini sudah terjual. Lihat produk lain dengan kategori serupa di bawah.</p>
      @else
      <form action="{{ route('keranjang.handle') }}" method="post" class="form-beli">
        @csrf
        <input type="hidden" name="aksi" value="tambah">
        <input type="hidden" name="id" value="{{ (int) $produk->id }}">
        <div class="form-beli-baris">
          <label for="jumlah">Jumlah</label>
          <input type="number" id="jumlah" name="jumlah" value="1" min="1" max="{{ (int) $produk->stok }}">
        </div>
        <div class="form-beli-aksi">
          <button type="submit" class="tombol">Tambah ke Keranjang</button>
          <button type="submit" name="lanjut" value="checkout" class="tombol tombol-garis">Beli Sekarang</button>
        </div>
      </form>
      @endif

      <div class="detail-info-box">
        <h2>Info pengiriman</h2>
        <ul>
          <li>Dikirim dari Yogyakarta dalam 1 sampai 2 hari kerja.</li>
          <li>Ongkir tetap {{ rupiah(config('toko.ongkir')) }} dan gratis di atas {{ rupiah(config('toko.gratis_ongkir_min')) }}.</li>
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

@if ($terkait->isNotEmpty())
<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Kamu mungkin suka</p>
        <h2>Produk serupa</h2>
      </div>
      <a class="tautan-lain" href="{{ route('produk.index', ['kategori' => $produk->kategori?->slug ?? '']) }}">Lihat kategori {{ $produk->kategori?->nama ?? '' }}</a>
    </div>
    <div class="grid-produk grid-produk-empat">
      @foreach ($terkait as $itemTerkait)
      @include('partials.kartu-produk', ['produk' => $itemTerkait])
      @endforeach
    </div>
  </div>
</section>
@endif
@endsection
