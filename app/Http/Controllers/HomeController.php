<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Services\OrderService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(OrderService $orders): View
    {
        $orders->cancelExpiredOrders();

        $unggulan = Produk::with('kategori')
            ->where('unggulan', 1)
            ->where('stok', '>', 0)
            ->orderBy('id')
            ->limit(8)
            ->get();

        $terbaru = Produk::with('kategori')
            ->where('stok', '>', 0)
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $kategori = Kategori::withCount('produk')->orderBy('nama')->get();
        $jumlahSiapKirim = Produk::where('stok', '>', 0)->count();
        $hargaTermurah = (int) (Produk::where('stok', '>', 0)->min('harga') ?? 45000);
        if ($hargaTermurah < 1) {
            $hargaTermurah = 45000;
        }

        $heroGambar = 'produk-13.jpg';
        if (!is_file(public_path('images/' . $heroGambar))) {
            if ($unggulan->isNotEmpty()) {
                $heroGambar = $unggulan->first()->gambar;
            } elseif ($terbaru->isNotEmpty()) {
                $heroGambar = $terbaru->first()->gambar;
            }
        }

        return view('home', [
            'unggulan' => $unggulan,
            'terbaru' => $terbaru,
            'kategori' => $kategori,
            'jumlahSiapKirim' => $jumlahSiapKirim,
            'hargaTermurah' => $hargaTermurah,
            'heroGambar' => $heroGambar,
            'judul' => config('toko.nama') . ' | Baju Thrift Pilihan Harga Mahasiswa',
            'deskripsi' => 'Toko baju thrift online dengan produk terkurasi, stok satuan, dan harga mulai ' . rupiah($hargaTermurah) . '. Temukan rajutan, kemeja, outer, dan aksesoris thrift berkualitas di ' . config('toko.nama') . '.',
            'kanonikal' => url('/'),
            'halaman' => 'beranda',
        ]);
    }

    public function tentang(): View
    {
        return view('tentang', [
            'judul' => 'Tentang ' . config('toko.nama') . ' | Toko Baju Thrift Terkurasi',
            'deskripsi' => 'Kenali ' . config('toko.nama') . ', toko baju thrift online yang mengurasi pakaian layak pakai, mencuci setiap produk, dan menjual dengan harga ramah mahasiswa.',
            'kanonikal' => route('tentang'),
            'halaman' => 'tentang',
        ]);
    }
}
