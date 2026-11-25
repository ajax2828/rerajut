@extends('layouts.app')

@push('head')
<script type="application/ld+json">{!! $skemaFaq !!}</script>
@endpush

@section('content')
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="{{ route('home') }}">Beranda</a>
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
          <dd>{{ config('toko.alamat') }}</dd>
        </div>
        <div>
          <dt>Telepon dan WhatsApp</dt>
          <dd>{{ config('toko.telepon') }}</dd>
        </div>
        <div>
          <dt>Email</dt>
          <dd>{{ config('toko.email') }}</dd>
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
        <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=110.35%2C-7.82%2C110.40%2C-7.78&amp;layer=mapnik" title="Peta lokasi {{ config('toko.nama') }}" loading="lazy"></iframe>
      </div>
    </div>

    <div class="kontak-form">
      @if (session('kontak_berhasil'))
      <div class="kotak-sukses">
        <h2>Pesan terkirim</h2>
        <p>Terima kasih sudah menghubungi kami. Tim Rerajut akan membalas melalui email dalam 1 x 24 jam.</p>
      </div>
      @endif
      @if ($errors->any())
      <div class="kotak-galat">
        <h2>Periksa kembali</h2>
        <ul>
          @foreach ($errors->all() as $g)
          <li>{{ $g }}</li>
          @endforeach
        </ul>
      </div>
      @endif
      <form action="{{ route('kontak.store') }}" method="post">
        @csrf
        <div class="baris-form">
          <div class="kolom-form">
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" value="{{ old('nama', '') }}" maxlength="120" required>
          </div>
          <div class="kolom-form">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', '') }}" maxlength="150" required>
          </div>
        </div>
        <div class="kolom-form">
          <label for="subjek">Subjek</label>
          <input type="text" id="subjek" name="subjek" value="{{ old('subjek', '') }}" maxlength="150" required>
        </div>
        <div class="kolom-form">
          <label for="pesan">Pesan</label>
          <textarea id="pesan" name="pesan" rows="6" required>{{ old('pesan', '') }}</textarea>
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
@endsection
