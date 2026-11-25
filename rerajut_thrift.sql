-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 20 Nov 2026 pada 16.00
-- Versi server: 8.0.46-0ubuntu0.24.04.5
-- Versi PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rerajut_thrift`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int NOT NULL,
  `nama` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama`, `slug`) VALUES
(1, 'Rajutan', 'rajutan'),
(2, 'Outer', 'outer'),
(3, 'Atasan', 'atasan'),
(4, 'Bawahan', 'bawahan'),
(5, 'Kemeja', 'kemeja'),
(6, 'Setelan', 'setelan'),
(7, 'Aksesoris', 'aksesoris');

-- --------------------------------------------------------

--
-- Struktur dari tabel `langganan`
--

CREATE TABLE `langganan` (
  `id` int NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int NOT NULL,
  `kode` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telepon` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provinsi` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_pos` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `metode_bayar` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` int NOT NULL,
  `ongkir` int NOT NULL,
  `total` int NOT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Menunggu Pembayaran',
  `bukti_bayar` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `batas_bayar` datetime DEFAULT NULL,
  `dibayar_pada` datetime DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id`, `kode`, `nama`, `email`, `telepon`, `alamat`, `kota`, `provinsi`, `kode_pos`, `catatan`, `metode_bayar`, `subtotal`, `ongkir`, `total`, `status`, `bukti_bayar`, `batas_bayar`, `dibayar_pada`, `dibuat_pada`) VALUES
(3, 'RJT-20261119-8C036', 'asda', 'asfa@gmail.com', '0824141145121', 'asdsada asfsafa asfafa', 'dasd', 'dasda', '51212', 'sacfsa', 'COD', 95000, 15000, 110000, 'Diproses', NULL, NULL, NULL, '2026-11-19 22:24:32'),
(4, 'RJT-20261119-B7C81', 'azam', 'sales@sales.com', '081241215141', 'asfas awsfsafa awrfasfr', 'asfafa', 'asfa', '21411', '', 'Transfer Bank', 99000, 15000, 114000, 'Dibatalkan', NULL, '2026-11-20 22:28:32', NULL, '2026-11-19 22:28:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan_item`
--

CREATE TABLE `pesanan_item` (
  `id` int NOT NULL,
  `pesanan_id` int NOT NULL,
  `produk_id` int NOT NULL,
  `nama_produk` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ukuran` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga` int NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pesanan_item`
--

INSERT INTO `pesanan_item` (`id`, `pesanan_id`, `produk_id`, `nama_produk`, `ukuran`, `harga`, `jumlah`, `subtotal`) VALUES
(3, 3, 11, 'Celana Chino Krem', '30', 95000, 1, 95000),
(4, 4, 13, 'Set Vest dan Kemeja', 'M', 99000, 1, 99000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesan_kontak`
--

CREATE TABLE `pesan_kontak` (
  `id` int NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subjek` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int NOT NULL,
  `kategori_id` int NOT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(170) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga` int NOT NULL,
  `harga_asli` int NOT NULL,
  `ukuran` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kondisi` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stok` int NOT NULL DEFAULT '1',
  `gambar` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unggulan` tinyint(1) NOT NULL DEFAULT '0',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `kategori_id`, `nama`, `slug`, `deskripsi`, `harga`, `harga_asli`, `ukuran`, `kondisi`, `stok`, `gambar`, `unggulan`, `dibuat_pada`) VALUES
(1, 2, 'Outer Rajut Ruffle Krem', 'outer-rajut-ruffle-krem', 'Outer rajut panjang warna krem dengan detail ruffle di bagian depan. Bahan adem, jatuh dengan rapi, cocok untuk tampilan kasual maupun semiformal.', 89000, 150000, 'M', 'Sangat Baik', 1, 'produk-01.jpg', 1, '2026-11-19 21:47:41'),
(2, 7, 'Selendang Motif Pastel', 'selendang-motif-pastel', 'Selendang berbahan lembut dengan motif pastel. Bisa dipakai sebagai penutup kepala atau pelengkap gaya harian.', 45000, 85000, 'All Size', 'Baik', 1, 'produk-02.jpg', 0, '2026-11-19 21:47:41'),
(3, 1, 'Cardigan Rajut Lime', 'cardigan-rajut-lime', 'Cardigan rajut warna lime dengan kancing depan. Warna segar, rajutan rapat, dan tidak ada cacat berarti.', 75000, 120000, 'M', 'Sangat Baik', 1, 'produk-03.jpg', 1, '2026-11-19 21:47:41'),
(4, 7, 'Selendang Groovy Child', 'selendang-groovy-child', 'Selendang motif warna cerah dengan tulisan Groovy Child. Bahan ringan, mudah dibentuk, dan nyaman dipakai seharian.', 49000, 90000, 'All Size', 'Baik', 1, 'produk-04.jpg', 0, '2026-11-19 21:47:41'),
(5, 1, 'Cardigan Rajut Navy', 'cardigan-rajut-navy', 'Cardigan rajut navy dengan potongan V dan kancing depan. Warna netral, mudah dipadukan dengan kaus maupun kemeja.', 79000, 130000, 'L', 'Sangat Baik', 1, 'produk-05.jpg', 1, '2026-11-19 21:47:41'),
(6, 1, 'Cardigan Rajut Motif Kotak', 'cardigan-rajut-motif-kotak', 'Cardigan rajut tebal bermotif kotak dengan dua kantong depan. Hangat, nyaman, dan pas untuk cuaca dingin.', 85000, 140000, 'M', 'Sangat Baik', 1, 'produk-06.jpg', 1, '2026-11-19 21:47:41'),
(7, 1, 'Sweater Lavender', 'sweater-lavender', 'Sweater rajut warna lavender dengan kerah bulat. Bahan lembut, tidak gerah, dan warna masih pekat.', 69000, 110000, 'M', 'Baik', 1, 'produk-07.jpg', 0, '2026-11-19 21:47:41'),
(8, 3, 'Outer Drape Krem', 'outer-drape-krem', 'Outer drape warna krem dengan potongan asimetris. Jatuh kainnya bagus dan cocok untuk gaya minimalis.', 65000, 100000, 'S', 'Baik', 1, 'produk-08.jpg', 0, '2026-11-19 21:47:41'),
(9, 1, 'Sweater V-Neck Krem', 'sweater-v-neck-krem', 'Sweater rajut kerah V warna krem dengan potongan longgar. Nyaman dipakai harian dan mudah dipadukan.', 72000, 115000, 'L', 'Sangat Baik', 1, 'produk-09.jpg', 1, '2026-11-19 21:47:41'),
(10, 1, 'Sweater Pink Cropped', 'sweater-pink-cropped', 'Sweater rajut pink potongan cropped dengan ujung mengkerut. Warna lembut dan bahan tidak tebal.', 68000, 105000, 'S', 'Sangat Baik', 1, 'produk-10.jpg', 0, '2026-11-19 21:47:41'),
(11, 4, 'Celana Chino Krem', 'celana-chino-krem', 'Celana chino warna krem dengan potongan lurus. Bahan tebal, jahitan rapi, dan saku berfungsi dengan baik.', 95000, 160000, '30', 'Sangat Baik', 0, 'produk-11.jpg', 1, '2026-11-19 21:47:41'),
(12, 1, 'Sweater Rajut Manik', 'sweater-rajut-manik', 'Sweater rajut warna krem dengan hiasan manik merah. Detail manik lengkap dan rajutan masih kuat.', 88000, 145000, 'M', 'Sangat Baik', 1, 'produk-12.jpg', 1, '2026-11-19 21:47:41'),
(13, 6, 'Set Vest dan Kemeja', 'set-vest-dan-kemeja', 'Setelan vest coklat dan kemeja putih dalam satu paket. Bisa dipakai bersama atau dipisah sesuai kebutuhan.', 99000, 170000, 'M', 'Sangat Baik', 0, 'produk-13.jpg', 1, '2026-11-19 21:47:41'),
(14, 5, 'Kemeja Stripe Bordir', 'kemeja-stripe-bordir', 'Kemeja stripe biru dengan bordir bunga di bagian depan. Bahan ringan, cocok untuk acara santai maupun kerja.', 79000, 125000, 'L', 'Baik', 1, 'produk-14.jpg', 0, '2026-11-19 21:47:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `dibuat_pada`) VALUES
(1, 'admin', '$2y$10$DZYF1lyBh2bqv3Vplmf9duLf5uVfm.N4ZOLRki4/GxOmN7hPZb/Za', 'Administrator Rerajut', '2026-11-19 22:33:37');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `langganan`
--
ALTER TABLE `langganan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indeks untuk tabel `pesanan_item`
--
ALTER TABLE `pesanan_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_item_pesanan` (`pesanan_id`);

--
-- Indeks untuk tabel `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_produk_kategori` (`kategori_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `langganan`
--
ALTER TABLE `langganan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pesanan_item`
--
ALTER TABLE `pesanan_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pesanan_item`
--
ALTER TABLE `pesanan_item`
  ADD CONSTRAINT `fk_item_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
