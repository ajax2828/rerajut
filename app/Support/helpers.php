<?php

if (!function_exists('rupiah')) {
    function rupiah($angka): string
    {
        return 'Rp ' . number_format((int) $angka, 0, ',', '.');
    }
}

if (!function_exists('tanggal_wib')) {
    function tanggal_wib($waktu, bool $denganWib = true): string
    {
        if ($waktu === null || trim((string) $waktu) === '') {
            return '-';
        }
        $ts = strtotime((string) $waktu);
        if ($ts === false) {
            return '-';
        }

        return date('d-m-Y H:i', $ts) . ($denganWib ? ' WIB' : '');
    }
}

if (!function_exists('slug_aman')) {
    function slug_aman($teks, int $maxLen = 160): string
    {
        $teks = strtolower(trim((string) $teks));
        $teks = (string) preg_replace('/[^a-z0-9]+/', '-', $teks);
        $teks = trim($teks, '-');
        if ($maxLen > 0 && strlen($teks) > $maxLen) {
            $teks = rtrim(substr($teks, 0, $maxLen), '-');
        }

        return $teks;
    }
}
