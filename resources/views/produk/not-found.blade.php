@extends('layouts.app')

@section('content')
<section class="bagian">
  <div class="wadah kosong">
    <h1>Produk tidak ditemukan</h1>
    <p>Produk mungkin sudah terjual atau tautannya berubah.</p>
    <a class="tombol" href="{{ route('produk.index') }}">Kembali ke Katalog</a>
  </div>
</section>
@endsection
