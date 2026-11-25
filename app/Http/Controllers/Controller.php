<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

abstract class Controller
{
    protected function flash(string $tipe, string $pesan): array
    {
        return ['flash' => ['tipe' => $tipe, 'pesan' => $pesan]];
    }

    protected function redirectFlash(string $route, string $tipe, string $pesan, array $params = []): RedirectResponse
    {
        return redirect()->route($route, $params)->with($this->flash($tipe, $pesan));
    }
}
