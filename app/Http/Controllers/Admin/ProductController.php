<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    private const KONDISI_OPSI = ['Sangat Baik', 'Baik', 'Cukup'];

    public function index(Request $request): View
    {
        $kategoriList = Kategori::withCount('produk')->orderBy('nama')->get();
        $cari = trim((string) $request->query('q', ''));
        $filterKategori = (int) $request->query('kategori', 0);
        $filterStok = (string) $request->query('stok', '');
        if (!in_array($filterStok, ['', 'tersedia', 'habis'], true)) {
            $filterStok = '';
        }

        $query = Produk::with('kategori');
        if ($cari !== '') {
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', '%' . $cari . '%')
                    ->orWhere('ukuran', 'like', '%' . $cari . '%');
            });
        }
        if ($filterKategori > 0) {
            $query->where('kategori_id', $filterKategori);
        }
        if ($filterStok === 'tersedia') {
            $query->where('stok', '>', 0);
        } elseif ($filterStok === 'habis') {
            $query->where('stok', '<', 1);
        }

        $perHal = 20;
        $totalHasil = (clone $query)->count();
        $totalHal = max(1, (int) ceil($totalHasil / $perHal));
        $hal = min($totalHal, max(1, (int) $request->query('hal', 1)));
        $produkList = $query->orderByDesc('id')->offset(($hal - 1) * $perHal)->limit($perHal)->get();

        return view('admin.produk', [
            'produkList' => $produkList,
            'kategoriList' => $kategoriList,
            'cari' => $cari,
            'filterKategori' => $filterKategori,
            'filterStok' => $filterStok,
            'hal' => $hal,
            'totalHal' => $totalHal,
            'judul' => 'Kelola Produk | Admin ' . config('toko.nama'),
            'deskripsi' => 'Kelola daftar produk ' . config('toko.nama') . '.',
            'kanonikal' => route('admin.products.index'),
            'robots' => 'noindex, nofollow',
            'halaman' => 'admin-produk',
        ]);
    }

    public function create(): View
    {
        $kategoriList = Kategori::orderBy('nama')->get();

        return view('admin.produk-form', [
            'produk' => null,
            'kategoriList' => $kategoriList,
            'kondisiOpsi' => self::KONDISI_OPSI,
            'judul' => 'Tambah Produk | Admin ' . config('toko.nama'),
            'deskripsi' => 'Form tambah produk ' . config('toko.nama') . '.',
            'kanonikal' => route('admin.products.create'),
            'robots' => 'noindex, nofollow',
            'halaman' => 'admin-produk',
        ]);
    }

    public function edit(int $id): View|RedirectResponse
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return $this->redirectFlash('admin.products.index', 'gagal', 'Produk tidak ditemukan.');
        }

        $kategoriList = Kategori::orderBy('nama')->get();

        return view('admin.produk-form', [
            'produk' => $produk,
            'kategoriList' => $kategoriList,
            'kondisiOpsi' => self::KONDISI_OPSI,
            'judul' => 'Ubah Produk | Admin ' . config('toko.nama'),
            'deskripsi' => 'Form ubah produk ' . config('toko.nama') . '.',
            'kanonikal' => route('admin.products.edit', $produk->id),
            'robots' => 'noindex, nofollow',
            'halaman' => 'admin-produk',
        ]);
    }

    public function store(Request $request, ImageUploadService $uploads): RedirectResponse
    {
        return $this->saveProduct($request, null, $uploads);
    }

    public function update(Request $request, int $id, ImageUploadService $uploads): RedirectResponse
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return $this->redirectFlash('admin.products.index', 'gagal', 'Produk tidak ditemukan.');
        }

        return $this->saveProduct($request, $produk, $uploads);
    }

    public function destroy(int $id, ImageUploadService $uploads): RedirectResponse
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return redirect()->back()->with($this->flash('gagal', 'Produk tidak ditemukan.'));
        }

        $gambar = $produk->gambar;
        $nama = $produk->nama;
        $dipakai = Produk::where('gambar', $gambar)->where('id', '<>', $id)->count();
        $produk->delete();

        if ($dipakai === 0 && !empty($gambar)) {
            $uploads->deleteIfExists('images', $gambar);
        }

        return redirect()->back()->with($this->flash('sukses', 'Produk ' . $nama . ' berhasil dihapus.'));
    }

    private function saveProduct(Request $request, ?Produk $produk, ImageUploadService $uploads): RedirectResponse
    {
        $data = [
            'nama' => trim((string) $request->input('nama', '')),
            'kategori_id' => (int) $request->input('kategori_id', 0),
            'deskripsi' => trim((string) $request->input('deskripsi', '')),
            'harga' => (int) preg_replace('/[^0-9]/', '', (string) $request->input('harga', '')),
            'harga_asli' => (int) preg_replace('/[^0-9]/', '', (string) $request->input('harga_asli', '')),
            'ukuran' => trim((string) $request->input('ukuran', '')),
            'kondisi' => trim((string) $request->input('kondisi', '')),
            'stok' => (int) $request->input('stok', 0),
            'unggulan' => $request->boolean('unggulan') ? 1 : 0,
        ];

        $galat = [];
        if (mb_strlen($data['nama']) < 3 || mb_strlen($data['nama']) > 150) {
            $galat[] = 'Nama produk harus antara 3 sampai 150 karakter.';
        }
        if (!Kategori::where('id', $data['kategori_id'])->exists()) {
            $galat[] = 'Kategori produk wajib dipilih.';
        }
        if (mb_strlen($data['deskripsi']) < 10 || mb_strlen($data['deskripsi']) > 5000) {
            $galat[] = 'Deskripsi produk harus antara 10 sampai 5000 karakter.';
        }
        if ($data['harga'] < 1) {
            $galat[] = 'Harga produk wajib diisi.';
        }
        if ($data['harga_asli'] < $data['harga']) {
            $galat[] = 'Harga asli tidak boleh lebih rendah dari harga jual.';
        }
        if ($data['ukuran'] === '' || mb_strlen($data['ukuran']) > 20) {
            $galat[] = 'Ukuran produk wajib diisi (maksimal 20 karakter).';
        }
        if (!in_array($data['kondisi'], self::KONDISI_OPSI, true)) {
            $galat[] = 'Kondisi produk tidak valid.';
        }
        if ($data['stok'] < 0) {
            $galat[] = 'Stok tidak boleh negatif.';
        }

        $gambarBaru = null;
        if ($request->hasFile('gambar')) {
            $hasil = $uploads->store($request->file('gambar'), 'images', 'produk-' . date('YmdHis'), 2 * 1024 * 1024);
            if (!$hasil['ok']) {
                $galat[] = $hasil['pesan'];
            } else {
                $gambarBaru = $hasil['filename'];
            }
        } elseif (!$produk) {
            $galat[] = 'Gambar produk wajib diunggah.';
        }

        if ($galat) {
            if ($gambarBaru) {
                $uploads->deleteIfExists('images', $gambarBaru);
            }
            $routeName = $produk ? 'admin.products.edit' : 'admin.products.create';
            $params = $produk ? ['id' => $produk->id] : [];

            return redirect()->route($routeName, $params)->withInput($data)->withErrors($galat);
        }

        $dasar = slug_aman($data['nama'], 155);
        if ($dasar === '') {
            $dasar = 'produk';
        }
        $slug = $dasar;
        $nomor = 2;
        $excludeId = $produk ? (int) $produk->id : 0;
        while (Produk::where('slug', $slug)->where('id', '<>', $excludeId)->exists()) {
            $slug = $dasar . '-' . $nomor;
            $nomor++;
        }

        try {
            if ($produk) {
                $gambarLama = $produk->gambar;
                $produk->update([
                    'kategori_id' => $data['kategori_id'],
                    'nama' => $data['nama'],
                    'slug' => $slug,
                    'deskripsi' => $data['deskripsi'],
                    'harga' => $data['harga'],
                    'harga_asli' => $data['harga_asli'],
                    'ukuran' => $data['ukuran'],
                    'kondisi' => $data['kondisi'],
                    'stok' => $data['stok'],
                    'gambar' => $gambarBaru ?: $gambarLama,
                    'unggulan' => $data['unggulan'],
                ]);
                if ($gambarBaru && $gambarLama !== '' && $gambarLama !== $gambarBaru) {
                    if (Produk::where('gambar', $gambarLama)->count() === 0) {
                        $uploads->deleteIfExists('images', $gambarLama);
                    }
                }

                return $this->redirectFlash('admin.products.index', 'sukses', 'Produk berhasil diperbarui.');
            }

            Produk::create([
                'kategori_id' => $data['kategori_id'],
                'nama' => $data['nama'],
                'slug' => $slug,
                'deskripsi' => $data['deskripsi'],
                'harga' => $data['harga'],
                'harga_asli' => $data['harga_asli'],
                'ukuran' => $data['ukuran'],
                'kondisi' => $data['kondisi'],
                'stok' => $data['stok'],
                'gambar' => $gambarBaru,
                'unggulan' => $data['unggulan'],
                'dibuat_pada' => now()->format('Y-m-d H:i:s'),
            ]);

            return $this->redirectFlash('admin.products.index', 'sukses', 'Produk baru berhasil ditambahkan.');
        } catch (Throwable $e) {
            if ($gambarBaru) {
                $uploads->deleteIfExists('images', $gambarBaru);
            }
            Log::error('[Rerajut Product Save Error] ' . $e->getMessage());
            $routeName = $produk ? 'admin.products.edit' : 'admin.products.create';
            $params = $produk ? ['id' => $produk->id] : [];

            return redirect()->route($routeName, $params)->withInput($data)->withErrors(['Gagal menyimpan data produk. Silakan coba lagi.']);
        }
    }
}
