<?php
use App\Layout\AdminLayout;

$statusColors = [
    '審核中'   => 'bg-orange-50 text-orange-600',
    '公開連署' => 'bg-[#E0F2ED] text-[#2D7A60]',
    '已達標'   => 'bg-blue-50 text-blue-600',
    '已列管'   => 'bg-slate-100 text-slate-500',
];

ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-black text-slate-800">連署提案管理</h1>
    <a href="/admin/petitions/create" class="bg-[#2D7A60] hover:bg-[#1f5c48] text-white px-5 py-2.5 rounded-2xl font-black text-sm transition-colors shadow-sm focus:ring-4 focus:ring-[#E0F2ED]">
        <i class="fa-solid fa-plus mr-2"></i> 新增提案
    </a>
</div>

<div class="space-y-4">
<?php if (empty($petitions)): ?>
    <div class="bg-white rounded-3xl p-12 text-center text-slate-300 text-sm border border-slate-50">尚無連署提案</div>
<?php else: ?>
    <?php foreach ($petitions as $p): ?>
    <div class="bg-white rounded-3xl p-6 border border-slate-50 shadow-sm flex flex-col md:flex-row md:items-center gap-4">
        <div class="flex-1">
            <h3 class="font-black text-slate-800 mb-1">
                <a href="/admin/petitions/<?= $p['id'] ?>" class="hover:text-[#2D7A60] transition-colors"><?= htmlspecialchars($p['title']) ?></a>
            </h3>
            <p class="text-sm text-slate-400"><?= htmlspecialchars($p['town'] ?? '全區') ?> · <?= htmlspecialchars($p['created_at']) ?></p>
        </div>
        <div class="flex items-center gap-6">
            <!-- Progress -->
            <div class="text-center">
                <div class="text-lg font-black text-slate-800"><?= (int)$p['current_count'] ?> / <?= (int)$p['target_count'] ?></div>
                <div class="text-[10px] text-slate-400 uppercase tracking-widest">連署人數</div>
            </div>
            <!-- Status select -->
            <form method="POST" action="/admin/petitions/<?= $p['id'] ?>/status">
                <select name="status" onchange="this.form.submit()"
                        class="px-3 py-2 rounded-xl text-xs font-black border-0 <?= $statusColors[$p['status']] ?? 'bg-slate-100' ?> focus:outline-none cursor-pointer">
                    <?php foreach (array_keys($statusColors) as $s): ?>
                    <option value="<?= $s ?>" <?= $p['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            
            <div class="flex flex-col gap-2">
                <a href="/admin/petitions/<?= $p['id'] ?>/edit" class="text-slate-400 hover:text-blue-500 transition-colors p-1" title="編輯">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <form action="/admin/petitions/<?= $p['id'] ?>/delete" method="POST" onsubmit="return confirm('確定要刪除此提案嗎？相關連署紀錄也將一併刪除。');">
                    <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors p-1" title="刪除">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<?php AdminLayout::render('連署提案管理', ob_get_clean() ?: '', 'petition'); ?>
