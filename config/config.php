<?php
/**
 * Konfigurasi aplikasi Rerajut Thrift.
 * Mendukung file .env maupun environment variables server (Apache, Nginx, Docker, PaaS).
 */

if (!function_exists('rerajut_load_env')) {
    function rerajut_load_env($path) {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }
            $key = trim(substr($line, 0, $pos));
            if ($key === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                continue;
            }
            if (getenv($key) !== false || array_key_exists($key, $_ENV)) {
                continue;
            }
            $val = trim(substr($line, $pos + 1));
            $len = strlen($val);
            if ($len >= 2 && (($val[0] === '"' && $val[$len - 1] === '"') || ($val[0] === "'" && $val[$len - 1] === "'"))) {
                $val = substr($val, 1, -1);
            } else {
                $commentPos = strpos($val, ' #');
                if ($commentPos !== false) {
                    $val = trim(substr($val, 0, $commentPos));
                }
            }
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
            if (function_exists('putenv')) {
                @putenv($key . '=' . $val);
            }
        }
    }
}

if (!function_exists('env_val')) {
    function env_val($key, $default = null) {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        $val = getenv($key);
        if ($val !== false) {
            return $val;
        }
        return $default;
    }
}

if (!function_exists('env_bool')) {
    function env_bool($key, $default = false) {
        $val = env_val($key, null);
        if ($val === null || $val === '') {
            return $default;
        }
        return in_array(strtolower(trim((string) $val)), ['1', 'true', 'yes', 'on'], true);
    }
}

rerajut_load_env(__DIR__ . '/../.env');

$appDebug = env_bool('APP_DEBUG', false);
define('APP_DEBUG', $appDebug);
ini_set('log_errors', '1');
ini_set('display_errors', $appDebug ? '1' : '0');
error_reporting($appDebug ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_STRICT));

$isHttps = (
    (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
    || ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443)
    || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
    || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '')) === 'on')
);

// Deteksi BASE_URL otomatis (bisa dioverride via APP_BASE_URL di .env; isi "/" untuk memaksa root)
$envBaseUrl = env_val('APP_BASE_URL', null);
if ($envBaseUrl !== null && trim((string) $envBaseUrl) !== '') {
    $baseUrl = rtrim(trim((string) $envBaseUrl), '/');
    if ($baseUrl !== '' && $baseUrl[0] !== '/') {
        $baseUrl = '/' . $baseUrl;
    }
} else {
    $baseUrl = '';
    $projectRoot = realpath(__DIR__ . '/..');
    if ($projectRoot !== false) {
        $projectRootNorm = rtrim(str_replace('\\', '/', $projectRoot), '/');
        $scriptFile = isset($_SERVER['SCRIPT_FILENAME']) ? realpath($_SERVER['SCRIPT_FILENAME']) : false;
        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

        if ($scriptFile !== false && $scriptName !== '') {
            $scriptFileNorm = str_replace('\\', '/', $scriptFile);
            if (strpos($scriptFileNorm, $projectRootNorm . '/') === 0) {
                $relScript = substr($scriptFileNorm, strlen($projectRootNorm));
                if ($relScript !== '' && substr($scriptName, -strlen($relScript)) === $relScript) {
                    $baseUrl = rtrim(substr($scriptName, 0, -strlen($relScript)), '/');
                }
            }
        }

        if ($baseUrl === '' && !empty($_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
            if ($docRoot !== false) {
                $docRootNorm = rtrim(str_replace('\\', '/', $docRoot), '/');
                if ($projectRootNorm !== $docRootNorm && strpos($projectRootNorm, $docRootNorm . '/') === 0) {
                    $baseUrl = rtrim(substr($projectRootNorm, strlen($docRootNorm)), '/');
                }
            }
        }
    }
}

define('BASE_URL', $baseUrl);

// Deteksi SITE_URL otomatis (bisa dioverride via APP_URL di .env)
$envSiteUrl = trim((string) env_val('APP_URL', ''));
if ($envSiteUrl !== '') {
    $siteUrl = rtrim($envSiteUrl, '/');
} else {
    $scheme = $isHttps ? 'https' : 'http';
    $rawHost = (string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost');
    if (strpos($rawHost, ',') !== false) {
        $rawHost = trim(explode(',', $rawHost)[0]);
    }
    if (!preg_match('/^[A-Za-z0-9.\-:\[\]]+$/', $rawHost)) {
        $rawHost = 'localhost';
    }
    $siteUrl = $scheme . '://' . $rawHost . BASE_URL;
}

define('SITE_URL', $siteUrl);
define('SITE_NAMA', (string) env_val('SITE_NAMA', 'Rerajut Thrift'));
define('SITE_TAGLINE', (string) env_val('SITE_TAGLINE', 'Baju thrift pilihan, gaya baru tanpa beli baru'));
define('SITE_DESKRIPSI', (string) env_val('SITE_DESKRIPSI', 'Rerajut Thrift menjual baju thrift pilihan dengan kualitas terkurasi, stok satuan, harga ramah mahasiswa, dan pengiriman ke seluruh Indonesia.'));
define('SITE_EMAIL', (string) env_val('SITE_EMAIL', 'halo@rerajut.id'));
define('SITE_TELEPON', (string) env_val('SITE_TELEPON', '0812-3456-7890'));
define('SITE_ALAMAT', (string) env_val('SITE_ALAMAT', 'Jl. Melati No. 21, Yogyakarta'));
define('ONGKIR', max(0, (int) env_val('ONGKIR', 15000)));
define('GRATIS_ONGKIR_MIN', max(0, (int) env_val('GRATIS_ONGKIR_MIN', 250000)));
define('BANK_NAMA', (string) env_val('BANK_NAMA', 'BCA'));
define('BANK_REKENING', (string) env_val('BANK_REKENING', '1234567890'));
define('BANK_ATAS_NAMA', (string) env_val('BANK_ATAS_NAMA', 'Rerajut Thrift'));
define('EWALLET_NAMA', (string) env_val('EWALLET_NAMA', 'GoPay, OVO, DANA'));
define('EWALLET_NOMOR', (string) env_val('EWALLET_NOMOR', SITE_TELEPON));
define('EWALLET_ATAS_NAMA', (string) env_val('EWALLET_ATAS_NAMA', 'Rerajut Thrift'));
define('APP_TIMEZONE', (string) env_val('APP_TIMEZONE', 'Asia/Jakarta'));
define('DB_TIMEZONE', (string) env_val('DB_TIMEZONE', '+07:00'));

date_default_timezone_set(APP_TIMEZONE);

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BASE_URL !== '' ? BASE_URL . '/' : '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
