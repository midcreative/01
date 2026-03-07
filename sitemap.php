<?php

declare(strict_types=1);

require_once __DIR__ . '/admin/vendor/autoload.php';
use Dotenv\Dotenv;
use App\Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->load();

header('Content-Type: application/xml; charset=utf-8');

$appUrl = rtrim($_ENV['APP_URL'] ?? 'https://demo10.midcreative.com', '/');

try {
    $pdo   = Database::getInstance();
    $posts = $pdo->query("SELECT slug, updated_at, created_at FROM posts WHERE is_published = 1 ORDER BY created_at DESC")->fetchAll();
} catch (\Throwable) {
    $posts = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <!-- 首頁 -->
  <url>
    <loc><?= $appUrl ?>/</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- 志工招募 -->
  <url>
    <loc><?= $appUrl ?>/volunteer</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>

  <?php foreach ($posts as $post): ?>
  <!-- 服務日記：<?= htmlspecialchars($post['slug']) ?> -->
  <url>
    <loc><?= $appUrl ?>/post/<?= htmlspecialchars($post['slug']) ?></loc>
    <lastmod><?= htmlspecialchars($post['updated_at'] ?? $post['created_at'] ?? date('Y-m-d')) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <?php endforeach; ?>

</urlset>
