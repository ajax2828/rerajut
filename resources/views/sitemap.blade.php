{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($halamanStatis as $hal)
<url>
<loc>{{ $hal['lokasi'] }}</loc>
<lastmod>{{ $hal['ubah'] }}</lastmod>
<changefreq>weekly</changefreq>
<priority>{{ $hal['prioritas'] }}</priority>
</url>
@endforeach
@foreach ($produkList as $p)
@php
    $ts = !empty($p->dibuat_pada) ? strtotime((string) $p->dibuat_pada) : false;
    $lastmod = $ts !== false ? date('Y-m-d', $ts) : $hariIni;
@endphp
<url>
<loc>{{ route('produk.show', $p->slug) }}</loc>
<lastmod>{{ $lastmod }}</lastmod>
<changefreq>weekly</changefreq>
<priority>0.8</priority>
</url>
@endforeach
</urlset>
