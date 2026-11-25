<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderService
{
    public const STATUS_LIST = [
        'Menunggu Pembayaran',
        'Pembayaran Diproses',
        'Diproses',
        'Dikirim',
        'Selesai',
        'Dibatalkan',
    ];

    public const PAYMENT_METHODS = [
        'Transfer Bank',
        'E-Wallet',
        'COD',
    ];

    private static bool $checkedExpired = false;

    public function shippingCost(int $subtotal): int
    {
        return $subtotal >= (int) config('toko.gratis_ongkir_min', 250000)
            ? 0
            : (int) config('toko.ongkir', 15000);
    }

    public function generateOrderCode(): string
    {
        for ($i = 0; $i < 6; $i++) {
            $kode = 'RJT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            if (!Pesanan::where('kode', $kode)->exists()) {
                return $kode;
            }
        }

        throw new \RuntimeException('Gagal membuat kode pesanan unik. Silakan coba lagi.');
    }

    public function cancelExpiredOrders(): int
    {
        if (self::$checkedExpired) {
            return 0;
        }
        self::$checkedExpired = true;

        try {
            $ids = DB::table('pesanan')
                ->where('status', 'Menunggu Pembayaran')
                ->whereNull('bukti_bayar')
                ->whereNotNull('batas_bayar')
                ->where('batas_bayar', '<', now()->format('Y-m-d H:i:s'))
                ->limit(20)
                ->pluck('id')
                ->all();

            if (!$ids) {
                return 0;
            }

            return (int) DB::transaction(function () use ($ids) {
                $dibatalkan = 0;
                foreach ($ids as $id) {
                    $row = DB::table('pesanan')->where('id', (int) $id)->lockForUpdate()->first();
                    if (!$row || $row->status !== 'Menunggu Pembayaran' || !empty($row->bukti_bayar)) {
                        continue;
                    }
                    if (empty($row->batas_bayar) || strtotime((string) $row->batas_bayar) >= time()) {
                        continue;
                    }
                    $items = DB::table('pesanan_item')->where('pesanan_id', (int) $id)->get();
                    foreach ($items as $item) {
                        DB::table('produk')
                            ->where('id', (int) $item->produk_id)
                            ->increment('stok', (int) $item->jumlah);
                    }
                    DB::table('pesanan')
                        ->where('id', (int) $id)
                        ->update(['status' => 'Dibatalkan']);
                    $dibatalkan++;
                }

                return $dibatalkan;
            });
        } catch (Throwable $e) {
            Log::error('[Rerajut Auto-Cancel Error] ' . $e->getMessage());

            return 0;
        }
    }

    public function updateStatus(int $pesananId, string $statusBaru): array
    {
        if ($pesananId < 1 || !in_array($statusBaru, self::STATUS_LIST, true)) {
            return ['ok' => false, 'pesan' => 'Status pesanan tidak valid.'];
        }

        try {
            return DB::transaction(function () use ($pesananId, $statusBaru) {
                $pesanan = DB::table('pesanan')->where('id', $pesananId)->lockForUpdate()->first();
                if (!$pesanan) {
                    return ['ok' => false, 'pesan' => 'Pesanan tidak ditemukan.'];
                }

                $statusLama = (string) $pesanan->status;
                if ($statusLama === $statusBaru) {
                    return ['ok' => true, 'pesan' => 'Status pesanan tetap ' . $statusBaru . '.'];
                }

                $items = DB::table('pesanan_item')->where('pesanan_id', $pesananId)->get();

                if ($statusLama !== 'Dibatalkan' && $statusBaru === 'Dibatalkan') {
                    foreach ($items as $item) {
                        DB::table('produk')
                            ->where('id', (int) $item->produk_id)
                            ->increment('stok', (int) $item->jumlah);
                    }
                } elseif ($statusLama === 'Dibatalkan' && $statusBaru !== 'Dibatalkan') {
                    foreach ($items as $item) {
                        $prod = DB::table('produk')->where('id', (int) $item->produk_id)->lockForUpdate()->first();
                        if (!$prod || (int) $prod->stok < (int) $item->jumlah) {
                            throw new \RuntimeException(
                                'Pesanan tidak dapat diaktifkan kembali karena stok produk "' . $item->nama_produk . '" sudah habis atau tidak mencukupi.'
                            );
                        }
                        DB::table('produk')
                            ->where('id', (int) $item->produk_id)
                            ->where('stok', '>=', (int) $item->jumlah)
                            ->decrement('stok', (int) $item->jumlah);
                    }
                }

                DB::table('pesanan')->where('id', $pesananId)->update(['status' => $statusBaru]);

                $catatanStok = '';
                if ($statusLama !== 'Dibatalkan' && $statusBaru === 'Dibatalkan') {
                    $catatanStok = ' Stok produk telah dikembalikan.';
                } elseif ($statusLama === 'Dibatalkan' && $statusBaru !== 'Dibatalkan') {
                    $catatanStok = ' Stok produk telah dikurangi kembali.';
                }

                return ['ok' => true, 'pesan' => 'Status pesanan berhasil diperbarui menjadi ' . $statusBaru . '.' . $catatanStok];
            });
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'pesan' => $e->getMessage()];
        } catch (Throwable $e) {
            Log::error('[Rerajut Status Error] ' . $e->getMessage());

            return ['ok' => false, 'pesan' => 'Gagal memperbarui status pesanan. Silakan coba lagi.'];
        }
    }

    public function rejectProof(int $pesananId, ImageUploadService $uploads): array
    {
        try {
            $result = DB::transaction(function () use ($pesananId) {
                $pesanan = DB::table('pesanan')->where('id', $pesananId)->lockForUpdate()->first();
                if (!$pesanan || empty($pesanan->bukti_bayar)) {
                    return ['ok' => false, 'pesan' => 'Bukti pembayaran tidak ditemukan.'];
                }
                if ($pesanan->metode_bayar === 'COD') {
                    return ['ok' => false, 'pesan' => 'Pesanan COD tidak memiliki bukti pembayaran.'];
                }
                if ($pesanan->status === 'Dibatalkan' || $pesanan->status === 'Selesai') {
                    return ['ok' => false, 'pesan' => 'Bukti tidak dapat ditolak pada status ' . $pesanan->status . '.'];
                }

                $batasBaru = (!empty($pesanan->batas_bayar) && strtotime((string) $pesanan->batas_bayar) > time())
                    ? (string) $pesanan->batas_bayar
                    : now()->addDay()->format('Y-m-d H:i:s');

                $buktiLama = (string) $pesanan->bukti_bayar;
                DB::table('pesanan')->where('id', $pesananId)->update([
                    'bukti_bayar' => null,
                    'dibayar_pada' => null,
                    'status' => 'Menunggu Pembayaran',
                    'batas_bayar' => $batasBaru,
                ]);

                return [
                    'ok' => true,
                    'bukti_lama' => $buktiLama,
                    'pesan' => 'Bukti pembayaran ditolak dan dihapus. Pembeli dapat mengunggah bukti baru hingga ' . tanggal_wib($batasBaru) . '.',
                ];
            });

            if (!empty($result['ok']) && !empty($result['bukti_lama'])) {
                $uploads->deleteIfExists('uploads/bukti', $result['bukti_lama']);
            }

            return $result;
        } catch (Throwable $e) {
            Log::error('[Rerajut Reject Proof Error] ' . $e->getMessage());

            return ['ok' => false, 'pesan' => 'Gagal menolak bukti pembayaran.'];
        }
    }
}
