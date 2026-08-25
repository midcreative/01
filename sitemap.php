<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Taipei');

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/admin/vendor/autoload.php')) {
    require_once __DIR__ . '/admin/vendor/autoload.php';
}
use Dotenv\Dotenv;
use App\Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->safeLoad();

header('Content-Type: application/xml; charset=utf-8');

$appUrl = rtrim($_ENV['APP_URL'] ?? 'https://panlingyi.tw', '/');

try {
    $pdo   = Database::getInstance();
    $posts = $pdo->query("SELECT slug, published_at, updated_at, created_at FROM posts WHERE is_published = 1 ORDER BY created_at DESC")->fetchAll();
} catch (\Throwable) {
    $posts = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <!-- 首頁 -->
  <url>
    <loc><?= $appUrl ?>/</loc>
    <lastmod><?= date('c') ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- 志工招募 -->
  <url>
    <loc><?= $appUrl ?>/volunteer</loc>
    <lastmod><?= date('c') ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>

  <?php foreach ($posts as $post): 
    $rawDate = $post['updated_at'] ?? $post['published_at'] ?? $post['created_at'] ?? null;
    $timestamp = $rawDate ? strtotime((string)$rawDate) : false;
    $lastMod = ($timestamp !== false && $timestamp > 0) ? date('c', $timestamp) : date('c');
  ?>
  <!-- 服務日記：<?= htmlspecialchars((string)$post['slug']) ?> -->
  <url>
    <loc><?= $appUrl ?>/post/<?= htmlspecialchars((string)$post['slug']) ?></loc>
    <lastmod><?= $lastMod ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <?php endforeach; ?>

</urlset>

