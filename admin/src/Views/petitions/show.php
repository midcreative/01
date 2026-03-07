<?php
use App\Layout\AdminLayout;

ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-black text-slate-800">提案詳情與連署名冊</h1>
    <a href="/admin/petitions" class="text-slate-400 hover:text-slate-600 font-medium text-sm transition-colors">
        <i class="fa-solid fa-arrow-left mr-2"></i>返回列表
    </a>
</div>

<!-- 提案資訊 -->
<div class="bg-white rounded-3xl p-8 border border-slate-50 shadow-sm mb-6">
    <div class="flex justify-between items-start mb-4">
        <div>
            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 text-xs font-black rounded-lg mb-3">
                <?= htmlspecialchars($petition['town'] ?? '全區') ?>
            </span>
            <span class="inline-block px-3 py-1 bg-[#E0F2ED] text-[#2D7A60] text-xs font-black rounded-lg mb-3 ml-2">
                <?= htmlspecialchars($petition['status'] ?? '審核中') ?>
            </span>
            <h2 class="font-black text-2xl text-slate-800 mb-2"><?= htmlspecialchars($petition['title']) ?></h2>
            <p class="text-slate-500 text-sm leading-relaxed"><?= nl2br(htmlspecialchars($petition['description'] ?? '')) ?></p>
        </div>
        <a href="/admin/petitions/<?= $petition['id'] ?>/edit" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-sm transition-colors">
            <i class="fa-solid fa-pen mr-1"></i> 編輯
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center mt-6">
        <div class="bg-slate-50 rounded-2xl p-4">
            <div class="text-3xl font-black text-slate-800"><?= (int)$petition['current_count'] ?></div>
            <div class="text-sm font-bold text-slate-400 mt-1">目前連署</div>
        </div>
        <div class="bg-slate-50 rounded-2xl p-4">
            <div class="text-3xl font-black text-slate-800"><?= (int)$petition['target_count'] ?></div>
            <div class="text-sm font-bold text-slate-400 mt-1">目標人數</div>
        </div>
        <div class="bg-slate-50 rounded-2xl p-4">
            <div class="text-3xl font-black text-[#66C2A5]"><?= min(100, (int)round($petition['current_count'] / max(1, $petition['target_count']) * 100)) ?>%</div>
            <div class="text-sm font-bold text-slate-400 mt-1">完成度</div>
        </div>
    </div>
</div>

<!-- 連署名冊 -->
<h3 class="font-black text-lg text-slate-800 mb-4 px-2">連署民眾名冊</h3>
<div class="bg-white rounded-3xl border border-slate-50 shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-widest font-bold">
                <th class="py-4 px-6 font-medium">LINE 用戶</th>
                <th class="py-4 px-6 font-medium w-32">鄉鎮區</th>
                <th class="py-4 px-6 font-medium w-48 hidden md:table-cell">連署時間</th>
            </tr>
        </thead>
        <tbody class="text-sm divide-y divide-slate-50">
            <?php if (empty($signatures)): ?>
                <tr>
                    <td colspan="3" class="py-8 text-center text-slate-400">目前尚無連署資料</td>
                </tr>
            <?php else: ?>
                <?php foreach ($signatures as $sig): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-3 px-6">
                        <div class="flex items-center gap-3">
                            <img src="<?= htmlspecialchars($sig['line_picture_url'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($sig['line_display_name']) . '&background=f1f5f9&color=64748b') ?>" 
                                 alt="Avatar" class="w-10 h-10 rounded-full bg-slate-100 object-cover shrink-0">
                            <div>
                                <div class="font-bold text-slate-800"><?= htmlspecialchars($sig['line_display_name']) ?></div>
                                <div class="text-xs text-slate-400 mt-0.5 truncate max-w-[150px] md:max-w-xs" title="LINE USER ID">
                                    ID: <?= htmlspecialchars($sig['line_user_id']) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6 text-slate-600 font-medium">
                        <?= htmlspecialchars($sig['town'] ?? '未提供') ?>
                    </td>
                    <td class="py-3 px-6 text-slate-400 hidden md:table-cell">
                        <?= htmlspecialchars($sig['created_at']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php AdminLayout::render('提案詳情', ob_get_clean() ?: '', 'petition'); ?>
