@extends('layouts.app')

@section('content')
@php
    $kategoriDipilih = (int) old('kategori_id', $produk?->kategori_id ?? ($kategoriList->first()?->id ?? 0));
    $kondisiDipilih = (string) old('kondisi', $produk?->kondisi ?? 'Sangat Baik');
    $unggulanDipilih = (bool) old('unggulan', $produk ? $produk->unggulan : false);
@endphp
<section class="bagian">
  <div class="wadah wadah-sempit">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Panel pengelola</p>
        <h1>{{ $produk ? 'Ubah Produk' : 'Tambah Produk' }}</h1>
      </div>
      <a class="tautan-lain" href="{{ route('admin.products.index') }}">Kembali ke daftar</a>
    </div>

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

    <div class="admin-panel">
      <form action="{{ $produk ? route('admin.products.update', $produk->id) : route('admin.products.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="baris-form">
          <div class="kolom-form">
            <label for="nama">Nama produk</label>
            <input type="text" id="nama" name="nama" value="{{ old('nama', $produk?->nama ?? '') }}" maxlength="150" required>
          </div>
          <div class="kolom-form">
            <label for="kategori_id">Kategori</label>
            <select id="kategori_id" name="kategori_id" required>
              @foreach ($kategoriList as $kategori)
              <option value="{{ (int) $kategori->id }}" {{ $kategoriDipilih === (int) $kategori->id ? 'selected' : '' }}>{{ $kategori->nama }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="kolom-form">
          <label for="deskripsi">Deskripsi</label>
          <textarea id="deskripsi" name="deskripsi" rows="5" required>{{ old('deskripsi', $produk?->deskripsi ?? '') }}</textarea>
        </div>

        <div class="baris-form">
          <div class="kolom-form">
            <label for="harga">Harga jual</label>
            <input type="text" id="harga" name="harga" value="{{ old('harga', $produk?->harga ?? '') }}" inputmode="numeric" required>
          </div>
          <div class="kolom-form">
            <label for="harga_asli">Harga asli</label>
            <input type="text" id="harga_asli" name="harga_asli" value="{{ old('harga_asli', $produk?->harga_asli ?? '') }}" inputmode="numeric" required>
          </div>
          <div class="kolom-form">
            <label for="stok">Stok</label>
            <input type="number" id="stok" name="stok" value="{{ (int) old('stok', $produk?->stok ?? 1) }}" min="0" required>
          </div>
        </div>

        <div class="baris-form">
          <div class="kolom-form">
            <label for="ukuran">Ukuran</label>
            <input type="text" id="ukuran" name="ukuran" value="{{ old('ukuran', $produk?->ukuran ?? '') }}" maxlength="20" placeholder="Contoh: M atau All Size" required>
          </div>
          <div class="kolom-form">
            <label for="kondisi">Kondisi</label>
            <select id="kondisi" name="kondisi" required>
              @foreach ($kondisiOpsi as $kondisi)
              <option value="{{ $kondisi }}" {{ $kondisiDipilih === $kondisi ? 'selected' : '' }}>{{ $kondisi }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="kolom-form">
          <label for="gambar">Gambar produk</label>
          <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp">
          <small class="bantuan-form">Format JPG, PNG, atau WEBP. Maksimal 2 MB. {{ $produk ? 'Biarkan kosong jika tidak ingin mengganti gambar.' : 'Wajib diunggah untuk produk baru.' }}</small>
        </div>

        @if ($produk && !empty($produk->gambar))
        <div class="gambar-pratinjau">
          <img src="{{ asset('images/' . $produk->gambar) }}" alt="{{ $produk->nama }}" width="180" height="240">
          <span>Gambar saat ini</span>
        </div>
        @endif

        <label class="centang-form">
          <input type="checkbox" name="unggulan" value="1" {{ $unggulanDipilih ? 'checked' : '' }}>
          Tampilkan sebagai produk unggulan
        </label>

        <button type="submit" class="tombol tombol-lebar">{{ $produk ? 'Simpan Perubahan' : 'Tambah Produk' }}</button>
      </form>
    </div>
  </div>
</section>
@endsection
