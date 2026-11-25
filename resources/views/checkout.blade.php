@extends('layouts.app')

@section('content')
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="{{ route('home') }}">Beranda</a>
      <span>/</span>
      <a href="{{ route('keranjang.index') }}">Keranjang</a>
      <span>/</span>
      <span>Checkout</span>
    </nav>
    <h1>Checkout</h1>
    <p>Isi data pengiriman dengan lengkap agar pesanan tidak tertunda.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah checkout-grid">
    <div class="checkout-form">
      @if ($errors->any())
      <div class="kotak-galat">
        <h2>Periksa kembali data berikut</h2>
        <ul>
          @foreach ($errors->all() as $g)
          <li>{{ $g }}</li>
          @endforeach
        </ul>
      </div>
      @endif
      <form action="{{ route('checkout.store') }}" method="post" id="form-checkout">
        @csrf
        <fieldset>
          <legend>Data Pembeli</legend>
          <div class="baris-form">
            <div class="kolom-form">
              <label for="nama">Nama lengkap</label>
              <input type="text" id="nama" name="nama" value="{{ old('nama', '') }}" maxlength="120" required>
            </div>
            <div class="kolom-form">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" value="{{ old('email', '') }}" maxlength="150" required>
            </div>
          </div>
          <div class="kolom-form">
            <label for="telepon">Nomor telepon atau WhatsApp</label>
            <input type="tel" id="telepon" name="telepon" value="{{ old('telepon', '') }}" maxlength="30" placeholder="081234567890" required>
          </div>
        </fieldset>

        <fieldset>
          <legend>Alamat Pengiriman</legend>
          <div class="kolom-form">
            <label for="alamat">Alamat lengkap</label>
            <textarea id="alamat" name="alamat" rows="4" placeholder="Nama jalan, nomor rumah, RT, RW, kelurahan, kecamatan" required>{{ old('alamat', '') }}</textarea>
          </div>
          <div class="baris-form">
            <div class="kolom-form">
              <label for="kota">Kota atau kabupaten</label>
              <input type="text" id="kota" name="kota" value="{{ old('kota', '') }}" maxlength="80" required>
            </div>
            <div class="kolom-form">
              <label for="provinsi">Provinsi</label>
              <input type="text" id="provinsi" name="provinsi" value="{{ old('provinsi', '') }}" maxlength="80" required>
            </div>
            <div class="kolom-form">
              <label for="kode_pos">Kode pos</label>
              <input type="text" id="kode_pos" name="kode_pos" value="{{ old('kode_pos', '') }}" inputmode="numeric" maxlength="5" required>
            </div>
          </div>
          <div class="kolom-form">
            <label for="catatan">Catatan untuk penjual</label>
            <textarea id="catatan" name="catatan" rows="3" placeholder="Opsional, misalnya patokan alamat">{{ old('catatan', '') }}</textarea>
          </div>
        </fieldset>

        <fieldset>
          <legend>Metode Pembayaran</legend>
          @php $metodeDipilih = old('metode_bayar', 'Transfer Bank'); @endphp
          <div class="metode-grid">
            @foreach ($metodeBayar as $metode)
            <label class="metode-kartu">
              <input type="radio" name="metode_bayar" value="{{ $metode }}" {{ $metodeDipilih === $metode ? 'checked' : '' }}>
              <span>
                <strong>{{ $metode }}</strong>
                @if ($metode === 'Transfer Bank')
                <small>BCA, BRI, atau Mandiri</small>
                @elseif ($metode === 'E-Wallet')
                <small>GoPay, OVO, atau DANA</small>
                @else
                <small>Bayar saat barang diterima</small>
                @endif
              </span>
            </label>
            @endforeach
          </div>
        </fieldset>

        <button type="submit" class="tombol tombol-lebar">Buat Pesanan</button>
        <p class="checkout-catatan">Dengan menekan tombol ini, kamu menyetujui data yang diisi sudah benar.</p>
      </form>
    </div>

    <aside class="ringkasan ringkasan-checkout">
      <h2>Ringkasan Pesanan</h2>
      <ul class="ringkasan-produk">
        @foreach ($items as $item)
        <li>
          <img src="{{ asset('images/' . $item['gambar']) }}" alt="{{ $item['nama'] }}" width="120" height="160" loading="lazy">
          <div>
            <strong>{{ $item['nama'] }}</strong>
            <span>Ukuran {{ $item['ukuran'] }} x {{ (int) $item['jumlah'] }}</span>
          </div>
          <span>{{ rupiah($item['subtotal']) }}</span>
        </li>
        @endforeach
      </ul>
      <div class="ringkasan-baris">
        <span>Subtotal</span>
        <strong>{{ rupiah($subtotal) }}</strong>
      </div>
      <div class="ringkasan-baris">
        <span>Ongkir</span>
        <strong>{{ $ongkir === 0 ? 'Gratis' : rupiah($ongkir) }}</strong>
      </div>
      <div class="ringkasan-total">
        <span>Total bayar</span>
        <strong>{{ rupiah($total) }}</strong>
      </div>
      <a class="tautan-lain" href="{{ route('keranjang.index') }}">Ubah keranjang</a>
    </aside>
  </div>
</section>
@endsection
