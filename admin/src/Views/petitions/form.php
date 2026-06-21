<?php
use App\Layout\AdminLayout;

use App\Models\Town;

$isEdit = isset($petition);
$title = $isEdit ? '編輯提案' : '新增提案';
$action = $isEdit ? "/admin/petitions/{$petition['id']}" : '/admin/petitions/store';

$townRecords = Town::all();
$towns = array_column($townRecords, 'name');

ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-black text-slate-800"><?= $title ?></h1>
    <a href="/admin/petitions" class="text-slate-400 hover:text-slate-600 font-medium text-sm transition-colors">
        <i class="fa-solid fa-arrow-left mr-2"></i>返回列表
    </a>
</div>

<div class="bg-white rounded-3xl p-8 border border-slate-50 shadow-sm">
    <form action="<?= $action ?>" method="POST" class="space-y-6">
        <div>
            <label class="block text-sm font-black text-slate-800 mb-2">提案標題 <span class="text-red-500">*</span></label>
            <input type="text" name="title" required value="<?= htmlspecialchars($petition['title'] ?? '') ?>"
                   class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#E0F2ED] transition-shadow placeholder:text-slate-300 font-medium">
        </div>

        <div>
            <label class="block text-sm font-black text-slate-800 mb-2">提案說明 <span class="text-red-500">*</span></label>
            <textarea name="description" rows="5" required
                      class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#E0F2ED] transition-shadow placeholder:text-slate-300 font-medium leading-relaxed"><?= htmlspecialchars($petition['description'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-black text-slate-800 mb-2">所屬鄉鎮</label>
                <select name="town" class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#E0F2ED] transition-shadow font-medium cursor-pointer">
                    <option value="" <?= empty($petition['town']) ? 'selected' : '' ?>>全部地區</option>
                    <?php foreach ($towns as $t): ?>
                        <option value="<?= $t ?>" <?= ($petition['town'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-black text-slate-800 mb-2">議題分類</label>
                <select name="category" required class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#E0F2ED] transition-shadow font-medium cursor-pointer">
                    <?php 
                    $categories = ['農業與產銷', '婦幼與社區生活', '長者照顧', '青年培力與地方創生', '交通建設與微型移動', '運動與休閒', '其他綜合議題'];
                    foreach ($categories as $cat): 
                    ?>
                        <option value="<?= $cat ?>" <?= ($petition['category'] ?? '其他綜合議題') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-black text-slate-800 mb-2">目標連署人數</label>
                <input type="number" name="target_count" value="<?= htmlspecialchars((string)($petition['target_count'] ?? 50)) ?>"
                       class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#E0F2ED] transition-shadow font-medium">
            </div>

            <div>
                <label class="block text-sm font-black text-slate-800 mb-2">目前狀態</label>
                <select name="status" class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#E0F2ED] transition-shadow font-medium cursor-pointer">
                    <?php foreach (['審核中', '公開連署', '已達標', '已列管'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($petition['status'] ?? '審核中') === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <hr class="border-slate-100">

        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-[#2D7A60] hover:bg-[#1f5c48] text-white px-8 py-3 rounded-2xl font-black transition-colors shadow-sm focus:ring-4 focus:ring-[#E0F2ED]">
                <?= $isEdit ? '儲存變更' : '建立提案' ?>
            </button>
        </div>
    </form>
</div>

<?php AdminLayout::render($title, ob_get_clean() ?: '', 'petition'); ?>
