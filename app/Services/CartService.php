<?php

namespace App\Services;

use App\Models\Produk;

class CartService
{
    public function raw(): array
    {
        $cart = session('keranjang', []);

        return is_array($cart) ? $cart : [];
    }

    public function count(): int
    {
        return array_sum(array_map('intval', $this->raw()));
    }

    public function set(int $id, int $jumlah): void
    {
        $cart = $this->raw();
        if ($jumlah < 1) {
            unset($cart[$id]);
        } else {
            $cart[$id] = $jumlah;
        }
        session(['keranjang' => $cart]);
    }

    public function remove(int $id): void
    {
        $cart = $this->raw();
        unset($cart[$id]);
        session(['keranjang' => $cart]);
    }

    public function clear(): void
    {
        session()->forget('keranjang');
    }

    public function items(array &$penyesuaian = []): array
    {
        $penyesuaian = [];
        $isi = $this->raw();
        if (!$isi) {
            return [];
        }

        $ids = array_map('intval', array_keys($isi));
        $ditemukan = Produk::whereIn('id', $ids)->get()->keyBy('id');

        $cartBaru = $isi;
        $hasil = [];
        foreach ($isi as $id => $diminta) {
            $id = (int) $id;
            $diminta = (int) $diminta;
            $produk = $ditemukan->get($id);
            if (!$produk) {
                unset($cartBaru[$id]);
                $penyesuaian[] = 'Salah satu produk di keranjang sudah tidak tersedia dan dihapus otomatis.';
                continue;
            }
            $stok = (int) $produk->stok;
            if ($stok < 1) {
                unset($cartBaru[$id]);
                $penyesuaian[] = $produk->nama . ' sudah terjual dan dihapus dari keranjang.';
                continue;
            }
            $jumlah = min($stok, max(1, $diminta));
            if ($jumlah !== $diminta) {
                $cartBaru[$id] = $jumlah;
                $penyesuaian[] = 'Jumlah ' . $produk->nama . ' disesuaikan mengikuti stok tersedia (' . $stok . ').';
            }
            $hasil[] = [
                'id' => (int) $produk->id,
                'produk' => $produk,
                'nama' => $produk->nama,
                'slug' => $produk->slug,
                'gambar' => $produk->gambar,
                'ukuran' => $produk->ukuran,
                'kondisi' => $produk->kondisi,
                'harga' => (int) $produk->harga,
                'stok' => $stok,
                'jumlah' => $jumlah,
                'subtotal' => (int) $produk->harga * $jumlah,
            ];
        }

        if ($cartBaru !== $isi) {
            session(['keranjang' => $cartBaru]);
        }

        usort($hasil, fn ($a, $b) => $a['id'] <=> $b['id']);

        return $hasil;
    }
}
