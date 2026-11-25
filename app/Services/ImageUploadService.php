<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageUploadService
{
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function store(UploadedFile $file, string $subDir, string $prefix, int $maxBytes = 2097152): array
    {
        if (!$file->isValid()) {
            $err = $file->getError();
            if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
                return ['ok' => false, 'pesan' => 'Ukuran file terlalu besar. Maksimal 2 MB.'];
            }

            return ['ok' => false, 'pesan' => 'File gagal diunggah. Silakan coba lagi.'];
        }

        if ((int) $file->getSize() < 1 || (int) $file->getSize() > $maxBytes) {
            return ['ok' => false, 'pesan' => 'Ukuran file maksimal ' . round($maxBytes / (1024 * 1024)) . ' MB.'];
        }

        $realPath = $file->getRealPath();
        $infoGambar = $realPath ? @getimagesize($realPath) : false;
        if ($infoGambar === false || empty($infoGambar['mime'])) {
            return ['ok' => false, 'pesan' => 'File bukan gambar yang valid. Gunakan JPG, PNG, atau WEBP.'];
        }

        $mimeGambar = (string) $infoGambar['mime'];
        $mimeFinfo = (string) ($file->getMimeType() ?: $mimeGambar);
        if (!isset(self::ALLOWED_MIMES[$mimeGambar]) || !isset(self::ALLOWED_MIMES[$mimeFinfo]) || self::ALLOWED_MIMES[$mimeGambar] !== self::ALLOWED_MIMES[$mimeFinfo]) {
            return ['ok' => false, 'pesan' => 'Format file harus JPG, PNG, atau WEBP.'];
        }

        $folder = public_path(trim($subDir, '/'));
        if (!is_dir($folder) && !@mkdir($folder, 0755, true) && !is_dir($folder)) {
            Log::error('[Rerajut Upload Error] Gagal membuat folder: ' . $folder);

            return ['ok' => false, 'pesan' => 'Folder penyimpanan belum siap. Periksa izin folder server.'];
        }
        if (!is_writable($folder)) {
            Log::error('[Rerajut Upload Error] Folder tidak bisa ditulis: ' . $folder);

            return ['ok' => false, 'pesan' => 'Folder penyimpanan tidak dapat ditulis. Periksa izin folder server.'];
        }

        $safePrefix = (string) preg_replace('/[^A-Za-z0-9-]/', '', $prefix);
        if ($safePrefix === '') {
            $safePrefix = 'file';
        }
        $namaFile = $safePrefix . '-' . bin2hex(random_bytes(5)) . '.' . self::ALLOWED_MIMES[$mimeGambar];

        try {
            $file->move($folder, $namaFile);
        } catch (\Throwable $e) {
            Log::error('[Rerajut Upload Move Error] ' . $e->getMessage());

            return ['ok' => false, 'pesan' => 'File gagal disimpan. Silakan coba lagi.'];
        }

        return ['ok' => true, 'filename' => $namaFile];
    }

    public function deleteIfExists(string $subDir, ?string $filename): void
    {
        if (!$filename) {
            return;
        }
        $path = public_path(trim($subDir, '/') . '/' . basename($filename));
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
