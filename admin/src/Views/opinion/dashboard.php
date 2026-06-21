<?php
use App\Layout\AdminLayout;
ob_start();
?>

<!-- 頂部統計數據與手動更新 -->
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold text-slate-800">輿情數據總覽</h2>
    <form action="/admin/opinion/fetch" method="POST" class="m-0" id="fetchform">
        <button type="submit" onclick="this.innerHTML='<i data-lucide=\'refresh-cw\' class=\'w-4 h-4 animate-spin\'></i> 更新中...'; document.getElementById('fetchform').submit(); this.disabled=true;" class="flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm cursor-pointer">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            手動更新輿情
        </button>
    </form>
</div>

<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
    <!-- 正向聲量 -->
    <div class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="smile" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-sm text-slate-500 font-medium mb-1">近期正向聲量</div>
            <div class="text-3xl font-black text-slate-800"><?= number_format($selfStats['positive'] ?? 0) ?> <span class="text-sm text-slate-400 font-normal">篇</span></div>
        </div>
    </div>

    <!-- 負向聲量 -->
    <div class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="frown" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-sm text-slate-500 font-medium mb-1">近期負向聲量 (需注意)</div>
            <div class="text-3xl font-black text-slate-800"><?= number_format($selfStats['negative'] ?? 0) ?> <span class="text-sm text-slate-400 font-normal">篇</span></div>
        </div>
    </div>

    <!-- 中立聲量 -->
    <div class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-slate-50 text-slate-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="meh" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-sm text-slate-500 font-medium mb-1">近期中立聲量</div>
            <div class="text-3xl font-black text-slate-800"><?= number_format($selfStats['neutral'] ?? 0) ?> <span class="text-sm text-slate-400 font-normal">篇</span></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
    
    <!-- 候選人聲量 PK -->
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="w-5 h-5 text-brand"></i>
                主要對手聲量對比
            </h2>
            <a href="/admin/candidates" class="text-sm text-slate-400 hover:text-brand transition-colors"><i data-lucide="settings" class="w-4 h-4"></i></a>
        </div>
        <div class="p-6 flex-1">
            <?php if (empty($opponentStatsList)): ?>
                <div class="flex flex-col items-center justify-center text-center min-h-[250px] text-slate-400">
                    <i data-lucide="user-minus" class="w-10 h-10 mb-3 text-slate-200"></i>
                    <p class="text-sm mb-4">尚未設定主要對手，請先新增對手以進行聲量比較。</p>
                    <a href="/admin/candidates" class="px-4 py-2 bg-brand text-white text-sm font-bold rounded-lg hover:bg-[#5bb294] transition-colors inline-flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i> 前往「候選人追蹤」新增
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($opponentStatsList as $opp): ?>
                        <?php 
                            $total = array_sum($opp['stats']);
                            $posPercent = $total > 0 ? round(($opp['stats']['positive'] / $total) * 100) : 0;
                            $negPercent = $total > 0 ? round(($opp['stats']['negative'] / $total) * 100) : 0;
                            $neuPercent = $total > 0 ? round(($opp['stats']['neutral'] / $total) * 100) : 0;
                        ?>
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <h3 class="font-bold text-slate-700"><?= htmlspecialchars($opp['name']) ?> <span class="text-xs text-slate-400 font-normal ml-1">總聲量: <?= $total ?></span></h3>
                            </div>
                            <!-- 堆疊長條圖 -->
                            <div class="w-full h-4 flex rounded-full overflow-hidden bg-slate-100">
                                <?php if($total > 0): ?>
                                    <?php if($posPercent > 0): ?><div style="width: <?= $posPercent ?>%" class="bg-emerald-400" title="正面 <?= $posPercent ?>%"></div><?php endif; ?>
                                    <?php if($neuPercent > 0): ?><div style="width: <?= $neuPercent ?>%" class="bg-slate-300" title="中立 <?= $neuPercent ?>%"></div><?php endif; ?>
                                    <?php if($negPercent > 0): ?><div style="width: <?= $negPercent ?>%" class="bg-rose-400" title="負面 <?= $negPercent ?>%"></div><?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex justify-between text-xs text-slate-500 mt-1.5">
                                <span class="text-emerald-600">正面 <?= $posPercent ?>%</span>
                                <span class="text-rose-600">負面 <?= $negPercent ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 最熱門的討論串 -->
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <i data-lucide="flame" class="w-5 h-5 text-orange-500"></i>
                最新輿情動態
            </h2>
            <a href="/admin/opinion/list" class="text-sm text-brand hover:text-emerald-600 font-medium flex items-center gap-1">
                查看全部 <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="p-0 flex-1 flex flex-col">
            <?php if (empty($recentOpinions)): ?>
                <div class="flex-1 flex flex-col items-center justify-center text-center p-6 min-h-[250px]">
                    <i data-lucide="activity" class="w-12 h-12 text-slate-200 mb-4"></i>
                    <h3 class="text-slate-500 font-medium mb-2">等待外部輿情匯入</h3>
                    <p class="text-sm text-slate-400 max-w-sm">系統將會定時抓取設定的關鍵字新聞，目前的資料庫中尚無最新輿情。</p>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-slate-100">
                    <?php foreach($recentOpinions as $opinion): ?>
                    <li class="p-4 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 flex-shrink-0">
                                <?php if($opinion['sentiment'] === 'positive'): ?>
                                    <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center"><i data-lucide="smile" class="w-4 h-4"></i></div>
                                <?php elseif($opinion['sentiment'] === 'negative'): ?>
                                    <div class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center"><i data-lucide="frown" class="w-4 h-4"></i></div>
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center"><i data-lucide="meh" class="w-4 h-4"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="<?= htmlspecialchars($opinion['url'] ?? '#') ?>" target="_blank" class="text-sm font-bold text-slate-800 hover:text-brand line-clamp-2 leading-snug block mb-1">
                                    <?= htmlspecialchars($opinion['title']) ?>
                                </a>
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="font-medium"><?= htmlspecialchars($opinion['candidate_name'] ?? '未指定') ?></span>
                                    <span>&bull;</span>
                                    <span><?= strtoupper($opinion['source_type']) ?> <?= $opinion['source_name'] ? "({$opinion['source_name']})" : '' ?></span>
                                    <span>&bull;</span>
                                    <span><?= substr($opinion['published_at'] ?? $opinion['created_at'], 5, 11) ?></span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
AdminLayout::render($title ?? '輿情戰情室', $content, 'opinion_dashboard');
