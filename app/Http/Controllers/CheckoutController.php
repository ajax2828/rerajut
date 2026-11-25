<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckoutController extends Controller
{
    public function show(CartService $cart, OrderService $orders): Response|RedirectResponse
    {
        $orders->cancelExpiredOrders();

        $penyesuaian = [];
        $items = $cart->items($penyesuaian);
        if (!$items) {
            $pesan = $penyesuaian
                ? implode(' ', $penyesuaian)
                : 'Keranjang masih kosong. Pilih produk terlebih dahulu.';

            return $this->redirectFlash('produk.index', 'gagal', $pesan);
        }

        if ($penyesuaian && !session()->has('flash')) {
            session()->flash('flash', ['tipe' => 'gagal', 'pesan' => implode(' ', $penyesuaian)]);
        }

        $subtotal = array_sum(array_column($items, 'subtotal'));
        $ongkir = $orders->shippingCost($subtotal);
        $total = $subtotal + $ongkir;

        return response()->view('checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'total' => $total,
            'metodeBayar' => OrderService::PAYMENT_METHODS,
            'judul' => 'Checkout Pesanan | ' . config('toko.nama'),
            'deskripsi' => 'Lengkapi data pengiriman dan pilih metode pembayaran untuk menyelesaikan pesanan di ' . config('toko.nama') . '.',
            'kanonikal' => route('checkout.show'),
            'halaman' => 'checkout',
            'robots' => 'noindex, nofollow',
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function store(Request $request, CartService $cart, OrderService $orders): RedirectResponse
    {
        $orders->cancelExpiredOrders();

        $penyesuaian = [];
        $items = $cart->items($penyesuaian);
        if (!$items) {
            return $this->redirectFlash('produk.index', 'gagal', 'Keranjang kosong atau produk sudah terjual.');
        }

        $data = [
            'nama' => trim((string) $request->input('nama', '')),
            'email' => trim((string) $request->input('email', '')),
            'telepon' => trim((string) $request->input('telepon', '')),
            'alamat' => trim((string) $request->input('alamat', '')),
            'kota' => trim((string) $request->input('kota', '')),
            'provinsi' => trim((string) $request->input('provinsi', '')),
            'kode_pos' => trim((string) $request->input('kode_pos', '')),
            'catatan' => trim((string) $request->input('catatan', '')),
            'metode_bayar' => trim((string) $request->input('metode_bayar', 'Transfer Bank')),
        ];

        $galat = [];
        if (mb_strlen($data['nama']) < 3 || mb_strlen($data['nama']) > 120) {
            $galat[] = 'Nama lengkap harus antara 3 sampai 120 karakter.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 150) {
            $galat[] = 'Alamat email tidak valid.';
        }
        $digitTelepon = (string) preg_replace('/[^0-9]/', '', $data['telepon']);
        if (strlen($digitTelepon) < 9 || strlen($digitTelepon) > 15 || mb_strlen($data['telepon']) > 30) {
            $galat[] = 'Nomor telepon harus terdiri dari 9 sampai 15 digit.';
        }
        if (mb_strlen($data['alamat']) < 10 || mb_strlen($data['alamat']) > 1000) {
            $galat[] = 'Alamat lengkap harus antara 10 sampai 1000 karakter.';
        }
        if ($data['kota'] === '' || mb_strlen($data['kota']) > 80) {
            $galat[] = 'Kota atau kabupaten wajib diisi (maksimal 80 karakter).';
        }
        if ($data['provinsi'] === '' || mb_strlen($data['provinsi']) > 80) {
            $galat[] = 'Provinsi wajib diisi (maksimal 80 karakter).';
        }
        if (!preg_match('/^[0-9]{5}$/', $data['kode_pos'])) {
            $galat[] = 'Kode pos harus terdiri dari 5 angka.';
        }
        if (mb_strlen($data['catatan']) > 1000) {
            $galat[] = 'Catatan maksimal 1000 karakter.';
        }
        if (!in_array($data['metode_bayar'], OrderService::PAYMENT_METHODS, true)) {
            $galat[] = 'Metode pembayaran tidak valid.';
        }

        if ($galat) {
            return redirect()->route('checkout.show')->withInput($data)->withErrors($galat);
        }

        try {
            $kode = DB::transaction(function () use ($items, $data, $orders) {
                $subtotalDb = 0;
                $itemDb = [];
                foreach ($items as $item) {
                    $row = DB::table('produk')->where('id', $item['id'])->lockForUpdate()->first();
                    if (!$row || (int) $row->stok < (int) $item['jumlah']) {
                        throw new \RuntimeException('Stok produk ' . $item['nama'] . ' tidak mencukupi.');
                    }
                    $barisSubtotal = (int) $row->harga * (int) $item['jumlah'];
                    $subtotalDb += $barisSubtotal;
                    $itemDb[] = [
                        'produk_id' => (int) $row->id,
                        'nama' => (string) $row->nama,
                        'ukuran' => (string) $row->ukuran,
                        'harga' => (int) $row->harga,
                        'jumlah' => (int) $item['jumlah'],
                        'subtotal' => $barisSubtotal,
                    ];
                }

                $ongkirDb = $orders->shippingCost($subtotalDb);
                $totalDb = $subtotalDb + $ongkirDb;
                $kode = $orders->generateOrderCode();
                $statusAwal = $data['metode_bayar'] === 'COD' ? 'Diproses' : 'Menunggu Pembayaran';
                $batasBayar = $data['metode_bayar'] === 'COD' ? null : now()->addDay()->format('Y-m-d H:i:s');

                $pesananId = (int) DB::table('pesanan')->insertGetId([
                    'kode' => $kode,
                    'nama' => $data['nama'],
                    'email' => $data['email'],
                    'telepon' => $data['telepon'],
                    'alamat' => $data['alamat'],
                    'kota' => $data['kota'],
                    'provinsi' => $data['provinsi'],
                    'kode_pos' => $data['kode_pos'],
                    'catatan' => $data['catatan'],
                    'metode_bayar' => $data['metode_bayar'],
                    'subtotal' => $subtotalDb,
                    'ongkir' => $ongkirDb,
                    'total' => $totalDb,
                    'status' => $statusAwal,
                    'batas_bayar' => $batasBayar,
                    'dibuat_pada' => now()->format('Y-m-d H:i:s'),
                ]);

                foreach ($itemDb as $item) {
                    $updated = DB::table('produk')
                        ->where('id', $item['produk_id'])
                        ->where('stok', '>=', $item['jumlah'])
                        ->decrement('stok', $item['jumlah']);
                    if ($updated !== 1) {
                        throw new \RuntimeException('Stok produk ' . $item['nama'] . ' baru saja habis.');
                    }
                    DB::table('pesanan_item')->insert([
                        'pesanan_id' => $pesananId,
                        'produk_id' => $item['produk_id'],
                        'nama_produk' => $item['nama'],
                        'ukuran' => $item['ukuran'],
                        'harga' => $item['harga'],
                        'jumlah' => $item['jumlah'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                return $kode;
            });

            $cart->clear();

            return redirect()->route('pesanan.sukses', ['kode' => $kode]);
        } catch (\RuntimeException $e) {
            return redirect()->route('checkout.show')->withInput($data)->withErrors([$e->getMessage()]);
        } catch (Throwable $e) {
            Log::error('[Rerajut Checkout Error] ' . $e->getMessage());

            return redirect()->route('checkout.show')->withInput($data)->withErrors(['Pesanan gagal diproses. Silakan coba lagi.']);
        }
    }
}
