<?php
require_once __DIR__ . '/config.php';

if (!function_exists('env_first')) {
    function env_first(array $keys, $default = '') {
        foreach ($keys as $key) {
            $val = env_val($key, null);
            if ($val !== null && $val !== '') {
                return (string) $val;
            }
        }
        return (string) $default;
    }
}

$dbHost = '';
$dbPort = '';
$dbName = '';
$dbUser = '';
$dbPass = null;

// Mendukung DATABASE_URL / MYSQL_URL (format mysql://user:pass@host:port/dbname)
$dbUrl = env_first(['DATABASE_URL', 'MYSQL_URL'], '');
if ($dbUrl !== '') {
    $parsed = parse_url($dbUrl);
    if (is_array($parsed)) {
        if (!empty($parsed['host'])) {
            $dbHost = (string) $parsed['host'];
        }
        if (!empty($parsed['port'])) {
            $dbPort = (string) $parsed['port'];
        }
        if (!empty($parsed['path'])) {
            $dbName = ltrim((string) $parsed['path'], '/');
        }
        if (isset($parsed['user'])) {
            $dbUser = rawurldecode((string) $parsed['user']);
        }
        if (isset($parsed['pass'])) {
            $dbPass = rawurldecode((string) $parsed['pass']);
        }
    }
}

// Mendukung penamaan standar PHP Native, Laravel (DB_DATABASE/DB_USERNAME/DB_PASSWORD), dan Docker/MySQL
$dbHost = env_first(['DB_HOST', 'MYSQL_HOST', 'MYSQLHOST'], $dbHost !== '' ? $dbHost : 'localhost');
$dbPort = env_first(['DB_PORT', 'MYSQL_PORT', 'MYSQLPORT'], $dbPort !== '' ? $dbPort : '3306');
$dbName = env_first(['DB_NAME', 'DB_DATABASE', 'MYSQL_DATABASE', 'MYSQLDATABASE'], $dbName !== '' ? $dbName : 'rerajut_thrift');
$dbUser = env_first(['DB_USER', 'DB_USERNAME', 'MYSQL_USER', 'MYSQLUSER'], $dbUser !== '' ? $dbUser : 'root');

foreach (['DB_PASS', 'DB_PASSWORD', 'MYSQL_PASSWORD', 'MYSQLPASSWORD'] as $passKey) {
    $val = env_val($passKey, null);
    if ($val !== null) {
        $dbPass = (string) $val;
        break;
    }
}
if ($dbPass === null) {
    $dbPass = '';
}

$dbCharset = env_first(['DB_CHARSET'], 'utf8mb4');
$dbSocket = trim(env_first(['DB_SOCKET'], ''));

if (!preg_match('/^[0-9]+$/', $dbPort)) {
    $dbPort = '3306';
}

try {
    if ($dbSocket !== '') {
        $dsn = sprintf('mysql:unix_socket=%s;dbname=%s;charset=%s', $dbSocket, $dbName, $dbCharset);
    } else {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $dbHost, $dbPort, $dbName, $dbCharset);
    }
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    if (preg_match('/^[+-](?:0[0-9]|1[0-4]):[0-5][0-9]$/', DB_TIMEZONE)) {
        $pdo->exec("SET time_zone = '" . DB_TIMEZONE . "'");
    }
} catch (PDOException $e) {
    error_log('[Rerajut DB Error] ' . $e->getMessage());
    http_response_code(500);

    $acceptJson = strpos((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false;
    $isApi = basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === 'api-pesanan.php';
    if ($acceptJson || $isApi) {
        header('Content-Type: application/json; charset=utf-8');
        $payload = ['error' => 'Koneksi database gagal.', 'ditemukan' => false];
        if (APP_DEBUG) {
            $payload['detail'] = $e->getMessage();
        }
        exit(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    header('Content-Type: text/plain; charset=utf-8');
    $pesan = 'Koneksi database gagal. Pastikan layanan MySQL aktif dan konfigurasi environment database sudah sesuai.';
    if (APP_DEBUG) {
        $pesan .= "\nDetail: " . $e->getMessage();
    }
    exit($pesan);
}
