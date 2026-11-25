@extends('layouts.app')

@section('content')
<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Panel pengelola</p>
        <h1>Dashboard Pesanan</h1>
      </div>
      <a class="tombol tombol-kecil" href="{{ route('admin.products.index') }}">Kelola Produk</a>
    </div>

    <div class="admin-statistik">
      <div><strong>{{ $totalPesanan }}</strong><span>Total pesanan</span></div>
      <div><strong>{{ $menunggu }}</strong><span>Menunggu pembayaran</span></div>
      <div><strong>{{ $diproses }}</strong><span>Sedang diproses</span></div>
      <div><strong>{{ rupiah($omzet) }}</strong><span>Omzet terkonfirmasi</span></div>
      <div><strong>{{ $pesanBaru }}</strong><span>Pesan pelanggan</span></div>
    </div>

    <div class="admin-panel">
      <h2>Daftar Pesanan</h2>
      <form action="{{ route('admin.orders.index') }}" method="get" class="admin-filter">
        <input type="search" name="q" value="{{ $cari }}" placeholder="Cari kode, nama, email, atau telepon">
        <select name="status" aria-label="Filter status pesanan">
          <option value="">Semua status</option>
          @foreach ($statusList as $st)
          <option value="{{ $st }}" {{ $filterStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
          @endforeach
        </select>
        <button type="submit" class="tombol tombol-kecil">Filter</button>
        @if ($cari !== '' || $filterStatus !== '')
        <a class="tautan-lain" href="{{ route('admin.orders.index') }}">Reset</a>
        @endif
      </form>

      @if ($pesananList->isEmpty())
      <p>{{ ($cari !== '' || $filterStatus !== '') ? 'Tidak ada pesanan yang cocok dengan filter.' : 'Belum ada pesanan masuk. Pesanan akan muncul setelah pembeli menyelesaikan checkout.' }}</p>
      @else
      <div class="tabel-gulir">
        <table class="tabel-admin">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Pembeli</th>
              <th>Tanggal</th>
              <th>Metode</th>
              <th>Total</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($pesananList as $row)
            <tr>
              <td><a href="{{ route('admin.orders.show', $row->kode) }}">{{ $row->kode }}</a></td>
              <td>{{ $row->nama }}<br><small>{{ $row->kota }}</small></td>
              <td>{{ tanggal_wib($row->dibuat_pada, false) }}</td>
              <td>
                {{ $row->metode_bayar }}
                @if (!empty($row->bukti_bayar))
                <br><span class="bukti-lencana">Bukti ada</span>
                @endif
              </td>
              <td>{{ rupiah($row->total) }}</td>
              <td><span class="status-lencana">{{ $row->status }}</span></td>
              <td>
                <form action="{{ route('admin.orders.status', $row->id) }}" method="post" class="form-status">
                  @csrf
                  <select name="status" aria-label="Ubah status {{ $row->kode }}">
                    @foreach ($statusList as $status)
                    <option value="{{ $status }}" {{ $row->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                  </select>
                  <button type="submit" class="tombol tombol-kecil">Simpan</button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if ($totalHal > 1)
      <nav class="paginasi" aria-label="Navigasi halaman pesanan">
        @for ($i = 1; $i <= $totalHal; $i++)
        @php
            $query = ['hal' => $i];
            if ($filterStatus !== '') {
                $query['status'] = $filterStatus;
            }
            if ($cari !== '') {
                $query['q'] = $cari;
            }
        @endphp
        <a class="{{ $i === $hal ? 'aktif' : '' }}" href="{{ route('admin.orders.index', $query) }}">{{ $i }}</a>
        @endfor
      </nav>
      @endif
      @endif
    </div>
  </div>
</section>
@endsection
