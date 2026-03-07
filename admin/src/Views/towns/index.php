<?php
use App\Layout\AdminLayout;
ob_start();

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';
?>
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">鄉鎮分類管理</h1>
        <p class="text-sm text-slate-500 mt-1">管理服務足跡的鄉鎮區域。刪除鄉鎮前請確認該鄉鎮下沒有文章。</p>
    </div>
    <button onclick="openModal('add_modal')" class="bg-[#66C2A5] hover:bg-[#57A891] text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-[#66C2A5]/30 flex items-center gap-2 transition-all">
        <i data-lucide="plus" class="w-4 h-4"></i> 新增鄉鎮
    </button>
</div>

<?php if ($success): ?>
<div class="mb-6 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-xl p-4 text-sm font-bold flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i> <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="mb-6 bg-red-50 text-red-600 border border-red-100 rounded-xl p-4 text-sm font-bold flex items-center gap-2">
    <i data-lucide="alert-circle" class="w-4 h-4"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-50 overflow-hidden max-w-2xl">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100 text-xs font-black text-slate-500 uppercase tracking-wider">
                <th class="py-4 px-6 w-24">排序</th>
                <th class="py-4 px-6">鄉鎮名稱</th>
                <th class="py-4 px-6 text-right w-32">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 text-sm">
            <?php foreach ($towns as $town): ?>
            <tr class="hover:bg-slate-50/50 transition-colors group">
                <td class="py-4 px-6 text-slate-400 font-bold"><?= (int)$town['sort_order'] ?></td>
                <td class="py-4 px-6 font-bold text-slate-800"><?= htmlspecialchars($town['name']) ?></td>
                <td class="py-4 px-6 text-right space-x-2">
                    <button onclick="editTown(this)"
                            data-town-id="<?= $town['id'] ?>"
                            data-town-name="<?= htmlspecialchars($town['name'], ENT_QUOTES, 'UTF-8') ?>"
                            data-town-sort="<?= (int)$town['sort_order'] ?>"
                            class="text-slate-400 hover:text-[#66C2A5] transition-colors p-2"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                    <form action="/admin/towns/<?= $town['id'] ?>/delete" method="POST" class="inline-block" onsubmit="return confirm('確定要刪除「<?= htmlspecialchars($town['name']) ?>」嗎？');">
                        <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors p-2"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($towns)): ?>
            <tr>
                <td colspan="3" class="py-8 px-6 text-center text-slate-400">尚無任何鄉鎮分類</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div id="add_modal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all" id="add_modal_content">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-800">新增鄉鎮</h3>
            <button onclick="closeModal('add_modal')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="/admin/towns/store" method="POST" class="p-6 space-y-5">
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase mb-2">鄉鎮名稱</label>
                <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#66C2A5] focus:ring-1 focus:ring-[#66C2A5] bg-slate-50 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase mb-2">排序 (數字小在前)</label>
                <input type="number" name="sort_order" value="0" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#66C2A5] focus:ring-1 focus:ring-[#66C2A5] bg-slate-50">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('add_modal')" class="flex-1 px-4 py-3 rounded-xl font-bold bg-slate-100 text-slate-600 hover:bg-slate-200">取消</button>
                <button type="submit" class="flex-1 px-4 py-3 rounded-xl font-bold bg-[#66C2A5] text-white hover:bg-[#57A891]">新增</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit_modal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all" id="edit_modal_content">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-800">編輯鄉鎮</h3>
            <button onclick="closeModal('edit_modal')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="edit_form" method="POST" class="p-6 space-y-5">
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase mb-2">鄉鎮名稱</label>
                <input type="text" name="name" id="edit_name" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#66C2A5] focus:ring-1 focus:ring-[#66C2A5] bg-slate-50 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase mb-2">排序 (數字小在前)</label>
                <input type="number" name="sort_order" id="edit_sort" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#66C2A5] focus:ring-1 focus:ring-[#66C2A5] bg-slate-50">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('edit_modal')" class="flex-1 px-4 py-3 rounded-xl font-bold bg-slate-100 text-slate-600 hover:bg-slate-200">取消</button>
                <button type="submit" class="flex-1 px-4 py-3 rounded-xl font-bold bg-[#66C2A5] text-white hover:bg-[#57A891]">儲存</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    const m = document.getElementById(id);
    const c = document.getElementById(id + '_content');
    m.classList.remove('hidden');
    setTimeout(() => { c.classList.remove('scale-95', 'opacity-0'); }, 10);
}
function closeModal(id) {
    const m = document.getElementById(id);
    const c = document.getElementById(id + '_content');
    c.classList.add('scale-95', 'opacity-0');
    setTimeout(() => { m.classList.add('hidden'); }, 200);
}

function editTown(btn) {
    const id = btn.getAttribute('data-town-id');
    const name = btn.getAttribute('data-town-name');
    const sort = btn.getAttribute('data-town-sort');

    document.getElementById('edit_form').action = '/admin/towns/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_sort').value = sort;
    openModal('edit_modal');
}
</script>

<?php
$content = ob_get_clean();
AdminLayout::render($pageTitle, $content, 'towns');
