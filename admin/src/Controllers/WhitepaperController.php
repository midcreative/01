<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Layout\AdminLayout;
use App\Models\Whitepaper;

/**
 * Admin CRUD for 行動白皮書 (Whitepaper Pillars).
 */
final class WhitepaperController extends BaseController
{
    public function __construct(private readonly Auth $auth) {}

    /** List all pillars. */
    public function index(): void
    {
        $this->auth->requireAuth();
        $pillars = Whitepaper::all();

        ob_start(); ?>
        <div class="flex items-center justify-between mb-6">
            <p class="text-slate-400 text-sm">共 <?= count($pillars) ?> 個核心支柱</p>
            <a href="/admin/whitepaper/create" class="px-5 py-2.5 bg-[#66C2A5] text-white rounded-2xl text-sm font-black hover:bg-[#57A891] transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> 新增核心支柱
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-50 overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-400 text-xs uppercase tracking-widest">
                    <tr>
                        <th class="text-left px-6 py-4 font-black w-24">顯示排序</th>
                        <th class="text-left px-4 py-4 font-black">支柱標題</th>
                        <th class="text-left px-4 py-4 font-black hidden md:table-cell">分類標籤</th>
                        <th class="text-left px-4 py-4 font-black">狀態</th>
                        <th class="px-4 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($pillars as $pillar): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-400 text-center"><?= $pillar['sort_order'] ?></td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-100 <?= htmlspecialchars($pillar['theme_color']) ?>">
                                    <i data-lucide="<?= htmlspecialchars($pillar['icon_name']) ?>" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($pillar['title']) ?></div>
                                    <div class="text-xs text-slate-400 hidden lg:block truncate max-w-xs"><?= htmlspecialchars($pillar['subtitle']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-slate-500 hidden md:table-cell">
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black <?= htmlspecialchars($pillar['theme_color']) ?> bg-slate-50 border border-slate-100">
                                <?= htmlspecialchars($pillar['category_tag']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <?php if ($pillar['is_active']): ?>
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black bg-[#E0F2ED] text-[#2D7A60]">顯示中</span>
                            <?php else: ?>
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black bg-slate-100 text-slate-400">已隱藏</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4 flex items-center gap-2 justify-end">
                            <a href="/admin/whitepaper/<?= $pillar['id'] ?>/edit" class="p-2 text-slate-400 hover:text-[#66C2A5] transition-colors">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form method="POST" action="/admin/whitepaper/<?= $pillar['id'] ?>/delete" onsubmit="return confirm('確定刪除此支柱？前台與白皮書連署可能受影響。')">
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pillars)): ?>
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-300 text-sm">目前沒有任何核心支柱，點擊右上角新增</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        AdminLayout::render('白皮書管理', ob_get_clean() ?: '', 'whitepaper');
    }

    /** Show create form. */
    public function create(): void
    {
        $this->auth->requireAuth();
        ob_start();
        $this->renderForm(null);
        AdminLayout::render('新增核心支柱', ob_get_clean() ?: '', 'whitepaper');
    }

    /** Handle POST store. */
    public function store(): void
    {
        $this->auth->requireAuth();
        Whitepaper::create($this->buildDataFromPost());
        $this->redirect('/admin/whitepaper');
    }

    /** Show edit form. */
    public function edit(int $id): void
    {
        $this->auth->requireAuth();
        $pillar = Whitepaper::findById($id) ?: $this->redirect('/admin/whitepaper');
        ob_start();
        $this->renderForm($pillar);
        AdminLayout::render('編輯核心支柱', ob_get_clean() ?: '', 'whitepaper');
    }

    /** Handle POST update. */
    public function update(int $id): void
    {
        $this->auth->requireAuth();
        Whitepaper::update($id, $this->buildDataFromPost());
        $this->redirect('/admin/whitepaper');
    }

    /** Handle POST delete. */
    public function destroy(int $id): void
    {
        $this->auth->requireAuth();
        Whitepaper::delete($id);
        $this->redirect('/admin/whitepaper');
    }

    /** Build data array from $_POST. */
    private function buildDataFromPost(): array
    {
        return [
            'title'         => $this->postString('title'),
            'subtitle'      => $this->postString('subtitle'),
            'category_tag'  => $this->postString('category_tag'),
            'icon_name'     => $this->postString('icon_name', 'target'),
            'theme_color'   => $this->postString('theme_color', 'text-[#66C2A5]'),
            'description'   => $this->postString('description'),
            'bullet_points' => $_POST['bullet_points'] ?? '', // Expecting clean text with line breaks
            'sort_order'    => $this->postInt('sort_order', 0),
            'is_active'     => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    /** Render shared create/edit form. */
    private function renderForm(?array $pillar): void
    {
        $isEdit = $pillar !== null;
        $action = $isEdit ? "/admin/whitepaper/{$pillar['id']}" : '/admin/whitepaper/store';
        
        $colors = [
            'brand-green' => '【主視覺綠】brand-green',
            'text-[#66C2A5]' => '【薄荷綠】text-[#66C2A5]',
            'text-blue-500' => '【寧靜藍】text-blue-500',
            'text-orange-500' => '【活力橘】text-orange-500',
            'text-rose-500' => '【溫暖粉】text-rose-500',
            'text-indigo-500' => '【沉穩紫】text-indigo-500',
            'text-slate-900' => '【深夜黑】text-slate-900',
        ];
        ?>
        <form method="POST" action="<?= $action ?>" class="space-y-6 max-w-4xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">支柱標題 (Title) *</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($pillar['title'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white focus:outline-none focus:border-[#66C2A5] focus:ring-2 focus:ring-[#66C2A5]/20 text-slate-800 font-medium"
                           placeholder="例如：農漁牧產銷發展">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">副標題 (Subtitle) *</label>
                    <input type="text" name="subtitle" required value="<?= htmlspecialchars($pillar['subtitle'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white focus:outline-none focus:border-[#66C2A5] focus:ring-2 focus:ring-[#66C2A5]/20 text-slate-800 font-medium"
                           placeholder="例如：打破盤商束縛，讓屏東好貨賣得更有尊嚴">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">分類標籤 (Category Tag)</label>
                    <input type="text" name="category_tag" value="<?= htmlspecialchars($pillar['category_tag'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white focus:outline-none focus:border-[#66C2A5] text-slate-800"
                           placeholder="例如：產業升級">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">圖標名稱 (Lucide Icon)</label>
                    <input type="text" name="icon_name" value="<?= htmlspecialchars($pillar['icon_name'] ?? 'target') ?>"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white focus:outline-none focus:border-[#66C2A5] text-slate-800"
                           placeholder="例如：sprout, briefcase, smile...">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">視覺主題色 (Tailwind Color)</label>
                    <select name="theme_color" class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5]">
                        <?php foreach ($colors as $color => $label): ?>
                        <option value="<?= $color ?>" <?= ($pillar['theme_color'] ?? 'text-[#66C2A5]') === $color ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">顯示排序</label>
                    <input type="number" name="sort_order" value="<?= (int)($pillar['sort_order'] ?? 0) ?>"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5]">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">理念說明（引言區）</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 font-medium focus:outline-none focus:border-[#66C2A5] resize-none"
                              placeholder="例如：「我們不僅要種得好，更要賣得準...」"><?= htmlspecialchars($pillar['description'] ?? '') ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">核心重點項目（每行一點）</label>
                    <textarea name="bullet_points" rows="5"
                              class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5] resize-none"
                              placeholder="建置區域型冷鏈中心&#10;打造屏東優選直銷品牌..."><?= htmlspecialchars($pillar['bullet_points'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Publish toggle -->
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" <?= ($pillar['is_active'] ?? 1) ? 'checked' : '' ?>
                       class="w-5 h-5 rounded accent-[#66C2A5]">
                <span class="font-bold text-slate-700">在前台行動白皮書中顯示</span>
            </label>

            <div class="flex items-center gap-3 mt-8">
                <button type="submit" class="px-8 py-3 bg-[#66C2A5] text-white rounded-2xl font-black text-sm hover:bg-[#57A891] transition-all">
                    <?= $isEdit ? '儲存變更' : '新增核心支柱' ?>
                </button>
                <a href="/admin/whitepaper" class="px-6 py-3 bg-slate-100 text-slate-600 rounded-2xl font-bold text-sm hover:bg-slate-200 transition-all">取消</a>
            </div>
        </form>
        <?php
    }
}
