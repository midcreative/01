<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Config\Database;
use App\Layout\AdminLayout;

/**
 * Admin dashboard — shows summary counts for all modules.
 */
final class DashboardController extends BaseController
{
    public function __construct(private readonly Auth $auth) {}

    public function index(): void
    {
        $this->auth->requireAuth();
        $pdo = Database::getInstance();

        $counts = [
            'posts'       => (int) $pdo->query('SELECT COUNT(*) FROM posts WHERE is_published = 1')->fetchColumn(),
            'drafts'      => (int) $pdo->query('SELECT COUNT(*) FROM posts WHERE is_published = 0')->fetchColumn(),
            'volunteers'  => (int) $pdo->query('SELECT COUNT(*) FROM volunteer_jobs WHERE is_active = 1')->fetchColumn(),
            'petitions'   => (int) $pdo->query('SELECT COUNT(*) FROM petitions')->fetchColumn(),
            'applications'=> (int) $pdo->query("SELECT COUNT(*) FROM volunteer_applications WHERE status = '待審核'")->fetchColumn(),
        ];

        ob_start();
        ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-10">
            <?php
            $cards = [
                ['label' => '已發布日記',   'value' => $counts['posts'],        'color' => 'bg-[#E0F2ED] text-[#2D7A60]',  'icon' => 'newspaper'],
                ['label' => '草稿',         'value' => $counts['drafts'],       'color' => 'bg-slate-100 text-slate-500',   'icon' => 'file-text'],
                ['label' => '招募中志工職缺','value' => $counts['volunteers'],   'color' => 'bg-blue-50 text-blue-600',     'icon' => 'users'],
                ['label' => '連署提案',     'value' => $counts['petitions'],    'color' => 'bg-orange-50 text-orange-600', 'icon' => 'file-signature'],
            ];
            foreach ($cards as $c): ?>
            <div class="bg-white rounded-3xl p-6 border border-slate-50 shadow-sm flex flex-col gap-3">
                <div class="w-10 h-10 <?= $c['color'] ?> rounded-2xl flex items-center justify-center">
                    <i data-lucide="<?= $c['icon'] ?>" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-800"><?= $c['value'] ?></div>
                    <div class="text-xs text-slate-400 font-bold mt-0.5"><?= $c['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($counts['applications'] > 0): ?>
        <div class="bg-orange-50 border border-orange-100 rounded-2xl px-5 py-4 flex items-center gap-3 mb-6">
            <i data-lucide="bell" class="w-5 h-5 text-orange-500"></i>
            <span class="text-sm font-bold text-orange-700">有 <?= $counts['applications'] ?> 筆志工報名待審核</span>
            <a href="/admin/volunteers" class="ml-auto text-xs font-black text-orange-600 hover:underline">前往審核 →</a>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-3xl p-6 border border-slate-50 shadow-sm">
            <h2 class="font-black text-sm text-slate-500 uppercase tracking-widest mb-4">快速操作</h2>
            <div class="flex flex-wrap gap-3">
                <a href="/admin/posts/create" class="px-5 py-3 bg-[#66C2A5] text-white rounded-2xl text-sm font-black hover:bg-[#57A891] transition-all flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> 新增服務日記
                </a>
                <a href="/admin/volunteers/create" class="px-5 py-3 bg-blue-500 text-white rounded-2xl text-sm font-black hover:bg-blue-600 transition-all flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> 新增志工職缺
                </a>
                <a href="/admin/stats" class="px-5 py-3 bg-slate-100 text-slate-700 rounded-2xl text-sm font-black hover:bg-slate-200 transition-all flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i> 更新數據看板
                </a>
            </div>
        </div>
        <?php
        AdminLayout::render('儀表板', ob_get_clean() ?: '', 'dashboard');
    }
}
