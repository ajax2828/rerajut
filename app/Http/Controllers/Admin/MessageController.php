<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Langganan;
use App\Models\PesanKontak;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        return view('admin.pesan', [
            'pesanList' => PesanKontak::orderByDesc('id')->get(),
            'langgananList' => Langganan::orderByDesc('id')->get(),
            'judul' => 'Pesan Pelanggan | Admin ' . config('toko.nama'),
            'deskripsi' => 'Pesan pelanggan dan daftar langganan ' . config('toko.nama') . '.',
            'kanonikal' => route('admin.messages.index'),
            'robots' => 'noindex, nofollow',
            'halaman' => 'admin-pesan',
        ]);
    }

    public function destroyMessage(int $id): RedirectResponse
    {
        PesanKontak::where('id', $id)->delete();

        return $this->redirectFlash('admin.messages.index', 'sukses', 'Pesan pelanggan dihapus.');
    }

    public function destroySubscriber(int $id): RedirectResponse
    {
        Langganan::where('id', $id)->delete();

        return $this->redirectFlash('admin.messages.index', 'sukses', 'Email langganan dihapus.');
    }
}
