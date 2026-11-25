@extends('layouts.app')

@section('content')
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="{{ route('home') }}">Beranda</a>
      <span>/</span>
      <span>Keranjang</span>
    </nav>
    <h1>Keranjang Belanja</h1>
    <p>Periksa produk pilihanmu sebelum melanjutkan ke pembayaran.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah">
    @if (!$items)
    <div class="kosong">
      <h2>Keranjang masih kosong</h2>
      <p>Yuk pilih baju thrift favoritmu dari katalog kami.</p>
      <a class="tombol" href="{{ route('produk.index') }}">Mulai Belanja</a>
    </div>
    @else
    <div class="keranjang-grid">
      <div class="keranjang-daftar">
        @foreach ($items as $item)
        <article class="baris-keranjang">
          <a class="baris-gambar" href="{{ route('produk.show', $item['slug']) }}">
            <img src="{{ asset('images/' . $item['gambar']) }}" alt="{{ $item['nama'] }}" width="300" height="400" loading="lazy">
          </a>
          <div class="baris-info">
            <h2><a href="{{ route('produk.show', $item['slug']) }}">{{ $item['nama'] }}</a></h2>
            <p>Ukuran {{ $item['ukuran'] }} / {{ $item['kondisi'] }}</p>
            <p class="baris-harga">{{ rupiah($item['harga']) }}</p>
            <form action="{{ route('keranjang.handle') }}" method="post" class="form-jumlah">
              @csrf
              <input type="hidden" name="aksi" value="ubah">
              <input type="hidden" name="id" value="{{ (int) $item['id'] }}">
              <label for="jumlah-{{ (int) $item['id'] }}">Jumlah</label>
              <input type="number" id="jumlah-{{ (int) $item['id'] }}" name="jumlah" value="{{ (int) $item['jumlah'] }}" min="1" max="{{ (int) $item['stok'] }}">
              <button type="submit" class="tombol tombol-kecil tombol-garis">Perbarui</button>
            </form>
          </div>
          <div class="baris-akhir">
            <p>{{ rupiah($item['subtotal']) }}</p>
            <form action="{{ route('keranjang.handle') }}" method="post">
              @csrf
              <input type="hidden" name="aksi" value="hapus">
              <input type="hidden" name="id" value="{{ (int) $item['id'] }}">
              <button type="submit" class="tautan-hapus">Hapus</button>
            </form>
          </div>
        </article>
        @endforeach
        <a class="tautan-lain" href="{{ route('produk.index') }}">Lanjut belanja</a>
      </div>
      <aside class="ringkasan">
        <h2>Ringkasan Pesanan</h2>
        <div class="ringkasan-baris">
          <span>Subtotal</span>
          <strong>{{ rupiah($subtotal) }}</strong>
        </div>
        <div class="ringkasan-baris">
          <span>Ongkir</span>
          <strong>{{ $ongkir === 0 ? 'Gratis' : rupiah($ongkir) }}</strong>
        </div>
        @if ($subtotal < (int) config('toko.gratis_ongkir_min'))
        <p class="catatan-ongkir">Tambah {{ rupiah((int) config('toko.gratis_ongkir_min') - $subtotal) }} lagi untuk gratis ongkir.</p>
        @endif
        <div class="ringkasan-total">
          <span>Total</span>
          <strong>{{ rupiah($total) }}</strong>
        </div>
        <a class="tombol tombol-lebar" href="{{ route('checkout.show') }}">Lanjut ke Checkout</a>
        <p class="ringkasan-catatan">Pembayaran dilakukan setelah data pesanan dikirim.</p>
      </aside>
    </div>
    @endif
  </div>
</section>
@endsection
