@php
    $judul = $judul ?? config('toko.nama');
    $deskripsi = $deskripsi ?? config('toko.deskripsi');
    $kanonikal = $kanonikal ?? url('/');
    $ogGambar = $og_gambar ?? asset('images/produk-06.jpg');
    $halaman = $halaman ?? '';
    $robots = $robots ?? 'index, follow';
    $flash = session('flash');
    $isAdmin = (bool) session('admin');
    $jumlahKeranjang = app(\App\Services\CartService::class)->count();

    $skemaToko = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'OnlineStore',
        'name' => config('toko.nama'),
        'url' => url('/'),
        'logo' => asset('assets/img/logo.svg'),
        'description' => config('toko.deskripsi'),
        'email' => config('toko.email'),
        'telephone' => config('toko.telepon'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => config('toko.alamat'),
            'addressCountry' => 'ID',
        ],
        'sameAs' => [
            'https://www.instagram.com/rerajut.thrift',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $judul }}</title>
<meta name="description" content="{{ $deskripsi }}">
<meta name="keywords" content="thrift baju, baju thrift, thrift online, pakaian bekas berkualitas, thrift yogyakarta, rerajut thrift">
<meta name="author" content="{{ config('toko.nama') }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $kanonikal }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ config('toko.nama') }}">
<meta property="og:title" content="{{ $judul }}">
<meta property="og:description" content="{{ $deskripsi }}">
<meta property="og:url" content="{{ $kanonikal }}">
<meta property="og:image" content="{{ $ogGambar }}">
<meta property="og:locale" content="id_ID">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $judul }}">
<meta name="twitter:description" content="{{ $deskripsi }}">
<meta name="twitter:image" content="{{ $ogGambar }}">
<link rel="icon" href="{{ asset('assets/img/favicon.svg') }}" type="image/svg+xml">
<link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
<script type="application/ld+json">{!! $skemaToko !!}</script>
@stack('head')
</head>
<body>
<a class="lewati" href="#konten">Lewati ke konten</a>
<header class="situs-header">
  <div class="wadah header-dalam">
    <a class="logo" href="{{ route('home') }}">
      <img src="{{ asset('assets/img/logo.svg') }}" alt="Logo {{ config('toko.nama') }}" width="170" height="48">
    </a>
    <button class="tombol-menu" type="button" aria-label="Buka menu" aria-expanded="false" data-menu>
      <span></span><span></span><span></span>
    </button>
    <nav class="nav-utama" data-nav>
      @if ($isAdmin)
      <span class="admin-pengguna">Halo, {{ session('admin_username', 'admin') }}</span>
      <a href="{{ route('admin.orders.index') }}" class="{{ $halaman === 'admin-pesanan' ? 'aktif' : '' }}">Pesanan</a>
      <a href="{{ route('admin.products.index') }}" class="{{ $halaman === 'admin-produk' ? 'aktif' : '' }}">Produk</a>
      <a href="{{ route('admin.categories.index') }}" class="{{ $halaman === 'admin-kategori' ? 'aktif' : '' }}">Kategori</a>
      <a href="{{ route('admin.messages.index') }}" class="{{ $halaman === 'admin-pesan' ? 'aktif' : '' }}">Pesan</a>
      <a href="{{ route('admin.account.show') }}" class="{{ $halaman === 'admin-akun' ? 'aktif' : '' }}">Akun</a>
      <a href="{{ route('home') }}">Lihat Toko</a>
      <form action="{{ route('admin.logout') }}" method="post" class="form-keluar-admin">
        @csrf
        <button type="submit" class="tautan-keluar-admin">Keluar</button>
      </form>
      @else
      <a href="{{ route('home') }}" class="{{ $halaman === 'beranda' ? 'aktif' : '' }}">Beranda</a>
      <a href="{{ route('produk.index') }}" class="{{ $halaman === 'produk' ? 'aktif' : '' }}">Produk</a>
      <a href="{{ route('tentang') }}" class="{{ $halaman === 'tentang' ? 'aktif' : '' }}">Tentang</a>
      <a href="{{ route('lacak') }}" class="{{ $halaman === 'lacak' ? 'aktif' : '' }}">Lacak Pesanan</a>
      <a class="tautan-pesanan-saya {{ $halaman === 'pesanan-saya' ? 'aktif' : '' }}" href="{{ route('pesanan.saya') }}" hidden>Pesanan Saya</a>
      <a href="{{ route('kontak.show') }}" class="{{ $halaman === 'kontak' ? 'aktif' : '' }}">Kontak</a>
      <a class="tautan-keranjang" href="{{ route('keranjang.index') }}">Keranjang <span class="lencana">{{ $jumlahKeranjang }}</span></a>
      @endif
    </nav>
  </div>
</header>
@if (!empty($flash) && is_array($flash))
<div class="flash flash-{{ $flash['tipe'] ?? 'sukses' }}" role="status">
  <div class="wadah">{{ $flash['pesan'] ?? '' }}</div>
</div>
@endif
<main id="konten">
@yield('content')
</main>
<footer class="situs-footer">
  <div class="wadah footer-atas">
    <div class="footer-kolom footer-merek">
      <img src="{{ asset('assets/img/logo-putih.svg') }}" alt="Logo {{ config('toko.nama') }}" width="170" height="48">
      <p>Baju thrift pilihan yang sudah dicek kualitasnya. Stok satuan, jadi gaya kamu tidak akan sama dengan orang lain.</p>
      <p class="footer-kontak">{{ config('toko.alamat') }}<br>{{ config('toko.telepon') }}<br>{{ config('toko.email') }}</p>
    </div>
    <div class="footer-kolom">
      <h2>Jelajahi</h2>
      <a href="{{ route('produk.index') }}">Semua Produk</a>
      <a href="{{ route('produk.index', ['kategori' => 'rajutan']) }}">Rajutan</a>
      <a href="{{ route('produk.index', ['kategori' => 'kemeja']) }}">Kemeja</a>
      <a href="{{ route('produk.index', ['kategori' => 'aksesoris']) }}">Aksesoris</a>
    </div>
    <div class="footer-kolom">
      <h2>Bantuan</h2>
      <a href="{{ route('tentang') }}">Tentang Kami</a>
      <a href="{{ route('lacak') }}">Lacak Pesanan</a>
      <a href="{{ route('kontak.show') }}">Hubungi Kami</a>
      <a href="{{ route('keranjang.index') }}">Keranjang Belanja</a>
    </div>
    <div class="footer-kolom footer-newsletter">
      <h2>Info Drop Terbaru</h2>
      <p>Daftar email untuk mendapat kabar produk baru dan promo mingguan.</p>
      <form action="{{ route('langganan.store') }}" method="post" class="form-langganan">
        @csrf
        <label class="sr-only" for="email-langganan">Email</label>
        <input type="email" id="email-langganan" name="email" placeholder="Email kamu" required>
        <button type="submit" class="tombol tombol-terang">Daftar</button>
      </form>
    </div>
  </div>
  <div class="wadah footer-bawah">
    <p>Hak cipta {{ date('Y') }} {{ config('toko.nama') }}. Seluruh hak cipta dilindungi.</p>
    <p>Belanja hemat, bumi lebih ringan. <a class="tautan-admin" href="{{ route('admin.login') }}">Admin Toko</a></p>
  </div>
</footer>
<script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>
