<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, OrderService $orders): View
    {
        $orders->cancelExpiredOrders();

        $kategoriSlug = slug_aman((string) $request->query('kategori', ''));
        $cari = trim((string) $request->query('q', ''));
        $urut = (string) $request->query('urut', 'terbaru');
        if (!in_array($urut, ['terbaru', 'termurah', 'termahal', 'nama'], true)) {
            $urut = 'terbaru';
        }

        $query = Produk::with('kategori');
        if ($kategoriSlug !== '') {
            $query->whereHas('kategori', fn ($q) => $q->where('slug', $kategoriSlug));
        }
        if ($cari !== '') {
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', '%' . $cari . '%')
                    ->orWhere('deskripsi', 'like', '%' . $cari . '%');
            });
        }

        $perHal = 12;
        $total = (clone $query)->count();
        $totalHal = max(1, (int) ceil($total / $perHal));
        $hal = min($totalHal, max(1, (int) $request->query('hal', 1)));
        $offset = ($hal - 1) * $perHal;

        $query->orderByRaw('(stok > 0) DESC');
        if ($urut === 'termurah') {
            $query->orderBy('harga')->orderByDesc('id');
        } elseif ($urut === 'termahal') {
            $query->orderByDesc('harga')->orderByDesc('id');
        } elseif ($urut === 'nama') {
            $query->orderBy('nama');
        } else {
            $query->orderByDesc('dibuat_pada')->orderByDesc('id');
        }

        $produkList = $query->offset($offset)->limit($perHal)->get();
        $kategoriList = Kategori::withCount('produk')->orderBy('nama')->get();
        $kategoriAktif = $kategoriList->firstWhere('slug', $kategoriSlug);

        $judul = $kategoriAktif
            ? 'Produk ' . $kategoriAktif->nama . ' Thrift | ' . config('toko.nama')
            : 'Katalog Baju Thrift | ' . config('toko.nama');
        if ($cari !== '') {
            $judul = 'Hasil Pencarian ' . $cari . ' | ' . config('toko.nama');
        }

        return view('produk.index', [
            'produkList' => $produkList,
            'kategoriList' => $kategoriList,
            'kategoriAktif' => $kategoriAktif,
            'kategoriSlug' => $kategoriSlug,
            'cari' => $cari,
            'urut' => $urut,
            'hal' => $hal,
            'totalHal' => $totalHal,
            'total' => $total,
            'judul' => $judul,
            'deskripsi' => 'Lihat koleksi baju thrift pilihan ' . config('toko.nama') . '. Stok satuan, kondisi terkurasi, dan pengiriman ke seluruh Indonesia.',
            'kanonikal' => $kategoriAktif ? route('produk.index', ['kategori' => $kategoriAktif->slug]) : route('produk.index'),
            'halaman' => 'produk',
        ]);
    }

    public function show(string $slug, OrderService $orders): View|Response
    {
        $orders->cancelExpiredOrders();

        $cleanSlug = slug_aman($slug);
        $produk = $cleanSlug !== '' ? Produk::with('kategori')->where('slug', $cleanSlug)->first() : null;

        if (!$produk) {
            return response()->view('produk.not-found', [
                'judul' => 'Produk tidak ditemukan | ' . config('toko.nama'),
                'deskripsi' => 'Produk yang kamu cari tidak tersedia atau sudah terjual.',
                'kanonikal' => route('produk.index'),
                'halaman' => 'produk',
                'robots' => 'noindex, follow',
            ], 404);
        }

        $terkait = Produk::with('kategori')
            ->where('kategori_id', $produk->kategori_id)
            ->where('id', '<>', $produk->id)
            ->where('stok', '>', 0)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $habis = (int) $produk->stok < 1;
        $diskon = ((int) $produk->harga_asli > 0 && (int) $produk->harga_asli > (int) $produk->harga)
            ? (int) round(100 - ($produk->harga / $produk->harga_asli * 100))
            : 0;
        $gambarUrl = asset('images/' . $produk->gambar);
        $kanonikal = route('produk.show', $produk->slug);

        $skemaProduk = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $produk->nama,
            'image' => [$gambarUrl],
            'description' => $produk->deskripsi,
            'sku' => 'RJT-' . str_pad((string) $produk->id, 4, '0', STR_PAD_LEFT),
            'brand' => ['@type' => 'Brand', 'name' => config('toko.nama')],
            'itemCondition' => 'https://schema.org/UsedCondition',
            'offers' => [
                '@type' => 'Offer',
                'url' => $kanonikal,
                'priceCurrency' => 'IDR',
                'price' => $produk->harga,
                'availability' => $habis ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/UsedCondition',
                'seller' => ['@type' => 'Organization', 'name' => config('toko.nama')],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

        $skemaRemah = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Produk', 'item' => route('produk.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $produk->kategori?->nama ?? 'Produk', 'item' => route('produk.index', ['kategori' => $produk->kategori?->slug ?? ''])],
                ['@type' => 'ListItem', 'position' => 4, 'name' => $produk->nama, 'item' => $kanonikal],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

        return view('produk.show', [
            'produk' => $produk,
            'terkait' => $terkait,
            'habis' => $habis,
            'diskon' => $diskon,
            'skemaProduk' => $skemaProduk,
            'skemaRemah' => $skemaRemah,
            'judul' => $produk->nama . ' Ukuran ' . $produk->ukuran . ' | ' . config('toko.nama'),
            'deskripsi' => mb_substr(strip_tags((string) $produk->deskripsi), 0, 150),
            'kanonikal' => $kanonikal,
            'og_gambar' => $gambarUrl,
            'halaman' => 'produk',
        ]);
    }
}
