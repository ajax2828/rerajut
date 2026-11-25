@extends('layouts.app')

@section('content')
<section class="kepala-halaman">
  <div class="wadah">
    <nav class="remah" aria-label="Breadcrumb">
      <a href="{{ route('home') }}">Beranda</a>
      <span>/</span>
      <span>Lacak Pesanan</span>
    </nav>
    <h1>Lacak Pesanan</h1>
    <p>Masukkan kode pesanan yang kamu terima setelah checkout.</p>
  </div>
</section>

<section class="bagian">
  <div class="wadah wadah-sempit">
    <form action="{{ route('lacak') }}" method="get" class="form-lacak">
      <label for="kode">Kode pesanan</label>
      <div class="form-lacak-baris">
        <input type="text" id="kode" name="kode" value="{{ $kode }}" placeholder="Contoh: RJT-20261119-ABCDE" required>
        <button type="submit" class="tombol">Cek Status</button>
      </div>
    </form>

    @if ($sudahCari && !$pesanan)
    <div class="kotak-galat">
      <h2>Pesanan tidak ditemukan</h2>
      <p>Pastikan kode pesanan ditulis dengan benar. Kode terdiri dari huruf dan angka tanpa spasi.</p>
    </div>
    @endif

    @if ($pesanan)
    <div class="lacak-hasil">
      <div class="lacak-kepala">
        <div>
          <p class="label-kecil">Kode pesanan</p>
          <h2>{{ $pesanan->kode }}</h2>
        </div>
        <span class="status-lencana">{{ $pesanan->status }}</span>
      </div>
      @if ($pesanan->status === 'Dibatalkan')
      <div class="kotak-galat">
        <p>Pesanan ini telah dibatalkan. Jika ada pertanyaan, hubungi kami melalui halaman kontak dengan menyertakan kode pesanan.</p>
      </div>
      @else
      <ol class="linimasa">
        @foreach ($urutanStatus as $i => $status)
        <li class="{{ $i <= $indeksStatus ? 'selesai' : '' }}">
          <span class="titik"></span>
          <div>
            <strong>{{ $status }}</strong>
            @if ($i === 0)
            <small>Pesanan dibuat pada {{ tanggal_wib($pesanan->dibuat_pada) }}</small>
            @endif
          </div>
        </li>
        @endforeach
      </ol>
      @endif
      @if ($pesanan->metode_bayar === 'COD')
      <p class="checkout-catatan">Pembayaran dilakukan secara tunai saat pesanan diterima.</p>
      @endif

      <div class="lacak-grid">
        <div>
          <h3>Produk yang dipesan</h3>
          <ul class="daftar-pesanan">
            @foreach ($pesanan->items as $item)
            <li>
              <span>{{ $item->nama_produk }} ukuran {{ $item->ukuran }} x {{ (int) $item->jumlah }}</span>
              <strong>{{ rupiah($item->subtotal) }}</strong>
            </li>
            @endforeach
          </ul>
          <div class="ringkasan-baris">
            <span>Ongkir</span>
            <strong>{{ (int) $pesanan->ongkir === 0 ? 'Gratis' : rupiah($pesanan->ongkir) }}</strong>
          </div>
          <div class="ringkasan-total">
            <span>Total</span>
            <strong>{{ rupiah($pesanan->total) }}</strong>
          </div>
        </div>
        <div>
          <h3>Alamat pengiriman</h3>
          <p><strong>{{ $pesanan->nama }}</strong><br>{{ $pesanan->telepon }}</p>
          <p>{!! nl2br(e($pesanan->alamat)) !!}<br>{{ $pesanan->kota }}, {{ $pesanan->provinsi }} {{ $pesanan->kode_pos }}</p>
          <p>Metode bayar: <strong>{{ $pesanan->metode_bayar }}</strong></p>
          @if (!empty($pesanan->batas_bayar) && $pesanan->status === 'Menunggu Pembayaran')
          <p>Batas upload bukti: <strong>{{ tanggal_wib($pesanan->batas_bayar) }}</strong></p>
          @endif
          @if (!empty($pesanan->bukti_bayar))
          <p>Bukti pembayaran: <a class="tautan-lain" href="{{ asset('uploads/bukti/' . $pesanan->bukti_bayar) }}" target="_blank" rel="noopener">Lihat bukti</a></p>
          @elseif ($bisaUpload)
          <p>Bukti pembayaran belum diunggah.</p>
          <a class="tombol tombol-kecil" href="{{ route('pesanan.sukses', ['kode' => $pesanan->kode]) }}#pembayaran">Upload Bukti Pembayaran</a>
          @elseif ($metodeNonCod)
          <p>Bukti pembayaran belum diunggah.</p>
          @endif
        </div>
      </div>
    </div>
    @endif
  </div>
</section>
@endsection
