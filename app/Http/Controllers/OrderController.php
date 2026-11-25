<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Services\ImageUploadService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    public function success(Request $request, OrderService $orders): Response
    {
        $orders->cancelExpiredOrders();

        $kode = trim((string) $request->query('kode', ''));
        $pesanan = $kode !== '' ? Pesanan::with('items')->where('kode', $kode)->first() : null;

        $metodeNonCod = $pesanan && in_array($pesanan->metode_bayar, ['Transfer Bank', 'E-Wallet'], true);
        $lewatBatas = $pesanan && !empty($pesanan->batas_bayar) && time() > strtotime((string) $pesanan->batas_bayar);

        $pesanStatusBukti = 'Pembayaran sedang diperiksa oleh admin.';
        if ($pesanan) {
            if ($pesanan->status === 'Diproses') {
                $pesanStatusBukti = 'Pembayaran telah dikonfirmasi dan pesanan sedang diproses.';
            } elseif ($pesanan->status === 'Dikirim') {
                $pesanStatusBukti = 'Pembayaran telah dikonfirmasi dan pesanan sedang dalam pengiriman.';
            } elseif ($pesanan->status === 'Selesai') {
                $pesanStatusBukti = 'Pembayaran telah dikonfirmasi dan pesanan telah selesai.';
            } elseif ($pesanan->status === 'Dibatalkan') {
                $pesanStatusBukti = 'Pesanan ini telah dibatalkan.';
            }
        }

        return response()->view('pesanan-sukses', [
            'pesanan' => $pesanan,
            'metodeNonCod' => $metodeNonCod,
            'lewatBatas' => $lewatBatas,
            'pesanStatusBukti' => $pesanStatusBukti,
            'judul' => $pesanan ? 'Pesanan ' . $pesanan->kode . ' Berhasil | ' . config('toko.nama') : 'Pesanan Tidak Ditemukan | ' . config('toko.nama'),
            'deskripsi' => 'Konfirmasi pesanan ' . config('toko.nama') . ' beserta instruksi pembayaran.',
            'kanonikal' => route('pesanan.sukses'),
            'halaman' => 'pesanan',
            'robots' => 'noindex, nofollow',
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function uploadProof(Request $request, OrderService $orders, ImageUploadService $uploads): RedirectResponse
    {
        $kode = trim((string) $request->input('kode', ''));
        $backUrl = $kode !== '' ? route('pesanan.sukses', ['kode' => $kode]) : route('home');

        $orders->cancelExpiredOrders();

        $pesanan = $kode !== '' ? Pesanan::where('kode', $kode)->first() : null;
        if (!$pesanan) {
            return $this->redirectFlash('home', 'gagal', 'Pesanan tidak ditemukan.');
        }

        if ($pesanan->metode_bayar === 'COD') {
            return redirect($backUrl)->with($this->flash('gagal', 'Pesanan COD tidak memerlukan bukti pembayaran.'));
        }

        if ($pesanan->status !== 'Menunggu Pembayaran' || !empty($pesanan->bukti_bayar)) {
            return redirect($backUrl)->with($this->flash('gagal', 'Bukti pembayaran sudah diunggah atau status pesanan sudah berubah.'));
        }

        if (!empty($pesanan->batas_bayar) && time() > strtotime((string) $pesanan->batas_bayar)) {
            return redirect($backUrl)->with($this->flash('gagal', 'Batas waktu upload bukti pembayaran sudah lewat. Silakan hubungi kami melalui halaman kontak.'));
        }

        $file = $request->file('bukti');
        if (!$file) {
            return redirect($backUrl)->with($this->flash('gagal', 'Pilih file bukti pembayaran terlebih dahulu (maksimal 2 MB).'));
        }

        $hasil = $uploads->store($file, 'uploads/bukti', $pesanan->kode, 2 * 1024 * 1024);
        if (!$hasil['ok']) {
            return redirect($backUrl)->with($this->flash('gagal', $hasil['pesan']));
        }

        $namaFile = $hasil['filename'];
        try {
            $updated = DB::table('pesanan')
                ->where('id', $pesanan->id)
                ->where('status', 'Menunggu Pembayaran')
                ->whereNull('bukti_bayar')
                ->update([
                    'bukti_bayar' => $namaFile,
                    'dibayar_pada' => now()->format('Y-m-d H:i:s'),
                    'status' => 'Pembayaran Diproses',
                ]);
            if ($updated !== 1) {
                $uploads->deleteIfExists('uploads/bukti', $namaFile);

                return redirect($backUrl)->with($this->flash('gagal', 'Bukti pembayaran sudah diunggah atau status pesanan sudah berubah.'));
            }
        } catch (Throwable $e) {
            $uploads->deleteIfExists('uploads/bukti', $namaFile);
            Log::error('[Rerajut Upload DB Error] ' . $e->getMessage());

            return redirect($backUrl)->with($this->flash('gagal', 'Gagal menyimpan bukti pembayaran. Silakan coba lagi.'));
        }

        return redirect(route('pesanan.sukses', ['kode' => $pesanan->kode]) . '#pembayaran')
            ->with($this->flash('sukses', 'Bukti pembayaran berhasil diunggah. Pesanan akan segera kami periksa.'));
    }

    public function track(Request $request, OrderService $orders): Response
    {
        $orders->cancelExpiredOrders();

        $kode = trim((string) $request->query('kode', ''));
        $sudahCari = $kode !== '';
        $pesanan = $sudahCari ? Pesanan::with('items')->where('kode', $kode)->first() : null;

        $urutanStatus = ['Menunggu Pembayaran', 'Pembayaran Diproses', 'Diproses', 'Dikirim', 'Selesai'];
        if ($pesanan && $pesanan->metode_bayar === 'COD') {
            $urutanStatus = ['Diproses', 'Dikirim', 'Selesai'];
        }
        $indeksStatus = $pesanan ? array_search($pesanan->status, $urutanStatus, true) : false;
        if ($indeksStatus === false) {
            $indeksStatus = $pesanan && $pesanan->status === 'Dibatalkan' ? -1 : 0;
        }
        $metodeNonCod = $pesanan && in_array($pesanan->metode_bayar, ['Transfer Bank', 'E-Wallet'], true);
        $lewatBatas = $pesanan && !empty($pesanan->batas_bayar) && time() > strtotime((string) $pesanan->batas_bayar);
        $bisaUpload = $metodeNonCod && $pesanan->status === 'Menunggu Pembayaran' && empty($pesanan->bukti_bayar) && !$lewatBatas;

        return response()->view('lacak', [
            'kode' => $kode,
            'sudahCari' => $sudahCari,
            'pesanan' => $pesanan,
            'urutanStatus' => $urutanStatus,
            'indeksStatus' => $indeksStatus,
            'metodeNonCod' => $metodeNonCod,
            'bisaUpload' => $bisaUpload,
            'judul' => 'Lacak Pesanan | ' . config('toko.nama'),
            'deskripsi' => 'Cek status pesanan baju thrift kamu di ' . config('toko.nama') . ' dengan memasukkan kode pesanan.',
            'kanonikal' => route('lacak'),
            'halaman' => 'lacak',
            'robots' => 'noindex, follow',
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function myOrders(): View
    {
        return view('pesanan-saya', [
            'judul' => 'Pesanan Saya | ' . config('toko.nama'),
            'deskripsi' => 'Daftar pesanan yang pernah kamu buat di ' . config('toko.nama') . ', lengkap dengan status dan tautan pembayaran.',
            'kanonikal' => route('pesanan.saya'),
            'halaman' => 'pesanan-saya',
            'robots' => 'noindex, follow',
        ]);
    }

    public function apiStatus(Request $request, OrderService $orders): JsonResponse
    {
        $orders->cancelExpiredOrders();

        $kode = trim((string) $request->query('kode', ''));
        if ($kode === '' || strlen($kode) > 40) {
            return response()->json(['ditemukan' => false])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        $pesanan = Pesanan::with('items')->where('kode', $kode)->first();
        if (!$pesanan) {
            return response()->json(['ditemukan' => false])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        $metodeNonCod = in_array($pesanan->metode_bayar, ['Transfer Bank', 'E-Wallet'], true);
        $lewatBatas = !empty($pesanan->batas_bayar) && time() > strtotime((string) $pesanan->batas_bayar);
        $punyaBukti = !empty($pesanan->bukti_bayar);

        return response()->json([
            'ditemukan' => true,
            'kode' => $pesanan->kode,
            'nama' => $pesanan->nama,
            'tanggal' => tanggal_wib($pesanan->dibuat_pada, false),
            'total' => (int) $pesanan->total,
            'status' => $pesanan->status,
            'metode' => $pesanan->metode_bayar,
            'batas_bayar' => !empty($pesanan->batas_bayar) ? tanggal_wib($pesanan->batas_bayar, false) : null,
            'bukti' => $punyaBukti,
            'bisa_upload' => $metodeNonCod && $pesanan->status === 'Menunggu Pembayaran' && !$punyaBukti && !$lewatBatas,
            'lewat_batas' => $lewatBatas && !$punyaBukti && !in_array($pesanan->status, ['Selesai', 'Diproses', 'Dikirim'], true),
            'items' => $pesanan->items->map(fn ($item) => [
                'nama' => $item->nama_produk,
                'ukuran' => $item->ukuran,
                'jumlah' => (int) $item->jumlah,
                'subtotal' => (int) $item->subtotal,
            ])->values()->all(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
