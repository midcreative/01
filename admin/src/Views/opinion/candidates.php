<?php
use App\Layout\AdminLayout;
ob_start();
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight">候選人與追蹤關鍵字</h2>
        <p class="text-sm text-slate-500 mt-1">設定系統需自動監測的政治人物及其相關別名與關聯議題。</p>
    </div>
    <button onclick="document.getElementById('add-candidate-modal').classList.remove('hidden')" class="bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-sm shadow-brand/20 hover:bg-[#5bb294] transition-colors flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4 text-white/80"></i> 新增監測對象
    </button>
</div>

<!-- Modal: 新增候選人 -->
<div id="add-candidate-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <form action="/admin/candidates/store" method="POST">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-slate-800">新增監測對象</h3>
                <button type="button" onclick="document.getElementById('add-candidate-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">姓名 <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full border-slate-200 rounded-xl focus:ring-brand/20 focus:border-brand px-4 py-2 text-sm" placeholder="例如：潘小子">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">政黨</label>
                    <input type="text" name="party" class="w-full border-slate-200 rounded-xl focus:ring-brand/20 focus:border-brand px-4 py-2 text-sm" placeholder="無黨籍">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">定位 (Type) <span class="text-red-500">*</span></label>
                    <select name="type" class="w-full border-slate-200 rounded-xl focus:ring-brand/20 focus:border-brand px-4 py-2 text-sm text-slate-700">
                        <option value="main_opponent">主要對手</option>
                        <option value="other">其他同選區候選人或名嘴</option>
                        <option value="self">自己 (通常已預設建立)</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('add-candidate-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">取消</button>
                <button type="submit" class="px-6 py-2 bg-brand text-white text-sm font-bold rounded-lg hover:bg-[#5bb294] transition-colors">確認新增</button>
            </div>
        </form>
    </div>
</div>

<div class="space-y-6">
    <?php foreach ($candidates ?? [] as $candidate): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            
            <!-- Candidate Header -->
            <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 <?= $candidate['type'] === 'self' ? 'bg-emerald-50/50' : 'bg-slate-50/50' ?>">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 <?= $candidate['type'] === 'self' ? 'bg-brand' : 'bg-slate-200 text-slate-500' ?> rounded-full flex items-center justify-center text-white font-black text-lg flex-shrink-0 shadow-inner">
                        <?= mb_substr($candidate['name'], 0, 1) ?>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($candidate['name']) ?></h3>
                            <?php if($candidate['type'] === 'self'): ?>
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-brand/10 text-brand">自己</span>
                            <?php elseif($candidate['type'] === 'main_opponent'): ?>
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-600">主要對手</span>
                            <?php endif; ?>
                            <span class="px-2 py-0.5 rounded text-xs text-slate-500 bg-white border border-slate-200"><?= htmlspecialchars($candidate['party'] ?: '無黨籍') ?></span>
                        </div>
                        <div class="text-sm text-slate-500 mt-0.5">系統建立時間: <?= substr($candidate['created_at'], 0, 10) ?></div>
                    </div>
                </div>
                
                <form action="/admin/candidates/<?= $candidate['id'] ?>/delete" method="POST" onsubmit="return confirm('確定要刪除這位監測對象及其所有關鍵字嗎？無法復原。');">
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-sm font-bold text-red-500 hover:bg-red-50 hover:text-red-600 flex items-center gap-1.5 transition-colors border border-transparent hover:border-red-100" title="刪除追蹤">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>刪除監測對象</span>
                    </button>
                </form>
            </div>

            <!-- Keywords List -->
            <div class="p-6">
                
                <h4 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    爬蟲監測關鍵字設定
                </h4>

                <div class="flex flex-wrap gap-2 mb-6">
                    <?php if (empty($candidate['keywords'])): ?>
                        <div class="text-sm text-slate-400 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">尚未設定任何追蹤關鍵字</div>
                    <?php else: ?>
                        <?php foreach ($candidate['keywords'] as $kw): ?>
                            <div class="group inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm font-medium transition-colors
                                <?php
                                    switch($kw['type']){
                                        case 'negative': echo 'bg-rose-50 border-rose-100 text-rose-700'; break;
                                        case 'issue':    echo 'bg-blue-50 border-blue-100 text-blue-700'; break;
                                        default:         echo 'bg-slate-50 border-slate-200 text-slate-700'; break;
                                    }
                                ?>">
                                
                                <span><?= htmlspecialchars($kw['keyword']) ?></span>
                                
                                <form action="/admin/candidates/keyword/<?= $kw['id'] ?>/delete" method="POST" class="inline">
                                    <button type="submit" class="text-slate-400 hover:text-red-500 focus:outline-none">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add Keyword Form -->
                <form action="/admin/candidates/keyword/store" method="POST" class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex items-end gap-4 max-w-2xl">
                    <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">新增關鍵字</label>
                        <input type="text" name="keyword" required class="w-full border-slate-200 rounded-xl focus:ring-brand/20 focus:border-brand px-4 py-2 text-sm" placeholder="例如：炩禕 (別名) 或 黑金 (爭議)">
                    </div>
                    <div class="w-48">
                        <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">詞性歸類</label>
                        <select name="type" class="w-full border-slate-200 rounded-xl focus:ring-brand/20 focus:border-brand px-4 py-2 text-sm text-slate-700">
                            <option value="alias">常用別名/錯字</option>
                            <option value="issue">綁定議題/關聯詞</option>
                            <option value="negative">負面/爭議字眼</option>
                        </select>
                    </div>
                    <button type="submit" class="w-10 h-10 rounded-xl bg-slate-800 text-white flex items-center justify-center hover:bg-slate-700 transition-colors shadow-sm flex-shrink-0">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                    </button>
                </form>

            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
AdminLayout::render($title ?? '候選人與追蹤關鍵字', $content, 'candidates');
