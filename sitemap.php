<?php
require_once __DIR__ . '/includes/fungsi.php';

header('Content-Type: application/xml; charset=utf-8');

$produkList = $pdo->query('SELECT slug, dibuat_pada FROM produk ORDER BY id ASC')->fetchAll();
$halamanStatis = [
    ['lokasi' => SITE_URL . '/', 'prioritas' => '1.0', 'ubah' => date('Y-m-d')],
    ['lokasi' => SITE_URL . '/produk.php', 'prioritas' => '0.9', 'ubah' => date('Y-m-d')],
    ['lokasi' => SITE_URL . '/tentang.php', 'prioritas' => '0.6', 'ubah' => date('Y-m-d')],
    ['lokasi' => SITE_URL . '/kontak.php', 'prioritas' => '0.6', 'ubah' => date('Y-m-d')],
    ['lokasi' => SITE_URL . '/lacak.php', 'prioritas' => '0.4', 'ubah' => date('Y-m-d')],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($halamanStatis as $hal): ?>
<url>
<loc><?= e($hal['lokasi']) ?></loc>
<lastmod><?= e($hal['ubah']) ?></lastmod>
<changefreq>weekly</changefreq>
<priority><?= e($hal['prioritas']) ?></priority>
</url>
<?php endforeach; ?>
<?php foreach ($produkList as $p): ?>
<url>
<loc><?= e(SITE_URL . '/detail.php?slug=' . $p['slug']) ?></loc>
<lastmod><?= e(date('Y-m-d', strtotime($p['dibuat_pada']))) ?></lastmod>
<changefreq>weekly</changefreq>
<priority>0.8</priority>
</url>
<?php endforeach; ?>
</urlset>
