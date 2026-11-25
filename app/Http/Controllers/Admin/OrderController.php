<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesanKontak;
use App\Services\ImageUploadService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request, OrderService $orders): View
    {
        $orders->cancelExpiredOrders();

        $statusList = OrderService::STATUS_LIST;
        $filterStatus = trim((string) $request->query('status', ''));
        if ($filterStatus !== '' && !in_array($filterStatus, $statusList, true)) {
            $filterStatus = '';
        }
        $cari = trim((string) $request->query('q', ''));

        $query = Pesanan::query();
        if ($filterStatus !== '') {
            $query->where('status', $filterStatus);
        }
        if ($cari !== '') {
            $query->where(function ($q) use ($cari) {
                $q->where('kode', 'like', '%' . $cari . '%')
                    ->orWhere('nama', 'like', '%' . $cari . '%')
                    ->orWhere('email', 'like', '%' . $cari . '%')
                    ->orWhere('telepon', 'like', '%' . $cari . '%');
            });
        }

        $perHal = 20;
        $totalHasil = (clone $query)->count();
        $totalHal = max(1, (int) ceil($totalHasil / $perHal));
        $hal = min($totalHal, max(1, (int) $request->query('hal', 1)));
        $pesananList = $query->orderByDesc('id')->offset(($hal - 1) * $perHal)->limit($perHal)->get();

        $totalPesanan = Pesanan::count();
        $menunggu = Pesanan::where('status', 'Menunggu Pembayaran')->count();
        $diproses = Pesanan::whereIn('status', ['Pembayaran Diproses', 'Diproses', 'Dikirim'])->count();
        $omzet = (int) Pesanan::whereIn('status', ['Diproses', 'Dikirim', 'Selesai'])->sum('total');
        $pesanBaru = PesanKontak::count();

        return view('admin.index', [
            'statusList' => $statusList,
            'filterStatus' => $filterStatus,
            'cari' => $cari,
            'hal' => $hal,
            'totalHal' => $totalHal,
            'pesananList' => $pesananList,
            'totalPesanan' => $totalPesanan,
            'menunggu' => $menunggu,
            'diproses' => $diproses,
            'omzet' => $omzet,
            'pesanBaru' => $pesanBaru,
            'judul' => 'Dashboard Admin | ' . config('toko.nama'),
            'deskripsi' => 'Dashboard pengelolaan pesanan ' . config('toko.nama') . '.',
            'kanonikal' => route('admin.orders.index'),
            'robots' => 'noindex, nofollow',
            'halaman' => 'admin-pesanan',
        ]);
    }

    public function show(string $kode, OrderService $orders): View|RedirectResponse
    {
        $orders->cancelExpiredOrders();

        $pesanan = Pesanan::with('items')->where('kode', trim($kode))->first();
        if (!$pesanan) {
            return $this->redirectFlash('admin.orders.index', 'gagal', 'Pesanan tidak ditemukan.');
        }

        return view('admin.pesanan', [
            'pesanan' => $pesanan,
            'statusList' => OrderService::STATUS_LIST,
            'judul' => 'Detail Pesanan ' . $pesanan->kode . ' | Admin ' . config('toko.nama'),
            'deskripsi' => 'Detail pesanan pelanggan ' . config('toko.nama') . '.',
            'kanonikal' => route('admin.orders.show', $pesanan->kode),
            'robots' => 'noindex, nofollow',
            'halaman' => 'admin-pesanan',
        ]);
    }

    public function updateStatus(Request $request, int $id, OrderService $orders): RedirectResponse
    {
        $status = (string) $request->input('status', '');
        $hasil = $orders->updateStatus($id, $status);

        return redirect()->back()->with($this->flash($hasil['ok'] ? 'sukses' : 'gagal', $hasil['pesan']));
    }

    public function rejectProof(string $kode, OrderService $orders, ImageUploadService $uploads): RedirectResponse
    {
        $pesanan = Pesanan::where('kode', trim($kode))->first();
        if (!$pesanan) {
            return $this->redirectFlash('admin.orders.index', 'gagal', 'Pesanan tidak ditemukan.');
        }

        $hasil = $orders->rejectProof((int) $pesanan->id, $uploads);

        return redirect()->route('admin.orders.show', $pesanan->kode)
            ->with($this->flash($hasil['ok'] ? 'sukses' : 'gagal', $hasil['pesan']));
    }
}
