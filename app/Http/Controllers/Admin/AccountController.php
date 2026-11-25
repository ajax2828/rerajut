<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = User::find((int) $request->session()->get('admin_id'));
        if (!$user) {
            return redirect()->route('admin.login');
        }

        return view('admin.akun', [
            'user' => $user,
            'judul' => 'Akun Admin | ' . config('toko.nama'),
            'deskripsi' => 'Pengaturan akun pengelola ' . config('toko.nama') . '.',
            'kanonikal' => route('admin.account.show'),
            'robots' => 'noindex, nofollow',
            'halaman' => 'admin-akun',
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = User::find((int) $request->session()->get('admin_id'));
        if (!$user) {
            return redirect()->route('admin.login');
        }

        $nama = trim((string) $request->input('nama', ''));
        if (mb_strlen($nama) < 3 || mb_strlen($nama) > 120) {
            return redirect()->route('admin.account.show')
                ->withInput(['nama' => $nama])
                ->withErrors(['Nama tampilan harus antara 3 sampai 120 karakter.']);
        }

        $user->update(['nama' => $nama]);
        $request->session()->put('admin_nama', $nama);

        return $this->redirectFlash('admin.account.show', 'sukses', 'Profil admin berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = User::find((int) $request->session()->get('admin_id'));
        if (!$user) {
            return redirect()->route('admin.login');
        }

        $sandiLama = (string) $request->input('sandi_lama', '');
        $sandiBaru = (string) $request->input('sandi_baru', '');
        $konfirmasi = (string) $request->input('konfirmasi_sandi', '');

        $galat = [];
        if (!Hash::check($sandiLama, $user->password)) {
            $galat[] = 'Kata sandi saat ini tidak cocok.';
        }
        if (strlen($sandiBaru) < 8) {
            $galat[] = 'Kata sandi baru minimal 8 karakter.';
        }
        if ($sandiBaru !== $konfirmasi) {
            $galat[] = 'Konfirmasi kata sandi baru tidak cocok.';
        }

        if ($galat) {
            return redirect()->route('admin.account.show')->withErrors($galat);
        }

        $user->update(['password' => Hash::make($sandiBaru)]);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return $this->redirectFlash('admin.account.show', 'sukses', 'Kata sandi admin berhasil diperbarui.');
    }
}
