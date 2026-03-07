<?php

declare(strict_types=1);

namespace App\Layout;

/**
 * GEO-optimised base layout for the ADMIN panel.
 * For front-end public pages, see /src/Layout/FrontLayout.php
 */
final class AdminLayout
{
    /**
     * Render the admin shell with sidebar, navbar and content.
     *
     * @param string $title   Browser tab title
     * @param string $content HTML content to inject into main area
     * @param string $active  Active sidebar item key (e.g. 'posts')
     */
    public static function render(
        string $title,
        string $content,
        string $active = ''
    ): void {
        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — 潘炩禕 後台管理</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Noto Sans TC', sans-serif; }
        .brand { color: #66C2A5; }
        .bg-brand { background-color: #66C2A5; }
        .sidebar-item.active { background: #E0F2ED; color: #2D7A60; font-weight: 700; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-slate-100 flex flex-col shadow-sm flex-shrink-0">
        <div class="h-16 flex items-center gap-2 px-6 border-b border-slate-100">
            <div class="w-8 h-8 bg-[#66C2A5] rounded-lg flex items-center justify-center text-white font-black text-lg">潘</div>
            <div>
                <div class="font-black text-sm text-slate-800">CMS 後台</div>
                <div class="text-[9px] text-slate-400 uppercase tracking-widest">潘炩禕服務辦公室</div>
            </div>
        </div>
        <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
            <?php
            $navItems = [
                ['key' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => '儀表板',     'href' => '/admin/dashboard'],
                ['key' => 'posts',     'icon' => 'newspaper',        'label' => '服務日記',    'href' => '/admin/posts'],
                ['key' => 'stats',     'icon' => 'bar-chart-3',      'label' => '數據看板',    'href' => '/admin/stats'],
                ['key' => 'volunteer', 'icon' => 'users',             'label' => '志工招募',    'href' => '/admin/volunteers'],
                ['key' => 'petition',  'icon' => 'file-signature',    'label' => '連署提案',    'href' => '/admin/petitions'],
                ['key' => 'whitepaper','icon' => 'target',            'label' => '白皮書管理',  'href' => '/admin/whitepaper'],
                ['key' => 'categories',  'icon' => 'tags',            'label' => '主軸分類',    'href' => '/admin/categories'],
                ['key' => 'towns',       'icon' => 'map-pin',         'label' => '鄉鎮分類',    'href' => '/admin/towns'],
                ['key' => 'settings',    'icon' => 'settings',        'label' => '系統設定',    'href' => '/admin/settings'],
            ];
            foreach ($navItems as $item):
                $isActive = $active === $item['key'] ? 'sidebar-item active' : 'sidebar-item text-slate-500 hover:bg-slate-50';
            ?>
            <a href="<?= $item['href'] ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all <?= $isActive ?>">
                <i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4"></i>
                <?= $item['label'] ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="p-4 border-t border-slate-100">
            <a href="/admin/logout" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all">
                <i data-lucide="log-out" class="w-4 h-4"></i> 登出
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-100 flex items-center px-8 gap-4 shadow-sm">
            <h1 class="font-black text-lg text-slate-800"><?= htmlspecialchars($title) ?></h1>
        </header>
        <main class="flex-1 overflow-y-auto p-8">
            <?= $content ?>
        </main>
    </div>

</div>
<script>lucide.createIcons();</script>
</body>
</html>
        <?php
    }
}
