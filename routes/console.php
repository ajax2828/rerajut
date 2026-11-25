<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('rerajut:cancel-expired', function () {
    $count = app(\App\Services\OrderService::class)->cancelExpiredOrders();
    $this->info("Pesanan kedaluwarsa yang dibatalkan: {$count}");
})->purpose('Batalkan pesanan non-COD yang melewati batas bayar dan kembalikan stok produk.');
