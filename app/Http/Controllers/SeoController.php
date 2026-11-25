<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $produkList = Produk::select('slug', 'dibuat_pada')->orderBy('id')->get();
        $hariIni = date('Y-m-d');

        $halamanStatis = [
            ['lokasi' => url('/'), 'prioritas' => '1.0', 'ubah' => $hariIni],
            ['lokasi' => route('produk.index'), 'prioritas' => '0.9', 'ubah' => $hariIni],
            ['lokasi' => route('tentang'), 'prioritas' => '0.6', 'ubah' => $hariIni],
            ['lokasi' => route('kontak.show'), 'prioritas' => '0.6', 'ubah' => $hariIni],
            ['lokasi' => route('lacak'), 'prioritas' => '0.4', 'ubah' => $hariIni],
        ];

        return response()
            ->view('sitemap', [
                'halamanStatis' => $halamanStatis,
                'produkList' => $produkList,
                'hariIni' => $hariIni,
            ])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function robots(): Response
    {
        $konten = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /keranjang',
            'Disallow: /checkout',
            'Disallow: /pesanan-sukses',
            'Disallow: /lacak',
            'Disallow: /pesanan-saya',
            'Disallow: /api/',
            'Disallow: /upload-bukti',
            'Disallow: /langganan',
            'Disallow: /uploads/',
            'Disallow: /admin/',
            '',
            'Sitemap: ' . route('sitemap'),
            '',
        ]);

        return response($konten, 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
