<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartController extends Controller
{
    public function index(CartService $cart, OrderService $orders): Response
    {
        $orders->cancelExpiredOrders();

        $penyesuaian = [];
        $items = $cart->items($penyesuaian);
        if ($penyesuaian && !session()->has('flash')) {
            session()->flash('flash', [
                'tipe' => 'gagal',
                'pesan' => implode(' ', $penyesuaian),
            ]);
        }

        $subtotal = array_sum(array_column($items, 'subtotal'));
        $ongkir = $items ? $orders->shippingCost($subtotal) : 0;
        $total = $subtotal + $ongkir;

        return response()->view('keranjang', [
            'items' => $items,
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'total' => $total,
            'judul' => 'Keranjang Belanja | ' . config('toko.nama'),
            'deskripsi' => 'Keranjang belanja ' . config('toko.nama') . '. Periksa produk pilihanmu sebelum melanjutkan ke checkout.',
            'kanonikal' => route('keranjang.index'),
            'halaman' => 'keranjang',
            'robots' => 'noindex, follow',
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function handle(Request $request, CartService $cart, OrderService $orders): RedirectResponse
    {
        $orders->cancelExpiredOrders();

        $aksi = (string) $request->input('aksi', '');
        $id = (int) $request->input('id', 0);

        if ($aksi === 'tambah') {
            $produk = Produk::find($id);
            if (!$produk || (int) $produk->stok < 1) {
                return $this->redirectFlash('produk.index', 'gagal', 'Produk tidak tersedia atau sudah terjual.');
            }
            $jumlah = max(1, (int) $request->input('jumlah', 1));
            $sekarang = (int) ($cart->raw()[$id] ?? 0);
            $totalJumlah = min((int) $produk->stok, $sekarang + $jumlah);
            $cart->set($id, $totalJumlah);

            $tujuan = $request->input('lanjut') === 'checkout' ? 'checkout.show' : 'keranjang.index';

            return $this->redirectFlash($tujuan, 'sukses', $produk->nama . ' masuk ke keranjang.');
        }

        if ($aksi === 'ubah') {
            $produk = Produk::find($id);
            $stok = $produk ? (int) $produk->stok : 0;
            $jumlah = (int) $request->input('jumlah', 1);
            if ($stok < 1) {
                $cart->remove($id);

                return $this->redirectFlash('keranjang.index', 'gagal', 'Produk sudah terjual dan dihapus dari keranjang.');
            }
            $cart->set($id, min($stok, max(1, $jumlah)));

            return $this->redirectFlash('keranjang.index', 'sukses', 'Keranjang diperbarui.');
        }

        if ($aksi === 'hapus') {
            $cart->remove($id);

            return $this->redirectFlash('keranjang.index', 'sukses', 'Produk dihapus dari keranjang.');
        }

        return $this->redirectFlash('keranjang.index', 'gagal', 'Aksi keranjang tidak valid.');
    }
}
