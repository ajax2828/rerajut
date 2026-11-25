<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('kategori')->count() === 0) {
            DB::table('kategori')->insert([
                ['id' => 1, 'nama' => 'Rajutan', 'slug' => 'rajutan'],
                ['id' => 2, 'nama' => 'Outer', 'slug' => 'outer'],
                ['id' => 3, 'nama' => 'Atasan', 'slug' => 'atasan'],
                ['id' => 4, 'nama' => 'Bawahan', 'slug' => 'bawahan'],
                ['id' => 5, 'nama' => 'Kemeja', 'slug' => 'kemeja'],
                ['id' => 6, 'nama' => 'Setelan', 'slug' => 'setelan'],
                ['id' => 7, 'nama' => 'Aksesoris', 'slug' => 'aksesoris'],
            ]);
        }

        if (DB::table('produk')->count() === 0) {
            $now = now()->format('Y-m-d H:i:s');
            DB::table('produk')->insert([
                ['kategori_id' => 2, 'nama' => 'Outer Rajut Ruffle Krem', 'slug' => 'outer-rajut-ruffle-krem', 'deskripsi' => 'Outer rajut panjang warna krem dengan detail ruffle di bagian depan. Bahan adem, jatuh dengan rapi, cocok untuk tampilan kasual maupun semiformal.', 'harga' => 89000, 'harga_asli' => 150000, 'ukuran' => 'M', 'kondisi' => 'Sangat Baik', 'stok' => 1, 'gambar' => 'produk-01.jpg', 'unggulan' => 1, 'dibuat_pada' => $now],
                ['kategori_id' => 7, 'nama' => 'Selendang Motif Pastel', 'slug' => 'selendang-motif-pastel', 'deskripsi' => 'Selendang berbahan lembut dengan motif pastel. Bisa dipakai sebagai penutup kepala atau pelengkap gaya harian.', 'harga' => 45000, 'harga_asli' => 85000, 'ukuran' => 'All Size', 'kondisi' => 'Baik', 'stok' => 1, 'gambar' => 'produk-02.jpg', 'unggulan' => 0, 'dibuat_pada' => $now],
                ['kategori_id' => 1, 'nama' => 'Cardigan Rajut Lime', 'slug' => 'cardigan-rajut-lime', 'deskripsi' => 'Cardigan rajut warna lime dengan kancing depan. Warna segar, rajutan rapat, dan tidak ada cacat berarti.', 'harga' => 75000, 'harga_asli' => 120000, 'ukuran' => 'M', 'kondisi' => 'Sangat Baik', 'stok' => 1, 'gambar' => 'produk-03.jpg', 'unggulan' => 1, 'dibuat_pada' => $now],
                ['kategori_id' => 7, 'nama' => 'Selendang Groovy Child', 'slug' => 'selendang-groovy-child', 'deskripsi' => 'Selendang motif warna cerah dengan tulisan Groovy Child. Bahan ringan, mudah dibentuk, dan nyaman dipakai seharian.', 'harga' => 49000, 'harga_asli' => 90000, 'ukuran' => 'All Size', 'kondisi' => 'Baik', 'stok' => 1, 'gambar' => 'produk-04.jpg', 'unggulan' => 0, 'dibuat_pada' => $now],
                ['kategori_id' => 1, 'nama' => 'Cardigan Rajut Navy', 'slug' => 'cardigan-rajut-navy', 'deskripsi' => 'Cardigan rajut navy dengan potongan V dan kancing depan. Warna netral, mudah dipadukan dengan kaus maupun kemeja.', 'harga' => 79000, 'harga_asli' => 130000, 'ukuran' => 'L', 'kondisi' => 'Sangat Baik', 'stok' => 1, 'gambar' => 'produk-05.jpg', 'unggulan' => 1, 'dibuat_pada' => $now],
                ['kategori_id' => 1, 'nama' => 'Cardigan Rajut Motif Kotak', 'slug' => 'cardigan-rajut-motif-kotak', 'deskripsi' => 'Cardigan rajut tebal bermotif kotak dengan dua kantong depan. Hangat, nyaman, dan pas untuk cuaca dingin.', 'harga' => 85000, 'harga_asli' => 140000, 'ukuran' => 'M', 'kondisi' => 'Sangat Baik', 'stok' => 1, 'gambar' => 'produk-06.jpg', 'unggulan' => 1, 'dibuat_pada' => $now],
                ['kategori_id' => 1, 'nama' => 'Sweater Lavender', 'slug' => 'sweater-lavender', 'deskripsi' => 'Sweater rajut warna lavender dengan kerah bulat. Bahan lembut, tidak gerah, dan warna masih pekat.', 'harga' => 69000, 'harga_asli' => 110000, 'ukuran' => 'M', 'kondisi' => 'Baik', 'stok' => 1, 'gambar' => 'produk-07.jpg', 'unggulan' => 0, 'dibuat_pada' => $now],
                ['kategori_id' => 3, 'nama' => 'Outer Drape Krem', 'slug' => 'outer-drape-krem', 'deskripsi' => 'Outer drape warna krem dengan potongan asimetris. Jatuh kainnya bagus dan cocok untuk gaya minimalis.', 'harga' => 65000, 'harga_asli' => 100000, 'ukuran' => 'S', 'kondisi' => 'Baik', 'stok' => 1, 'gambar' => 'produk-08.jpg', 'unggulan' => 0, 'dibuat_pada' => $now],
                ['kategori_id' => 1, 'nama' => 'Sweater V-Neck Krem', 'slug' => 'sweater-v-neck-krem', 'deskripsi' => 'Sweater rajut kerah V warna krem dengan potongan longgar. Nyaman dipakai harian dan mudah dipadukan.', 'harga' => 72000, 'harga_asli' => 115000, 'ukuran' => 'L', 'kondisi' => 'Sangat Baik', 'stok' => 1, 'gambar' => 'produk-09.jpg', 'unggulan' => 1, 'dibuat_pada' => $now],
                ['kategori_id' => 1, 'nama' => 'Sweater Pink Cropped', 'slug' => 'sweater-pink-cropped', 'deskripsi' => 'Sweater rajut pink potongan cropped dengan ujung mengkerut. Warna lembut dan bahan tidak tebal.', 'harga' => 68000, 'harga_asli' => 105000, 'ukuran' => 'S', 'kondisi' => 'Sangat Baik', 'stok' => 1, 'gambar' => 'produk-10.jpg', 'unggulan' => 0, 'dibuat_pada' => $now],
                ['kategori_id' => 4, 'nama' => 'Celana Chino Krem', 'slug' => 'celana-chino-krem', 'deskripsi' => 'Celana chino warna krem dengan potongan lurus. Bahan tebal, jahitan rapi, dan saku berfungsi dengan baik.', 'harga' => 95000, 'harga_asli' => 160000, 'ukuran' => '30', 'kondisi' => 'Sangat Baik', 'stok' => 1, 'gambar' => 'produk-11.jpg', 'unggulan' => 1, 'dibuat_pada' => $now],
                ['kategori_id' => 1, 'nama' => 'Sweater Rajut Manik', 'slug' => 'sweater-rajut-manik', 'deskripsi' => 'Sweater rajut warna krem dengan hiasan manik merah. Detail manik lengkap dan rajutan masih kuat.', 'harga' => 88000, 'harga_asli' => 145000, 'ukuran' => 'M', 'kondisi' => 'Sangat Baik', 'stok' => 1, 'gambar' => 'produk-12.jpg', 'unggulan' => 1, 'dibuat_pada' => $now],
                ['kategori_id' => 6, 'nama' => 'Set Vest dan Kemeja', 'slug' => 'set-vest-dan-kemeja', 'deskripsi' => 'Setelan vest coklat dan kemeja putih dalam satu paket. Bisa dipakai bersama atau dipisah sesuai kebutuhan.', 'harga' => 99000, 'harga_asli' => 170000, 'ukuran' => 'M', 'kondisi' => 'Sangat Baik', 'stok' => 1, 'gambar' => 'produk-13.jpg', 'unggulan' => 1, 'dibuat_pada' => $now],
                ['kategori_id' => 5, 'nama' => 'Kemeja Stripe Bordir', 'slug' => 'kemeja-stripe-bordir', 'deskripsi' => 'Kemeja stripe biru dengan bordir bunga di bagian depan. Bahan ringan, cocok untuk acara santai maupun kerja.', 'harga' => 79000, 'harga_asli' => 125000, 'ukuran' => 'L', 'kondisi' => 'Baik', 'stok' => 1, 'gambar' => 'produk-14.jpg', 'unggulan' => 0, 'dibuat_pada' => $now],
            ]);
        }

        if (!DB::table('users')->where('username', 'admin')->exists()) {
            DB::table('users')->insert([
                'username' => 'admin',
                'password' => '$2y$10$DZYF1lyBh2bqv3Vplmf9duLf5uVfm.N4ZOLRki4/GxOmN7hPZb/Za',
                'nama' => 'Administrator Rerajut',
                'dibuat_pada' => now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
