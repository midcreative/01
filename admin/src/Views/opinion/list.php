<?php
use App\Layout\AdminLayout;
ob_start();

$filters = $filters ?? [];
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight">輿情資料庫</h2>
        <p class="text-sm text-slate-500 mt-1">系統自動抓取且經由 AI 分析情緒的輿情清單。</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
    <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center gap-2">
        <i data-lucide="filter" class="w-4 h-4 text-brand"></i>
        <h3 class="font-bold text-slate-700 text-sm">進階篩選</h3>
    </div>
    <form action="/admin/opinion/list" method="GET" class="p-5 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">提及候選人</label>
            <select name="candidate_id" class="w-full border-slate-200 rounded-xl focus:ring-brand/20 focus:border-brand px-4 py-2 text-sm text-slate-700">
                <option value="">全部候選人</option>
                <?php foreach ($candidates as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['candidate_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">情緒判定</label>
            <select name="sentiment" class="w-full border-slate-200 rounded-xl focus:ring-brand/20 focus:border-brand px-4 py-2 text-sm text-slate-700">
                <option value="">全部情緒</option>
                <option value="positive" <?= ($filters['sentiment'] ?? '') === 'positive' ? 'selected' : '' ?>>正面</option>
                <option value="neutral"  <?= ($filters['sentiment'] ?? '') === 'neutral' ? 'selected' : '' ?>>中立</option>
                <option value="negative" <?= ($filters['sentiment'] ?? '') === 'negative' ? 'selected' : '' ?>>負面</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">來源平台</label>
            <select name="source_type" class="w-full border-slate-200 rounded-xl focus:ring-brand/20 focus:border-brand px-4 py-2 text-sm text-slate-700">
                <option value="">全部來源</option>
                <option value="news"  <?= ($filters['source_type'] ?? '') === 'news' ? 'selected' : '' ?>>新聞</option>
                <option value="fb"    <?= ($filters['source_type'] ?? '') === 'fb' ? 'selected' : '' ?>>Facebook 社團/粉專</option>
                <option value="ptt"   <?= ($filters['source_type'] ?? '') === 'ptt' ? 'selected' : '' ?>>PTT</option>
                <option value="dcard" <?= ($filters['source_type'] ?? '') === 'dcard' ? 'selected' : '' ?>>Dcard</option>
            </select>
        </div>
        <div>
            <button type="submit" class="w-full px-4 py-2 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-700 transition-colors shadow-sm">
                套用篩選
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <?php if (empty($opinions)): ?>
        <div class="p-12 text-center flex flex-col items-center">
            <i data-lucide="inbox" class="w-16 h-16 text-slate-200 mb-4"></i>
            <h3 class="text-slate-500 font-bold mb-1">目前沒有輿情紀錄</h3>
            <p class="text-sm text-slate-400">目前資料庫內尚無抓取到符合條件的討論文。</p>
        </div>
    <?php else: ?>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-32">情緒 / 平台</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">標題與摘要</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-32">關聯人</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-36">時間</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($opinions as $opinion): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        
                        <!-- 情緒與平台 -->
                        <td class="px-6 py-4 align-top">
                            <div class="flex flex-col gap-2">
                                <?php if($opinion['sentiment'] === 'positive'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-bold self-start">
                                        <i data-lucide="smile" class="w-3.5 h-3.5"></i> 正面
                                    </span>
                                <?php elseif($opinion['sentiment'] === 'negative'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded border border-rose-200 bg-rose-50 text-rose-700 text-xs font-bold self-start">
                                        <i data-lucide="frown" class="w-3.5 h-3.5"></i> 負面
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded border border-slate-200 bg-slate-100 text-slate-600 text-xs font-bold self-start">
                                        <i data-lucide="meh" class="w-3.5 h-3.5"></i> 中立
                                    </span>
                                <?php endif; ?>

                                <span class="text-xs text-slate-500 font-medium">
                                    <?= strtoupper($opinion['source_type']) ?> 
                                    <?= $opinion['source_name'] ? "({$opinion['source_name']})" : '' ?>
                                </span>
                            </div>
                        </td>

                        <!-- 標題與摘要 -->
                        <td class="px-6 py-4 align-top">
                            <a href="<?= htmlspecialchars($opinion['url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer" class="text-sm font-bold text-blue-600 hover:text-blue-800 hover:underline flex items-start gap-1 mb-1.5">
                                <?= htmlspecialchars($opinion['title']) ?>
                                <i data-lucide="external-link" class="w-3.5 h-3.5 inline text-slate-400 mt-0.5 mt-0.5 shrink-0"></i>
                            </a>
                            <?php if(!empty($opinion['content_excerpt'])): ?>
                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed bg-slate-50 p-2 rounded border border-slate-100">
                                    <?= htmlspecialchars($opinion['content_excerpt']) ?>
                                </p>
                            <?php endif; ?>
                        </td>

                        <!-- 關聯人 -->
                        <td class="px-6 py-4 align-top">
                            <?php if ($opinion['candidate_id']): ?>
                                <span class="px-2 py-1 bg-white border border-slate-200 rounded text-xs font-bold text-slate-700 shadow-sm">
                                    <?= htmlspecialchars($opinion['candidate_name']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-xs text-slate-400">-</span>
                            <?php endif; ?>
                        </td>

                        <!-- 時間 -->
                        <td class="px-6 py-4 align-top whitespace-nowrap text-xs text-slate-500">
                            <?= substr($opinion['published_at'] ?? $opinion['created_at'], 0, 16) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
AdminLayout::render($title ?? '輿情資料庫', $content, 'opinion_list');
