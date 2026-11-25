<article class="kartu-produk">
  <a class="kartu-gambar" href="{{ route('produk.show', $produk->slug) }}">
    <img src="{{ asset('images/' . $produk->gambar) }}" alt="{{ $produk->nama }} ukuran {{ $produk->ukuran }} kondisi {{ $produk->kondisi }}" loading="lazy" width="600" height="800">
    @if ((int) $produk->stok < 1)
    <span class="label-habis">Terjual</span>
    @endif
  </a>
  <div class="kartu-isi">
    <p class="kartu-kategori">{{ $produk->kategori?->nama ?? 'Thrift' }} / {{ $produk->kondisi }}</p>
    <h3><a href="{{ route('produk.show', $produk->slug) }}">{{ $produk->nama }}</a></h3>
    <p class="kartu-harga">{{ rupiah($produk->harga) }}@if ((int) $produk->harga_asli > (int) $produk->harga) <span>{{ rupiah($produk->harga_asli) }}</span>@endif</p>
    <p class="kartu-ukuran">Ukuran {{ $produk->ukuran }}</p>
    <a class="tombol tombol-kecil" href="{{ route('produk.show', $produk->slug) }}">Lihat Produk</a>
  </div>
</article>
