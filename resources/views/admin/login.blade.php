@extends('layouts.app')

@section('content')
<section class="bagian">
  <div class="wadah wadah-sempit">
    <div class="admin-masuk">
      <h1>Masuk Admin</h1>
      <p>Gunakan akun pengelola untuk memproses pesanan dan mengelola produk.</p>
      @if ($errors->any())
      <div class="kotak-galat">
        <ul>
          @foreach ($errors->all() as $g)
          <li>{{ $g }}</li>
          @endforeach
        </ul>
      </div>
      @endif
      <form action="{{ route('admin.login.post') }}" method="post">
        @csrf
        <div class="kolom-form">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" value="{{ old('username', '') }}" autocomplete="username" required autofocus>
        </div>
        <div class="kolom-form">
          <label for="password">Kata sandi</label>
          <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="tombol tombol-lebar">Masuk</button>
      </form>
    </div>
  </div>
</section>
@endsection
