<?php

namespace App\Http\Controllers;

use App\Models\Langganan;
use App\Models\PesanKontak;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        $skemaFaq = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Apakah semua produk sudah dicuci?',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ya. Setiap produk dicuci sebelum disimpan dan dikirim. Kami menggunakan detergen lembut agar warna dan bahan tetap terjaga.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Bisakah saya menukar produk?',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Penukaran dapat dilakukan jika produk tidak sesuai deskripsi. Hubungi kami maksimal 2 x 24 jam setelah barang diterima dengan menyertakan foto dan kode pesanan.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Berapa lama pesanan dikirim?',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Pesanan diproses maksimal 2 hari kerja setelah pembayaran dikonfirmasi. Estimasi pengiriman 2 sampai 5 hari kerja tergantung kota tujuan.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Apakah bisa bayar di tempat?',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'COD tersedia untuk area tertentu. Pilih metode COD saat checkout, lalu tunggu konfirmasi dari tim kami melalui WhatsApp.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Bagaimana cara mengetahui ukuran yang tepat?',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Baca bagian deskripsi dan tabel ukuran pada halaman produk. Jika masih ragu, kirim pesan dengan ukuran badanmu dan kami bantu perkirakan.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Apakah stok bisa lebih dari satu?',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Hampir semua produk hanya tersedia satu unit karena berasal dari barang thrift. Jumlah stok tertera pada halaman produk.'],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

        return view('kontak', [
            'skemaFaq' => $skemaFaq,
            'judul' => 'Hubungi ' . config('toko.nama') . ' | Pertanyaan Produk dan Pesanan',
            'deskripsi' => 'Hubungi tim ' . config('toko.nama') . ' untuk pertanyaan ukuran, kondisi produk, pembayaran, atau pengiriman baju thrift.',
            'kanonikal' => route('kontak.show'),
            'halaman' => 'kontak',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rateKey = 'kontak:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $menit = (int) ceil(RateLimiter::availableIn($rateKey) / 60);

            return redirect()->route('kontak.show')
                ->withInput()
                ->withErrors(['Terlalu banyak pesan dikirim. Silakan coba lagi dalam ' . max(1, $menit) . ' menit.']);
        }

        $data = [
            'nama' => trim((string) $request->input('nama', '')),
            'email' => trim((string) $request->input('email', '')),
            'subjek' => trim((string) $request->input('subjek', '')),
            'pesan' => trim((string) $request->input('pesan', '')),
        ];

        $galat = [];
        if (mb_strlen($data['nama']) < 3 || mb_strlen($data['nama']) > 120) {
            $galat[] = 'Nama harus antara 3 sampai 120 karakter.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 150) {
            $galat[] = 'Alamat email tidak valid.';
        }
        if ($data['subjek'] === '' || mb_strlen($data['subjek']) > 150) {
            $galat[] = 'Subjek pesan wajib diisi (maksimal 150 karakter).';
        }
        if (mb_strlen($data['pesan']) < 10 || mb_strlen($data['pesan']) > 3000) {
            $galat[] = 'Pesan harus antara 10 sampai 3000 karakter.';
        }

        if ($galat) {
            return redirect()->route('kontak.show')->withInput($data)->withErrors($galat);
        }

        PesanKontak::create([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'subjek' => $data['subjek'],
            'pesan' => $data['pesan'],
            'dibuat_pada' => now()->format('Y-m-d H:i:s'),
        ]);

        RateLimiter::hit($rateKey, 600);

        return redirect()->route('kontak.show')->with('kontak_berhasil', true);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            return redirect()->back()->with($this->flash('gagal', 'Alamat email tidak valid.'));
        }

        try {
            if (Langganan::where('email', $email)->exists()) {
                return redirect()->back()->with($this->flash('sukses', 'Email tersebut sudah terdaftar. Terima kasih.'));
            }

            Langganan::create([
                'email' => $email,
                'dibuat_pada' => now()->format('Y-m-d H:i:s'),
            ]);

            return redirect()->back()->with($this->flash('sukses', 'Pendaftaran berhasil. Info produk baru akan dikirim ke emailmu.'));
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                return redirect()->back()->with($this->flash('sukses', 'Email tersebut sudah terdaftar. Terima kasih.'));
            }
            Log::error('[Rerajut Langganan Error] ' . $e->getMessage());

            return redirect()->back()->with($this->flash('gagal', 'Pendaftaran gagal diproses. Silakan coba lagi.'));
        }
    }
}
