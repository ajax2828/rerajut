<?php

return [
    'nama' => (string) env('SITE_NAMA', env('APP_NAME', 'Rerajut Thrift')),
    'tagline' => (string) env('SITE_TAGLINE', 'Baju thrift pilihan, gaya baru tanpa beli baru'),
    'deskripsi' => (string) env('SITE_DESKRIPSI', 'Rerajut Thrift menjual baju thrift pilihan dengan kualitas terkurasi, stok satuan, harga ramah mahasiswa, dan pengiriman ke seluruh Indonesia.'),
    'email' => (string) env('SITE_EMAIL', 'halo@rerajut.id'),
    'telepon' => (string) env('SITE_TELEPON', '0812-3456-7890'),
    'alamat' => (string) env('SITE_ALAMAT', 'Jl. Melati No. 21, Yogyakarta'),
    'ongkir' => max(0, (int) env('ONGKIR', 15000)),
    'gratis_ongkir_min' => max(0, (int) env('GRATIS_ONGKIR_MIN', 250000)),
    'bank_nama' => (string) env('BANK_NAMA', 'BCA'),
    'bank_rekening' => (string) env('BANK_REKENING', '1234567890'),
    'bank_atas_nama' => (string) env('BANK_ATAS_NAMA', 'Rerajut Thrift'),
    'ewallet_nama' => (string) env('EWALLET_NAMA', 'GoPay, OVO, DANA'),
    'ewallet_nomor' => (string) env('EWALLET_NOMOR', env('SITE_TELEPON', '0812-3456-7890')),
    'ewallet_atas_nama' => (string) env('EWALLET_ATAS_NAMA', 'Rerajut Thrift'),
];
