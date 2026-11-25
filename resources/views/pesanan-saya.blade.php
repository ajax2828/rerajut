@extends('layouts.app')

@section('content')
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="{{ route('home') }}">Beranda</a>
      <span>/</span>
      <span>Pesanan Saya</span>
    </nav>
    <h1>Pesanan Saya</h1>
    <p>Daftar pesanan yang tersimpan di perangkat ini. Data disimpan otomatis setelah checkout berhasil.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah wadah-sempit">
    <div id="pesanan-saya-muat" class="pesanan-saya-info">Memuat daftar pesanan.</div>
    <div id="pesanan-saya-kosong" class="kosong" hidden>
      <h2>Belum ada pesanan</h2>
      <p>Pesanan yang kamu buat akan muncul di halaman ini secara otomatis.</p>
      <a class="tombol" href="{{ route('produk.index') }}">Mulai Belanja</a>
    </div>
    <div
      id="daftar-pesanan-saya"
      class="daftar-pesanan-saya"
      data-api="{{ route('api.pesanan') }}"
      data-lacak-url="{{ route('lacak') }}"
      data-sukses-url="{{ route('pesanan.sukses') }}"
    ></div>
    <p class="catatan-perangkat">Daftar ini tersimpan di browser perangkat yang kamu pakai. Jika memakai perangkat lain, gunakan kode pesanan pada halaman lacak pesanan.</p>
  </div>
</section>
@endsection
