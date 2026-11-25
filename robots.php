<?php
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');
$prefix = BASE_URL;
?>
User-agent: *
Allow: /
Disallow: <?= $prefix ?>/keranjang.php
Disallow: <?= $prefix ?>/checkout.php
Disallow: <?= $prefix ?>/pesanan-sukses.php
Disallow: <?= $prefix ?>/lacak.php
Disallow: <?= $prefix ?>/pesanan-saya.php
Disallow: <?= $prefix ?>/api-pesanan.php
Disallow: <?= $prefix ?>/upload-bukti.php
Disallow: <?= $prefix ?>/langganan.php
Disallow: <?= $prefix ?>/uploads/
Disallow: <?= $prefix ?>/config/
Disallow: <?= $prefix ?>/includes/
Disallow: <?= $prefix ?>/admin/

Sitemap: <?= SITE_URL ?>/sitemap.php
