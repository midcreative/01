<?php

declare(strict_types=1);

require_once __DIR__ . '/admin/vendor/autoload.php';
use Dotenv\Dotenv;
use App\Config\Database;
use App\Layout\FrontLayout;

$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->safeLoad();
date_default_timezone_set('Asia/Taipei');

try {
    $pdo  = Database::getInstance();
    $jobs = $pdo->query("SELECT * FROM volunteer_jobs WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll();
} catch (\Throwable) {
    $jobs = [];
}

$appUrl = rtrim($_ENV['APP_URL'] ?? 'https://panlingyi.tw', '/');

ob_start();
?>
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#E0F2ED]">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-4">
        <a href="/" class="flex items-center gap-2">
            <div class="w-9 h-9 bg-brand-green rounded-xl flex items-center justify-center text-white font-serif font-black">ÊΩ?/div>
            <span class="font-serif font-black text-slate-800 hidden sm:block">ÊΩòÁÇ©Á¶?<span class="brand-green font-sans text-sm">?çÂ??•Ë?</span></span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
        <span class="text-sm text-slate-500 font-medium">ÂøóÂ∑•?õÂ?</span>
    </div>
</nav>

<main class="max-w-5xl mx-auto px-4 py-12">
    <header class="mb-10 text-center">
        <div class="inline-block bg-[#E0F2ED] px-3 py-1 rounded-full text-[#4A937F] text-[10px] font-black mb-4 tracking-[0.15em] uppercase">‰∏ÄËµ∑ÁÇ∫Â±èÊù±Á¨¨‰??∏Â?Ë≤¢Áçª</div>
        <h1 class="text-3xl md:text-5xl font-serif font-black text-slate-900 mb-4 leading-tight">ÂøóÂ∑•?õÂ?</h1>
        <p class="text-slate-500 max-w-xl mx-auto text-sm md:text-base leading-relaxed">?®Á??±Ê??åË??ïÔ??ØÊ??ëÊ?Â§ßÁ??ØÊ??ÇÊ≠°ËøéÂ??•Ê??©Á??çÂ?Ëæ¶ÂÖ¨ÂÆ§Á?ÂøóÂ∑•Ë°åÂ?Ôºå‰?Ëµ∑Ê∑±?ïÂ??±Á¨¨‰∏âÈÅ∏?Ä??/p>
    </header>

    <?php if (empty($jobs)): ?>
    <div class="bg-white rounded-3xl p-16 text-center text-slate-300 shadow-sm border border-slate-50">
        <i data-lucide="users" class="w-12 h-12 mx-auto mb-4 opacity-30"></i>
        <p class="text-sm">?ÆÂ??´ÁÑ°?ãÊîæ?ÑÂ?Â∑•ËÅ∑Áº∫Ô?Ë´ãÁ?ÂæåÂ??•Á???/p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($jobs as $job): ?>
        <div class="bg-white rounded-3xl p-8 border border-slate-50 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 flex flex-col">
            <div class="flex items-start justify-between mb-4">
                <h2 class="text-xl font-black text-slate-800 leading-snug"><?= htmlspecialchars($job['title']) ?></h2>
                <span class="px-3 py-1 rounded-lg text-[10px] font-black bg-[#E0F2ED] text-[#2D7A60] flex-shrink-0 ml-3">?õÂ?‰∏?/span>
            </div>
            <div class="flex gap-4 text-xs font-bold text-slate-400 mb-4">
                <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3 brand-green"></i> <?= htmlspecialchars($job['town'] ?? '?®ÈÅ∏?Ä') ?></span>
                <span class="flex items-center gap-1"><i data-lucide="users" class="w-3 h-3"></i> ?Ä <?= (int)$job['required_num'] ?> ‰∫?/span>
                <?php if ($job['deadline']): ?>
                <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> <?= htmlspecialchars($job['deadline']) ?> ?™Ê≠¢</span>
                <?php endif; ?>
            </div>
            <?php if ($job['description']): ?>
            <p class="text-sm text-slate-500 leading-relaxed mb-6 flex-grow"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
            <?php endif; ?>
            <button onclick="openApplyModal(<?= $job['id'] ?>, '<?= htmlspecialchars(addslashes($job['title'])) ?>')"
                    class="w-full py-3 bg-brand-green text-white rounded-2xl font-black text-sm hover:bg-[#57A891] transition-all">
                Á´ãÂç≥?±Â?
            </button>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<!-- Apply Modal -->
<div id="apply-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl">
        <h2 class="font-black text-xl text-slate-800 mb-2" id="modal-title">?±Â?ÂøóÂ∑•</h2>
        <p class="text-slate-400 text-sm mb-6">Â°´ÂØ´?∫Êú¨Ë≥áÊ?ÔºåÊ??ëÂ??°Âø´?áÊÇ®?ØÁπ´??/p>
        <form method="POST" action="/volunteer-apply.php" class="space-y-4">
            <input type="hidden" name="job_id" id="modal-job-id">
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">ÂßìÂ? *</label>
                <input type="text" name="name" required class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-slate-50 focus:outline-none focus:border-[#66C2A5] font-medium">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">?ªË©± *</label>
                <input type="tel" name="phone" required class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-slate-50 focus:outline-none focus:border-[#66C2A5] font-medium">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Email</label>
                <input type="email" name="email" class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-slate-50 focus:outline-none focus:border-[#66C2A5] font-medium">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Ë£úÂ?Ë™™Ê?</label>
                <textarea name="message" rows="2" class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-slate-50 focus:outline-none focus:border-[#66C2A5] resize-none"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-3 bg-brand-green text-white rounded-2xl font-black text-sm hover:bg-[#57A891] transition-all">?ÅÂá∫?±Â?</button>
                <button type="button" onclick="document.getElementById('apply-modal').classList.add('hidden')"
                        class="px-6 py-3 bg-slate-100 text-slate-600 rounded-2xl font-bold text-sm">?ñÊ?</button>
            </div>
        </form>
    </div>
</div>

<footer class="bg-white border-t border-[#E0F2ED] py-10 mt-20 text-center">
    <p class="text-slate-300 text-[9px] font-black tracking-[0.2em] uppercase">¬© 2026 PAN LING-YI IMPACT OFFICE</p>
</footer>

<script>
function openApplyModal(jobId, title) {
    document.getElementById('modal-job-id').value = jobId;
    document.getElementById('modal-title').textContent = '?±Â?Ôº? + title;
    document.getElementById('apply-modal').classList.remove('hidden');
}
</script>

<?php
$content = ob_get_clean() ?? '';
FrontLayout::render($content, [
    'title'       => 'ÂøóÂ∑•?õÂ? | ÊΩòÁÇ©Á¶??çÂ??•Ë?',
    'description' => '?†ÂÖ•ÊΩòÁÇ©Á¶ïÊ??ôËæ¶?¨ÂÆ§ÂøóÂ∑•Ë°åÂ?ÔºåÊ∑±?ïÂ??±Á¨¨‰∏âÈÅ∏?ÄÊΩÆÂ??Æ„ÄÅÊñ∞?§È??ÅÂÖß?îÈ??ÅËê¨Â∑íÈ??ÅÁ´π?∞È??ÅÊ?ÂØÆÈ???,
    'canonical'   => $appUrl . '/volunteer',
    'schema_type' => 'VolunteerWork',
    'schema_data' => ['title' => 'ÂøóÂ∑•?õÂ?', 'description' => '?†ÂÖ•ÊΩòÁÇ©Á¶ïÊ??ôËæ¶?¨ÂÆ§ÂøóÂ∑•Ë°åÂ?'],
]);
