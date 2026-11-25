<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $idleLimit = 7200; // 2 jam
        $lastActive = (int) $request->session()->get('admin_last_active', 0);
        if ($lastActive > 0 && (time() - $lastActive) > $idleLimit) {
            $request->session()->forget(['admin', 'admin_id', 'admin_username', 'admin_nama', 'admin_last_active']);
            $request->session()->regenerate();

            return redirect()->route('admin.login')->with('flash', [
                'tipe' => 'gagal',
                'pesan' => 'Sesi admin telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.',
            ]);
        }

        if (!$request->session()->get('admin') || !$request->session()->get('admin_id')) {
            return redirect()->route('admin.login')->with('flash', [
                'tipe' => 'gagal',
                'pesan' => 'Silakan masuk sebagai admin terlebih dahulu.',
            ]);
        }

        $admin = User::find((int) $request->session()->get('admin_id'));
        if (!$admin) {
            $request->session()->forget(['admin', 'admin_id', 'admin_username', 'admin_nama', 'admin_last_active']);
            $request->session()->regenerate();

            return redirect()->route('admin.login')->with('flash', [
                'tipe' => 'gagal',
                'pesan' => 'Akun admin tidak ditemukan. Silakan masuk kembali.',
            ]);
        }

        $request->session()->put([
            'admin_username' => $admin->username,
            'admin_nama' => $admin->nama,
            'admin_last_active' => time(),
        ]);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
