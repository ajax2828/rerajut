@extends('layouts.app')

@section('content')
@if (!$pesanan)
<section class="bagian">
  <div class="wadah kosong">
    <h1>Pesanan tidak ditemukan</h1>
    <p>Kode pesanan tidak cocok. Periksa kembali kode yang kamu simpan.</p>
    <a class="tombol" href="{{ route('lacak') }}">Lacak Pesanan</a>
  </div>
</section>
@else
<section class="kepala-halaman kepala-sukses">
  <div class="wadah">
    <p class="label-kecil">Pesanan diterima</p>
    <h1>Terima kasih, {{ $pesanan->nama }}</h1>
    <p>Pesananmu sudah kami catat. Simpan kode pesanan di bawah untuk melacak status.</p>
    <p class="kode-besar">{{ $pesanan->kode }}</p>
  </div>
</section>

<section class="bagian" data-simpan-pesanan="{{ $pesanan->kode }}">
  <div class="wadah sukses-grid">
    <div class="sukses-utama">
      <div class="kotak-info" id="pembayaran">
        <h2>Instruksi pembayaran</h2>
        @if ($pesanan->status === 'Dibatalkan')
        <div class="kotak-galat">
          <p>Pesanan ini telah dibatalkan. Jika kamu membutuhkan bantuan, silakan hubungi kami melalui halaman kontak dengan menyertakan kode pesanan.</p>
        </div>
        @elseif ($pesanan->metode_bayar === 'Transfer Bank')
        <p>Silakan transfer sebesar <strong>{{ rupiah($pesanan->total) }}</strong> ke rekening berikut.</p>
        <div class="rekening">
          <p><span>Bank</span><strong>{{ config('toko.bank_nama') }}</strong></p>
          <p><span>Nomor rekening</span><strong>{{ config('toko.bank_rekening') }}</strong></p>
          <p><span>Atas nama</span><strong>{{ config('toko.bank_atas_nama') }}</strong></p>
        </div>
        @elseif ($pesanan->metode_bayar === 'E-Wallet')
        <p>Silakan kirim pembayaran sebesar <strong>{{ rupiah($pesanan->total) }}</strong> ke akun e-wallet berikut.</p>
        <div class="rekening">
          <p><span>Layanan</span><strong>{{ config('toko.ewallet_nama') }}</strong></p>
          <p><span>Nomor</span><strong>{{ config('toko.ewallet_nomor') }}</strong></p>
          <p><span>Atas nama</span><strong>{{ config('toko.ewallet_atas_nama') }}</strong></p>
        </div>
        @else
        <p>Pesananmu menggunakan metode bayar di tempat. Siapkan uang tunai sebesar <strong>{{ rupiah($pesanan->total) }}</strong> saat kurir tiba.</p>
        <p>Kurir akan menghubungi nomor {{ $pesanan->telepon }} sebelum melakukan pengiriman. Status pesanan saat ini sudah masuk proses.</p>
        @endif

        @if ($metodeNonCod)
        @if (!empty($pesanan->batas_bayar) && $pesanan->status === 'Menunggu Pembayaran')
        <p class="batas-bayar">Batas upload bukti pembayaran: <strong>{{ tanggal_wib($pesanan->batas_bayar) }}</strong></p>
        @endif

        @if (!empty($pesanan->bukti_bayar))
        <div class="bukti-terunggah">
          <h3>Bukti pembayaran sudah diunggah</h3>
          <p>@if (!empty($pesanan->dibayar_pada))Diunggah pada {{ tanggal_wib($pesanan->dibayar_pada) }}. @endif{{ $pesanStatusBukti }}</p>
          <a href="{{ asset('uploads/bukti/' . $pesanan->bukti_bayar) }}" target="_blank" rel="noopener">
            <img src="{{ asset('uploads/bukti/' . $pesanan->bukti_bayar) }}" alt="Bukti pembayaran {{ $pesanan->kode }}" width="220" height="293">
          </a>
        </div>
        @elseif ($pesanan->status === 'Menunggu Pembayaran')
        @if ($lewatBatas)
        <div class="kotak-galat">
          <p>Batas waktu upload bukti pembayaran sudah lewat. Silakan hubungi kami melalui halaman kontak dengan menyertakan kode pesanan.</p>
        </div>
        @else
        <div class="form-upload">
          <h3>Upload bukti pembayaran</h3>
          <p>Unggah foto atau tangkapan layar bukti transfer. Format JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.</p>
          <form action="{{ route('pesanan.upload') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="kode" value="{{ $pesanan->kode }}">
            <div class="kolom-form">
              <label for="bukti">File bukti pembayaran</label>
              <input type="file" id="bukti" name="bukti" accept="image/jpeg,image/png,image/webp" required>
            </div>
            <button type="submit" class="tombol">Kirim Bukti Pembayaran</button>
          </form>
        </div>
        @endif
        @endif
        @endif
      </div>

      <div class="kotak-info">
        <h2>Rincian pesanan</h2>
        <ul class="daftar-pesanan">
          @foreach ($pesanan->items as $item)
          <li>
            <span>{{ $item->nama_produk }} ukuran {{ $item->ukuran }} x {{ (int) $item->jumlah }}</span>
            <strong>{{ rupiah($item->subtotal) }}</strong>
          </li>
          @endforeach
        </ul>
        <div class="ringkasan-baris">
          <span>Subtotal</span>
          <strong>{{ rupiah($pesanan->subtotal) }}</strong>
        </div>
        <div class="ringkasan-baris">
          <span>Ongkir</span>
          <strong>{{ (int) $pesanan->ongkir === 0 ? 'Gratis' : rupiah($pesanan->ongkir) }}</strong>
        </div>
        <div class="ringkasan-total">
          <span>Total</span>
          <strong>{{ rupiah($pesanan->total) }}</strong>
        </div>
      </div>
    </div>

    <aside class="ringkasan">
      <h2>Data Pengiriman</h2>
      <p><strong>{{ $pesanan->nama }}</strong></p>
      <p>{{ $pesanan->telepon }}<br>{{ $pesanan->email }}</p>
      <p>{!! nl2br(e($pesanan->alamat)) !!}<br>{{ $pesanan->kota }}, {{ $pesanan->provinsi }} {{ $pesanan->kode_pos }}</p>
      <p><span>Metode bayar</span><br><strong>{{ $pesanan->metode_bayar }}</strong></p>
      <p><span>Status</span><br><strong>{{ $pesanan->status }}</strong></p>
      <p><span>Tanggal pesanan</span><br>{{ tanggal_wib($pesanan->dibuat_pada) }}</p>
      <a class="tombol tombol-lebar" href="{{ route('lacak', ['kode' => $pesanan->kode]) }}">Lacak Pesanan</a>
      <a class="tombol tombol-lebar tombol-garis tombol-jarak" href="{{ route('pesanan.saya') }}">Pesanan Saya</a>
      <p class="ringkasan-catatan">Kode pesanan juga tersimpan otomatis di perangkat ini melalui halaman Pesanan Saya.</p>
    </aside>
  </div>
</section>
@endif
@endsection
