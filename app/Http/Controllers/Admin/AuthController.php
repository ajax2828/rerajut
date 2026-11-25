<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin(Request $request): Response|RedirectResponse
    {
        if ($request->session()->get('admin') && $request->session()->get('admin_id')) {
            return redirect()->route('admin.orders.index');
        }

        return response()->view('admin.login', [
            'judul' => 'Masuk Admin | ' . config('toko.nama'),
            'deskripsi' => 'Halaman masuk pengelola toko ' . config('toko.nama') . '.',
            'kanonikal' => route('admin.login'),
            'robots' => 'noindex, nofollow',
            'halaman' => '',
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function login(Request $request): RedirectResponse
    {
        $rateKey = 'admin-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $menit = (int) ceil(RateLimiter::availableIn($rateKey) / 60);

            return redirect()->route('admin.login')
                ->withInput($request->only('username'))
                ->withErrors(['Terlalu banyak percobaan masuk. Coba lagi dalam ' . max(1, $menit) . ' menit.']);
        }

        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

        if ($username === '' || $password === '') {
            return redirect()->route('admin.login')
                ->withInput(['username' => $username])
                ->withErrors(['Username dan kata sandi wajib diisi.']);
        }

        $user = User::where('username', $username)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            RateLimiter::hit($rateKey, 300);

            return redirect()->route('admin.login')
                ->withInput(['username' => $username])
                ->withErrors(['Username atau kata sandi admin salah.']);
        }

        RateLimiter::clear($rateKey);
        $request->session()->regenerate();
        $request->session()->put([
            'admin' => true,
            'admin_id' => (int) $user->id,
            'admin_username' => $user->username,
            'admin_nama' => $user->nama,
            'admin_last_active' => time(),
        ]);

        return redirect()->route('admin.orders.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'admin',
            'admin_id',
            'admin_username',
            'admin_nama',
            'admin_last_active',
        ]);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return $this->redirectFlash('home', 'sukses', 'Kamu sudah keluar dari halaman admin.');
    }
}
