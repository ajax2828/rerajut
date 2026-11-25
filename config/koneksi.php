<?php
require_once __DIR__ . '/config.php';

$dbHost = (string) env_val('DB_HOST', 'localhost');
$dbPort = (string) env_val('DB_PORT', '3306');
$dbName = (string) env_val('DB_NAME', 'rerajut_thrift');
$dbUser = (string) env_val('DB_USER', 'root');
$dbPass = (string) env_val('DB_PASS', '');
$dbCharset = (string) env_val('DB_CHARSET', 'utf8mb4');
$dbSocket = trim((string) env_val('DB_SOCKET', ''));

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
    $pesan = 'Koneksi database gagal. Pastikan layanan MySQL aktif dan konfigurasi file .env sudah sesuai.';
    if (APP_DEBUG) {
        $pesan .= "\nDetail: " . $e->getMessage();
    }
    exit($pesan);
}
