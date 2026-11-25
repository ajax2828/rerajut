@extends('layouts.app')

@section('content')
<section class="bagian">
  <div class="wadah wadah-sempit">
    <div class="judul-bagian">
      <div>
        <p class="label-kecil">Keamanan pengelola</p>
        <h1>Pengaturan Akun Admin</h1>
      </div>
      <a class="tautan-lain" href="{{ route('admin.orders.index') }}">Kembali ke dashboard</a>
    </div>

    @if ($errors->any())
    <div class="kotak-galat">
      <h2>Periksa kembali</h2>
      <ul>
        @foreach ($errors->all() as $g)
        <li>{{ $g }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <div class="admin-panel">
      <h2>Profil Admin</h2>
      <form action="{{ route('admin.account.profile') }}" method="post">
        @csrf
        <div class="kolom-form">
          <label for="username">Username</label>
          <input type="text" id="username" value="{{ $user->username }}" disabled>
        </div>
        <div class="kolom-form">
          <label for="nama">Nama tampilan</label>
          <input type="text" id="nama" name="nama" value="{{ old('nama', $user->nama) }}" maxlength="120" required>
        </div>
        <button type="submit" class="tombol">Simpan Profil</button>
      </form>
    </div>

    <div class="admin-panel">
      <h2>Ganti Kata Sandi</h2>
      <p class="bantuan-form">Disarankan segera mengganti kata sandi bawaan setelah aplikasi dijalankan di server.</p>
      <form action="{{ route('admin.account.password') }}" method="post">
        @csrf
        <div class="kolom-form">
          <label for="sandi_lama">Kata sandi saat ini</label>
          <input type="password" id="sandi_lama" name="sandi_lama" autocomplete="current-password" required>
        </div>
        <div class="kolom-form">
          <label for="sandi_baru">Kata sandi baru (minimal 8 karakter)</label>
          <input type="password" id="sandi_baru" name="sandi_baru" minlength="8" autocomplete="new-password" required>
        </div>
        <div class="kolom-form">
          <label for="konfirmasi_sandi">Ulangi kata sandi baru</label>
          <input type="password" id="konfirmasi_sandi" name="konfirmasi_sandi" minlength="8" autocomplete="new-password" required>
        </div>
        <button type="submit" class="tombol">Perbarui Kata Sandi</button>
      </form>
    </div>
  </div>
</section>
@endsection
