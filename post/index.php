<?php

declare(strict_types=1);

require_once __DIR__ . '/../admin/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Models\Post;
use App\Layout\FrontLayout;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../admin');
$dotenv->safeLoad();
date_default_timezone_set('Asia/Taipei');

$slug = htmlspecialchars(trim((string)($_GET['slug'] ?? '')));
if (!$slug) {
    header('Location: /');
    exit;
}

try {
    $post = Post::findBySlug($slug);
} catch (\Throwable $e) {
    error_log('Post detail error: ' . $e->getMessage());
    $post = false;
}

if (!$post) {
    http_response_code(404);
    echo '<h1>404 — 找不到此篇日記</h1><a href="/">返回首頁</a>';
    exit;
}

$appUrl    = rtrim($_ENV['APP_URL'] ?? 'https://panlingyi.tw', '/');
$canonical = $appUrl . '/post/' . $post['slug'];

$badgeClass = $post['category_color'] ?? 'bg-slate-100 text-slate-500';

ob_start();
?>
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#E0F2ED]">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-4">
        <a href="/" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
            <div class="w-9 h-9 bg-brand-green rounded-xl flex items-center justify-center text-white font-serif font-black">潘</div>
            <span class="font-serif font-black text-slate-800 hidden sm:block">潘炩禕 <span class="brand-green font-sans text-sm">服務日記</span></span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
        <span class="text-sm text-slate-500 font-medium truncate max-w-xs"><?= htmlspecialchars($post['category_name'] ?? '未分類') ?></span>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4 py-12 md:py-20">

    <!-- Header -->
    <header class="mb-10">
        <div class="flex flex-wrap items-center gap-2 mb-4 text-xs font-bold">
            <span class="px-3 py-1 rounded-full <?= $badgeClass ?>"><?= htmlspecialchars($post['category_name'] ?? '未分類') ?></span>
            <span class="text-slate-400 flex items-center gap-1">
                <i data-lucide="map-pin" class="w-3 h-3 brand-green"></i> <?= htmlspecialchars($post['town_name'] ?? '未指定') ?>
            </span>
            <span class="text-slate-400">·</span>
            <time class="text-slate-400" datetime="<?= htmlspecialchars($post['published_at'] ?? '') ?>">
                <?= htmlspecialchars($post['published_at'] ?? '') ?>
            </time>
        </div>
        <h1 class="text-3xl md:text-4xl font-serif font-black text-slate-900 leading-tight mb-6">
            <?= htmlspecialchars($post['title']) ?>
        </h1>
        <?php if ($post['excerpt']): ?>
        <div class="border-l-4 border-brand-green pl-5 py-1 mb-8">
            <p class="text-slate-500 text-base leading-relaxed italic font-medium">
                「<?= htmlspecialchars($post['excerpt']) ?>」
            </p>
        </div>
        <?php endif; ?>
    </header>

    <!-- Cover image -->
    <?php if ($post['cover_image']): ?>
    <figure class="rounded-3xl overflow-hidden mb-10">
        <img src="<?= htmlspecialchars($post['cover_image']) ?>"
             alt="<?= htmlspecialchars($post['title']) ?>"
             class="w-full object-cover max-h-[500px]">
    </figure>
    <?php endif; ?>

    <!-- Content -->
    <div class="prose prose-slate max-w-none text-base leading-relaxed text-slate-700">
        <?= $post['content'] /* Already sanitized HTML from Quill */ ?>
    </div>

    <!-- Footer meta -->
    <div class="mt-12 pt-8 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-brand-green rounded-xl flex items-center justify-center text-white font-serif font-black">潘</div>
            <div>
                <div class="font-bold text-slate-800 text-sm">潘炩禕</div>
                <div class="text-xs text-slate-400">屏東縣第三選區服務辦公室</div>
            </div>
        </div>
        <a href="/" class="text-sm brand-green font-black flex items-center gap-1 hover:gap-3 transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> 返回服務日記列表
        </a>
    </div>
</main>

<footer class="bg-white border-t border-[#E0F2ED] py-10 mt-12 text-center">
    <p class="text-slate-300 text-[9px] font-black tracking-[0.2em] uppercase">© 2026 PAN LING-YI IMPACT OFFICE</p>
</footer>

<?php
$content = ob_get_clean() ?? '';
FrontLayout::render($content, [
    'title'       => htmlspecialchars($post['title']) . ' | 潘炩禕 服務日記',
    'description' => mb_substr($post['excerpt'] ?? $post['title'], 0, 155),
    'canonical'   => $canonical,
    'schema_type' => 'Article',
    'schema_data' => $post,
]);
