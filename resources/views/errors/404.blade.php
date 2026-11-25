@extends('layouts.app', [
    'judul' => 'Halaman tidak ditemukan | ' . config('toko.nama'),
    'deskripsi' => 'Halaman yang kamu cari tidak tersedia di ' . config('toko.nama') . '.',
    'kanonikal' => url('/'),
    'halaman' => '',
    'robots' => 'noindex, follow',
])

@section('content')
<section class="bagian">
  <div class="wadah kosong">
    <h1>Halaman tidak ditemukan</h1>
    <p>Tautan mungkin sudah berubah atau produk sudah tidak tersedia.</p>
    <a class="tombol" href="{{ route('home') }}">Kembali ke Beranda</a>
  </div>
</section>
@endsection
