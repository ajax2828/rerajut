<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $kategoriList = Kategori::withCount('produk')->orderBy('nama')->get();

        return view('admin.kategori', [
            'kategoriList' => $kategoriList,
            'judul' => 'Kelola Kategori | Admin ' . config('toko.nama'),
            'deskripsi' => 'Kelola kategori produk ' . config('toko.nama') . '.',
            'kanonikal' => route('admin.categories.index'),
            'robots' => 'noindex, nofollow',
            'halaman' => 'admin-kategori',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $nama = trim((string) $request->input('nama', ''));
        $slug = slug_aman($nama, 75);
        if (mb_strlen($nama) < 2 || mb_strlen($nama) > 80 || $slug === '') {
            return $this->redirectFlash('admin.categories.index', 'gagal', 'Nama kategori harus antara 2 sampai 80 karakter.');
        }
        if (Kategori::where('slug', $slug)->exists()) {
            return $this->redirectFlash('admin.categories.index', 'gagal', 'Kategori dengan nama tersebut sudah ada.');
        }

        Kategori::create(['nama' => $nama, 'slug' => $slug]);

        return $this->redirectFlash('admin.categories.index', 'sukses', 'Kategori ' . $nama . ' berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $kategori = Kategori::find($id);
        if (!$kategori) {
            return $this->redirectFlash('admin.categories.index', 'gagal', 'Kategori tidak ditemukan.');
        }

        $nama = trim((string) $request->input('nama', ''));
        $slug = slug_aman($nama, 75);
        if (mb_strlen($nama) < 2 || mb_strlen($nama) > 80 || $slug === '') {
            return $this->redirectFlash('admin.categories.index', 'gagal', 'Nama kategori tidak valid (2 sampai 80 karakter).');
        }
        if (Kategori::where('slug', $slug)->where('id', '<>', $id)->exists()) {
            return $this->redirectFlash('admin.categories.index', 'gagal', 'Slug kategori sudah digunakan kategori lain.');
        }

        $kategori->update(['nama' => $nama, 'slug' => $slug]);

        return $this->redirectFlash('admin.categories.index', 'sukses', 'Kategori berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $dipakai = Produk::where('kategori_id', $id)->count();
        if ($dipakai > 0) {
            return $this->redirectFlash('admin.categories.index', 'gagal', 'Kategori tidak dapat dihapus karena masih dipakai oleh ' . $dipakai . ' produk.');
        }

        Kategori::where('id', $id)->delete();

        return $this->redirectFlash('admin.categories.index', 'sukses', 'Kategori berhasil dihapus.');
    }
}
