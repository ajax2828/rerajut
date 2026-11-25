@extends('layouts.app')

@section('content')
<section class="bagian">
  <div class="wadah wadah-sempit">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Panel pengelola</p>
        <h1>Kelola Kategori</h1>
      </div>
      <a class="tautan-lain" href="{{ route('admin.products.index') }}">Lihat Produk</a>
    </div>

    <div class="admin-panel">
      <h2>Tambah Kategori Baru</h2>
      <form action="{{ route('admin.categories.store') }}" method="post" class="admin-filter">
        @csrf
        <input type="text" name="nama" maxlength="80" placeholder="Nama kategori baru, misal: Jaket" required>
        <button type="submit" class="tombol tombol-kecil">Tambah</button>
      </form>
    </div>

    <div class="admin-panel">
      <h2>Daftar Kategori ({{ $kategoriList->count() }})</h2>
      @if ($kategoriList->isEmpty())
      <p>Belum ada kategori.</p>
      @else
      <div class="tabel-gulir">
        <table class="tabel-admin">
          <thead>
            <tr>
              <th>Nama Kategori</th>
              <th>Slug</th>
              <th>Produk</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($kategoriList as $kat)
            <tr>
              <td>
                <form action="{{ route('admin.categories.update', $kat->id) }}" method="post" class="form-status">
                  @csrf
                  <input type="text" name="nama" value="{{ $kat->nama }}" maxlength="80" aria-label="Nama kategori {{ $kat->nama }}" required>
                  <button type="submit" class="tombol tombol-kecil">Simpan</button>
                </form>
              </td>
              <td><code>{{ $kat->slug }}</code></td>
              <td>{{ (int) $kat->produk_count }}</td>
              <td>
                @if ((int) $kat->produk_count > 0)
                <small>Dipakai produk</small>
                @else
                <form action="{{ route('admin.categories.destroy', $kat->id) }}" method="post" data-konfirmasi="Hapus kategori {{ $kat->nama }}?">
                  @csrf
                  <button type="submit" class="tombol tombol-kecil tombol-hapus">Hapus</button>
                </form>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>
</section>
@endsection
