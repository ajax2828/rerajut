@extends('layouts.app')

@section('content')
<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Detail pesanan</p>
        <h1>{{ $pesanan->kode }}</h1>
      </div>
      <a class="tautan-lain" href="{{ route('admin.orders.index') }}">Kembali ke daftar</a>
    </div>

    <div class="admin-detail-grid">
      <div>
        <div class="admin-panel">
          <h2>Produk Dipesan</h2>
          <div class="tabel-gulir">
            <table class="tabel-admin">
              <thead>
                <tr><th>Produk</th><th>Ukuran</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr>
              </thead>
              <tbody>
                @foreach ($pesanan->items as $item)
                <tr>
                  <td>{{ $item->nama_produk }}</td>
                  <td>{{ $item->ukuran }}</td>
                  <td>{{ rupiah($item->harga) }}</td>
                  <td>{{ (int) $item->jumlah }}</td>
                  <td>{{ rupiah($item->subtotal) }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
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

        <div class="admin-panel">
          <h2>Data Pembeli</h2>
          <dl class="admin-dl">
            <div><dt>Nama</dt><dd>{{ $pesanan->nama }}</dd></div>
            <div><dt>Email</dt><dd>{{ $pesanan->email }}</dd></div>
            <div><dt>Telepon</dt><dd>{{ $pesanan->telepon }}</dd></div>
            <div><dt>Alamat</dt><dd>{!! nl2br(e($pesanan->alamat)) !!}</dd></div>
            <div><dt>Kota</dt><dd>{{ $pesanan->kota }}</dd></div>
            <div><dt>Provinsi</dt><dd>{{ $pesanan->provinsi }}</dd></div>
            <div><dt>Kode pos</dt><dd>{{ $pesanan->kode_pos }}</dd></div>
            <div><dt>Metode bayar</dt><dd>{{ $pesanan->metode_bayar }}</dd></div>
            <div><dt>Catatan</dt><dd>{!! trim((string) ($pesanan->catatan ?? '')) !== '' ? nl2br(e($pesanan->catatan)) : 'Tidak ada catatan' !!}</dd></div>
            <div><dt>Tanggal</dt><dd>{{ tanggal_wib($pesanan->dibuat_pada) }}</dd></div>
          </dl>
        </div>

        <div class="admin-panel">
          <h2>Pembayaran</h2>
          <dl class="admin-dl">
            <div><dt>Metode</dt><dd>{{ $pesanan->metode_bayar }}</dd></div>
            <div><dt>Status</dt><dd>{{ $pesanan->status }}</dd></div>
            @if (!empty($pesanan->batas_bayar))
            <div><dt>Batas bayar</dt><dd>{{ tanggal_wib($pesanan->batas_bayar) }}</dd></div>
            @endif
            @if (!empty($pesanan->dibayar_pada))
            <div><dt>Diunggah pada</dt><dd>{{ tanggal_wib($pesanan->dibayar_pada) }}</dd></div>
            @endif
            <div>
              <dt>Bukti bayar</dt>
              <dd>
                @if (!empty($pesanan->bukti_bayar))
                <a class="tautan-bukti" href="{{ asset('uploads/bukti/' . $pesanan->bukti_bayar) }}" target="_blank" rel="noopener">
                  <img src="{{ asset('uploads/bukti/' . $pesanan->bukti_bayar) }}" alt="Bukti pembayaran {{ $pesanan->kode }}" width="180" height="240">
                </a>
                @if ($pesanan->status !== 'Selesai' && $pesanan->status !== 'Dibatalkan')
                <form action="{{ route('admin.orders.reject_proof', $pesanan->kode) }}" method="post" class="tombol-jarak" data-konfirmasi="Tolak dan hapus bukti pembayaran ini agar pembeli bisa mengunggah ulang?">
                  @csrf
                  <button type="submit" class="tombol tombol-kecil tombol-hapus">Tolak Bukti Pembayaran</button>
                </form>
                @endif
                @else
                Belum ada bukti pembayaran
                @endif
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <aside class="ringkasan">
        <h2>Ubah Status</h2>
        <p>Status saat ini: <strong>{{ $pesanan->status }}</strong></p>
        <form action="{{ route('admin.orders.status', $pesanan->id) }}" method="post">
          @csrf
          <div class="kolom-form">
            <label for="status">Status pesanan</label>
            <select id="status" name="status">
              @foreach ($statusList as $status)
              <option value="{{ $status }}" {{ $pesanan->status === $status ? 'selected' : '' }}>{{ $status }}</option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="tombol tombol-lebar">Perbarui Status</button>
        </form>
        <p class="ringkasan-catatan">Membatalkan pesanan akan mengembalikan stok produk secara otomatis.</p>
      </aside>
    </div>
  </div>
</section>
@endsection
