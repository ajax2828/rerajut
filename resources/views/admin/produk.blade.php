@extends('layouts.app')

@section('content')
<section class="bagian">
  <div class="wadah">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Panel pengelola</p>
        <h1>Kelola Produk</h1>
      </div>
      <a class="tombol tombol-kecil" href="{{ route('admin.products.create') }}">Tambah Produk</a>
    </div>

    <div class="admin-panel">
      <form action="{{ route('admin.products.index') }}" method="get" class="admin-filter">
        <input type="search" name="q" value="{{ $cari }}" placeholder="Cari nama atau ukuran produk">
        <select name="kategori" aria-label="Filter kategori">
          <option value="0">Semua kategori</option>
          @foreach ($kategoriList as $kat)
          <option value="{{ (int) $kat->id }}" {{ $filterKategori === (int) $kat->id ? 'selected' : '' }}>{{ $kat->nama }} ({{ (int) $kat->produk_count }})</option>
          @endforeach
        </select>
        <select name="stok" aria-label="Filter stok">
          <option value="">Semua stok</option>
          <option value="tersedia" {{ $filterStok === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
          <option value="habis" {{ $filterStok === 'habis' ? 'selected' : '' }}>Terjual / Habis</option>
        </select>
        <button type="submit" class="tombol tombol-kecil">Filter</button>
        @if ($cari !== '' || $filterKategori > 0 || $filterStok !== '')
        <a class="tautan-lain" href="{{ route('admin.products.index') }}">Reset</a>
        @endif
      </form>

      @if ($produkList->isEmpty())
      <p>{{ ($cari !== '' || $filterKategori > 0 || $filterStok !== '') ? 'Tidak ada produk yang cocok dengan filter.' : 'Belum ada produk. Tambahkan produk pertama melalui tombol tambah produk.' }}</p>
      @else
      <div class="tabel-gulir">
        <table class="tabel-admin">
          <thead>
            <tr>
              <th>Gambar</th>
              <th>Nama Produk</th>
              <th>Kategori</th>
              <th>Harga</th>
              <th>Stok</th>
              <th>Unggulan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($produkList as $produk)
            <tr>
              <td><img class="tabel-thumb" src="{{ asset('images/' . $produk->gambar) }}" alt="{{ $produk->nama }}" width="60" height="80"></td>
              <td>
                <strong>{{ $produk->nama }}</strong><br>
                <small>Ukuran {{ $produk->ukuran }} / {{ $produk->kondisi }}</small>
              </td>
              <td>{{ $produk->kategori?->nama ?? '-' }}</td>
              <td>{{ rupiah($produk->harga) }}</td>
              <td>{{ (int) $produk->stok }}</td>
              <td>{{ $produk->unggulan ? 'Ya' : 'Tidak' }}</td>
              <td>
                <div class="aksi-tabel">
                  <a class="tombol tombol-kecil tombol-garis" href="{{ route('admin.products.edit', $produk->id) }}">Ubah</a>
                  <form action="{{ route('admin.products.destroy', $produk->id) }}" method="post" data-konfirmasi="Hapus produk {{ $produk->nama }}?">
                    @csrf
                    <button type="submit" class="tombol tombol-kecil tombol-hapus">Hapus</button>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if ($totalHal > 1)
      <nav class="paginasi" aria-label="Navigasi halaman produk">
        @for ($i = 1; $i <= $totalHal; $i++)
        @php
            $query = ['hal' => $i];
            if ($cari !== '') {
                $query['q'] = $cari;
            }
            if ($filterKategori > 0) {
                $query['kategori'] = $filterKategori;
            }
            if ($filterStok !== '') {
                $query['stok'] = $filterStok;
            }
        @endphp
        <a class="{{ $i === $hal ? 'aktif' : '' }}" href="{{ route('admin.products.index', $query) }}">{{ $i }}</a>
        @endfor
      </nav>
      @endif
      @endif
    </div>
  </div>
</section>
@endsection
