<?php

declare(strict_types=1);

// ?�?�?� Bootstrap ?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�
require_once __DIR__ . '/admin/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;
use App\Models\Post;
use App\Models\Town;
use App\Models\Whitepaper;
use App\Layout\FrontLayout;

$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->safeLoad();
date_default_timezone_set('Asia/Taipei');

// ?�?�?� Read data from DB ?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�
try {
    $pdo   = Database::getInstance();

    $posts = Post::allPublished();
    $towns = Town::all();
    $whitepapers = Whitepaper::allActive();

    $petitionsStmt = $pdo->prepare("SELECT * FROM petitions WHERE status IN ('?��???��', '已�?�?) ORDER BY created_at DESC");
    $petitionsStmt->execute();
    $petitions = $petitionsStmt->fetchAll();

    // Fetch up to 5 latest signatures for each petition
    $signaturesMap = [];
    if (!empty($petitions)) {
        $petitionIds = array_column($petitions, 'id');
        $placeholders = implode(',', array_fill(0, count($petitionIds), '?'));
        
        // Use a window function to get top 5 latest signatures per petition (Requires MySQL 8+ / MariaDB 10.2+)
        // As a fallback for older versions, we fetch all and filter in PHP
        $sigStmt = $pdo->prepare("
            SELECT petition_id, line_display_name, line_picture_url, created_at
            FROM petition_signatures
            WHERE petition_id IN ($placeholders)
            ORDER BY created_at DESC
        ");
        $sigStmt->execute($petitionIds);
        $allSignatures = $sigStmt->fetchAll();
        
        foreach ($allSignatures as $sig) {
            $pid = $sig['petition_id'];
            if (!isset($signaturesMap[$pid])) {
                $signaturesMap[$pid] = [];
            }
            if (count($signaturesMap[$pid]) < 5) {
                $signaturesMap[$pid][] = $sig;
            }
        }
    }

    $stats = $pdo->query('SELECT * FROM stats ORDER BY sort_order ASC')->fetchAll();
    $statsMap = [];
    foreach ($stats as $s) {
        $statsMap[$s['stat_key']] = $s;
    }

    $settingsRaw = $pdo->query('SELECT * FROM settings')->fetchAll();
    $settingsDb = [];
    foreach ($settingsRaw as $row) {
        $settingsDb[$row['setting_key']] = $row['setting_value'];
    }

} catch (\Throwable $e) {
    error_log('Front index error: ' . $e->getMessage());
    $posts       = [];
    $towns       = [];
    $whitepapers = [];
    $statsMap    = [];
    $settingsDb  = [];
}

// Default settings fallbacks
$defaultSettings = [
    'HERO_TAG' => '屏東縣議員第三選區參選人',
    'HERO_HOME_TITLE_1' => '聽見地方的心跳，',
    'HERO_HOME_TITLE_2' => '讓服務的溫度延續。',
    'HERO_ISSUES_TITLE_1' => '迎接下個階段的託付，',
    'HERO_ISSUES_TITLE_2' => '設計出更好的屏東。',
    'HERO_FEEDBACK_TITLE_1' => '我們需要你的志願，',
    'HERO_FEEDBACK_TITLE_2' => '翻轉家鄉的未來。',
    'HERO_CTA_SHOW' => '1',
    'HERO_CTA_TEXT' => '參與行動實踐',
    'HERO_BG_IMAGE' => '',
];
$settingsMap = array_merge($defaultSettings, $settingsDb);

// ?�?�?� Render ?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�?�
ob_start();
?>

<!-- 導航�?-->
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#E0F2ED]">
    <div class="max-w-7xl mx-auto px-4 h-16 md:h-20 flex items-center justify-between">
        <div class="flex items-center gap-2 md:gap-3 py-2 cursor-pointer" onclick="switchView('home')">
            <div class="w-10 h-10 md:w-12 md:h-12 bg-brand-green rounded-xl flex items-center justify-center text-white shadow-lg shadow-[#66C2A5]/20 rotate-3 hover:rotate-0 transition-all">
                <span class="font-serif text-xl md:text-2xl font-bold">�?/span>
            </div>
            <div class="flex flex-col text-left">
                <span class="font-serif text-lg md:text-2xl font-black tracking-tighter text-slate-800">
                    潘炩�?<span class="brand-green font-sans text-sm md:text-lg font-bold ml-0.5">?��??��?</span>
                </span>
                <span class="text-[8px] md:text-[9px] font-bold text-slate-400 uppercase tracking-[0.1em] md:tracking-[0.2em]">Pingtung District 3 Impact Site</span>
            </div>
        </div>
        <div class="hidden lg:flex items-center gap-8 text-sm font-bold tracking-wide">
            <button onclick="switchView('home')"     id="nav-home"     class="nav-btn brand-green border-b-2 border-brand-green pb-1">?�鎮足跡</button>
            <button onclick="switchView('issues')"   id="nav-issues"   class="nav-btn text-slate-500 hover:brand-green">行�??�皮??/button>
            <button onclick="switchView('feedback')" id="nav-feedback" class="nav-btn text-slate-500 hover:brand-green">??��實�?�?/button>
            <a href="/volunteer.php" class="nav-btn text-slate-500 hover:brand-green">志工?��?</a>
        </div>
        <div class="flex items-center gap-2">
            <a href="tel:081234567" class="bg-brand-green text-white px-4 md:px-6 py-2 md:py-2.5 rounded-full text-xs md:text-sm font-black shadow-lg shadow-[#66C2A5]/30 flex items-center gap-2 hover:bg-[#57A891] transition-all">
                <i data-lucide="message-square" class="w-3.5 h-3.5 md:w-4 md:h-4"></i> <span class="hidden xs:inline">線�??��?</span>
            </a>
            <button onclick="toggleMobileMenu()" class="lg:hidden p-2 text-slate-600 hover:text-brand-green transition-colors focus:outline-none">
                <i data-lucide="menu" id="mobile-menu-icon" class="w-6 h-6"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden lg:hidden flex-col bg-white border-t border-[#E0F2ED] shadow-xl absolute top-full left-0 w-full pb-4 z-40 transition-all origin-top">
        <button onclick="switchView('home'); toggleMobileMenu()" id="mobile-nav-home" class="mobile-nav-btn py-4 text-brand-green font-bold border-b border-slate-50 hover:bg-slate-50 w-full text-center">?�鎮足跡</button>
        <button onclick="switchView('issues'); toggleMobileMenu()" id="mobile-nav-issues" class="mobile-nav-btn py-4 text-slate-600 font-bold border-b border-slate-50 hover:bg-slate-50 w-full text-center">行�??�皮??/button>
        <button onclick="switchView('feedback'); toggleMobileMenu()" id="mobile-nav-feedback" class="mobile-nav-btn py-4 text-slate-600 font-bold border-b border-slate-50 hover:bg-slate-50 w-full text-center">??��實�?�?/button>
        <a href="/volunteer.php" class="py-4 text-slate-600 font-bold border-b border-slate-50 hover:bg-slate-50 block w-full text-center">志工?��?</a>
    </div>
</nav>

<!-- 主內�?-->
<main class="max-w-7xl mx-auto px-4 py-8 md:py-12 text-left">
    <?php
    $heroBg = $settingsMap['HERO_BG_IMAGE'];
    $bgStyle = $heroBg ? "background-image: url('" . htmlspecialchars($heroBg) . "'); background-size: cover; background-position: center;" : "";
    $headerClass = $heroBg ? "mb-8 md:mb-16 relative rounded-[2.5rem] overflow-hidden shadow-[0_10px_40px_-15px_rgba(102,194,165,0.3)] border border-[#E0F2ED]/50 aspect-[1/1] sm:aspect-[16/9] md:aspect-[2.5/1] flex items-center justify-center p-6" : "mb-8 md:mb-16 text-center";
    ?>
    <header class="<?= $headerClass ?>" style="<?= $bgStyle ?>">
        <?php if ($heroBg && (!isset($settingsMap['HERO_BG_OVERLAY']) || $settingsMap['HERO_BG_OVERLAY'] == '1')): ?>
        <div class="absolute inset-0 bg-white/80 backdrop-blur-md z-0"></div>
        <?php endif; ?>
        
        <div class="relative z-10 flex flex-col items-center justify-center w-full h-full">
            <div id="main-tag" class="inline-block bg-[#E0F2ED] px-3 py-1 rounded-full text-[#4A937F] text-[9px] md:text-[10px] font-black mb-4 tracking-[0.15em] uppercase" <?= empty($settingsMap['HERO_TAG']) ? 'style="display:none;"' : '' ?>>
                <?= htmlspecialchars($settingsMap['HERO_TAG']) ?>
            </div>
            <h1 id="main-title" class="text-3xl md:text-6xl font-serif font-black text-slate-900 mb-6 md:mb-8 leading-tight px-2 text-center" <?= empty($settingsMap['HERO_HOME_TITLE_1']) && empty($settingsMap['HERO_HOME_TITLE_2']) ? 'style="display:none;"' : '' ?>>
                <?= htmlspecialchars($settingsMap['HERO_HOME_TITLE_1']) ?><br class="hidden md:block">
                <span class="brand-green font-sans italic opacity-90"><?= htmlspecialchars($settingsMap['HERO_HOME_TITLE_2']) ?></span>
            </h1>
            
            <?php if ($settingsMap['HERO_CTA_SHOW'] == '1'): ?>
            <div id="hero-cta" class="<?= (empty($settingsMap['HERO_HOME_TITLE_1']) && empty($settingsMap['HERO_HOME_TITLE_2'])) ? 'absolute bottom-6 md:bottom-12 left-1/2 -translate-x-1/2 w-max' : 'mt-2 md:mt-4' ?>">
                <button onclick="switchView('feedback')" class="bg-[#4A937F] hover:bg-[#2D7A60] text-white px-8 md:px-10 py-3.5 md:py-4 rounded-full font-black text-sm shadow-xl shadow-[#4A937F]/30 transition-all flex items-center justify-center gap-2.5 mx-auto hover:!scale-105 active:!scale-95 group border border-[#4A937F]">
                    <i data-lucide="pen-tool" class="w-4 h-4 text-white group-hover:rotate-12 transition-transform"></i> 
                    <span class="tracking-wide"><?= htmlspecialchars($settingsMap['HERO_CTA_TEXT']) ?></span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- 首�?：�??�足�?-->
    <div id="view-home" class="view-content">
        <!-- ?�鎮?��? -->
        <section class="mb-8 overflow-x-auto whitespace-nowrap pb-4 no-scrollbar -mx-4 px-4 text-center">
            <div class="flex flex-nowrap items-center justify-center gap-2.5">
                <button onclick="filterTown(this,'?�部?��?')" class="town-btn active-town px-6 py-2 rounded-full text-xs font-bold bg-brand-green text-white shadow-md">?�部?��?</button>
                <?php foreach ($towns as $town): if($town['name'] === '?�部?��?') continue; ?>
                <button onclick="filterTown(this,'<?= htmlspecialchars($town['name']) ?>')" class="town-btn px-6 py-2 rounded-full text-xs font-bold bg-white text-slate-400 border border-slate-100"><?= htmlspecialchars($town['name']) ?></button>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ?��??�板（�? DB ?��??��?�?-->
        <section class="mb-10 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 text-center">
            <?php foreach ($statsMap as $key => $s): ?>
            <div class="bg-white p-5 rounded-[1.5rem] border border-slate-50 shadow-sm flex flex-col items-center justify-center group hover:border-[#66C2A5]/20 transition-all">
                <i data-lucide="<?= htmlspecialchars($s['icon_name'] ?? 'layers') ?>" class="<?= htmlspecialchars($s['icon_color'] ?? 'brand-green') ?> mb-2 w-5 h-5 opacity-70"></i>
                <span class="text-slate-400 text-[8px] font-black tracking-widest mb-1 uppercase"><?= htmlspecialchars($s['stat_label']) ?></span>
                <h3 class="text-xl font-black text-slate-800"><?= (int)$s['stat_value'] ?> <span class="text-[10px] font-bold text-slate-400"><?= htmlspecialchars($s['stat_unit']) ?></span></h3>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- ?��??��??�表 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8" id="service-list">
            <?php if (empty($posts)): ?>
            <div class="md:col-span-2 py-20 text-center text-slate-300 text-sm">尚未?��??�日記�?請透�?後台?��???/div>
            <?php endif; ?>

            <?php foreach ($posts as $post):
                $badgeClass = $post['category_color'] ?? 'bg-slate-50 text-slate-500 border-slate-100';
            ?>
            <article class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-50 shadow-sm hover:shadow-lg transition-all flex flex-col group"
                     data-town="<?= htmlspecialchars($post['town_name'] ?? '') ?>">
                <?php if ($post['cover_image']): ?>
                <div class="aspect-[16/9] bg-[#F4F8F7] overflow-hidden">
                    <img src="<?= htmlspecialchars($post['cover_image']) ?>"
                         alt="<?= htmlspecialchars($post['title']) ?>"
                         loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <?php else: ?>
                <div class="aspect-[16/9] md:aspect-[16/10] bg-[#F4F8F7] relative p-6 flex items-center justify-center">
                    <span class="absolute top-4 left-4 px-4 py-1 rounded-full text-[9px] font-black uppercase tracking-[0.1em] border <?= $badgeClass ?>">
                        <?= htmlspecialchars($post['category_name'] ?? '?��?�?) ?>
                    </span>
                </div>
                <?php endif; ?>
                <div class="p-6 md:p-10 flex flex-col flex-grow">
                    <div class="flex items-center gap-2 text-[9px] font-black text-slate-400 mb-3 tracking-wider">
                        <i data-lucide="map-pin" class="brand-green w-3 h-3"></i>
                        <?= htmlspecialchars($post['town_name'] ?? '?��?�?) ?>
                        <span class="mx-1 opacity-30">/</span>
                        <?= htmlspecialchars($post['published_at'] ?? '') ?>
                    </div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-800 mb-4 group-hover:brand-green transition-colors font-serif leading-snug">
                        <?= htmlspecialchars($post['title']) ?>
                    </h2>
                    <?php if ($post['excerpt']): ?>
                    <div class="bg-[#F9FBFA] p-5 rounded-2xl mb-6 flex-grow border border-slate-50">
                        <p class="text-xs md:text-sm text-slate-500 leading-relaxed italic font-medium">
                            ???= htmlspecialchars($post['excerpt']) ?>??
                        </p>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center justify-end">
                        <a href="/post/<?= htmlspecialchars($post['slug']) ?>" class="brand-green font-black text-xs flex items-center gap-1 hover:gap-3 transition-all">
                            ?��??��? <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="view-issues" class="view-content hidden-view animate-in fade-in max-w-5xl mx-auto text-left">
        <div class="text-center mb-10 md:mb-16">
            <h2 class="text-3xl md:text-5xl font-serif font-black text-slate-900 mb-4 leading-tight text-center">行�??�皮??/h2>
            <p class="text-slate-500 text-sm md:text-lg font-medium max-w-2xl mx-auto text-center leading-relaxed text-balance">
                以數?�設計未來�??��??��??��?付。這是?�們為屏東第�??��?定義?�核心支?��?
            </p>
        </div>

        <div class="space-y-8 md:space-y-12 text-left">
            <?php foreach ($whitepapers as $w): ?>
            <?php if ($w['theme_color'] === 'text-slate-900'): // ?�別?��?深色�?? ?>
            <div class="bg-slate-900 rounded-[2.5rem] md:rounded-[3.5rem] p-8 md:p-16 text-white shadow-2xl relative overflow-hidden group text-left">
                <div class="absolute -right-10 -bottom-10 opacity-10 group-hover:scale-110 transition-transform duration-700 text-right">
                    <i data-lucide="<?= htmlspecialchars($w['icon_name']) ?>" class="w-64 h-64 text-white text-right"></i>
                </div>
                <div class="relative z-10 text-left">
                    <span class="inline-block bg-white/10 <?= htmlspecialchars($w['theme_color'] === 'brand-green' ? 'brand-green' : $w['theme_color']) ?> text-[10px] font-black px-4 py-1.5 rounded-full mb-6 tracking-widest uppercase text-left border border-white/5">
                        <?= htmlspecialchars($w['category_tag']) ?>
                    </span>
                    <h3 class="text-3xl md:text-5xl font-serif font-black mb-6 text-left leading-tight"><?= nl2br(htmlspecialchars($w['title'] . ($w['subtitle'] ? "：\n" . $w['subtitle'] : ''))) ?></h3>
                    <p class="text-white/60 max-w-2xl mb-10 text-sm md:text-lg leading-relaxed text-left font-medium"><?= nl2br(htmlspecialchars($w['description'])) ?></p>
                    <a href="/volunteer.php" class="inline-block px-10 py-4 bg-[#66C2A5] text-white rounded-2xl font-black text-sm shadow-xl hover:scale-105 transition-all text-center">?��??�照??��</a>
                </div>
            </div>
            <?php else: // 一?�樣�??>
            <div class="bg-white rounded-[2.5rem] md:rounded-[3.5rem] overflow-hidden border border-slate-50 card-shadow flex flex-col md:flex-row group transition-all hover:-translate-y-1 text-left">
                <div class="md:w-1/3 bg-[#F4F8F7] p-8 md:p-12 flex flex-col items-center justify-center text-center group-hover:bg-[#E0F2ED] transition-colors">
                    <div class="w-16 h-16 bg-white rounded-3xl flex items-center justify-center <?= htmlspecialchars($w['theme_color'] === 'brand-green' ? 'brand-green' : $w['theme_color']) ?> shadow-lg mb-4 text-center">
                        <i data-lucide="<?= htmlspecialchars($w['icon_name']) ?>" class="w-10 h-10 text-center"></i>
                    </div>
                    <span class="text-[10px] font-black <?= htmlspecialchars($w['theme_color'] === 'brand-green' ? 'brand-green' : $w['theme_color']) ?> tracking-[0.2em] uppercase mb-2 text-center text-balance">
                        <?= htmlspecialchars($w['category_tag']) ?>
                    </span>
                    <h3 class="text-xl md:text-2xl font-serif font-black text-slate-800 text-center"><?= htmlspecialchars($w['title']) ?></h3>
                </div>
                <div class="md:w-2/3 p-8 md:p-16 text-left">
                    <h4 class="text-lg font-bold text-slate-800 mb-4 text-left"><?= htmlspecialchars($w['subtitle']) ?></h4>
                    <?php if ($w['description']): ?>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6 font-medium italic text-left">
                        <?= nl2br(htmlspecialchars($w['description'])) ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($w['bullet_points']): ?>
                    <ul class="space-y-3 mb-8 text-sm text-left">
                        <?php foreach (explode("\n", trim($w['bullet_points'])) as $point): 
                            if (trim($point)): ?>
                        <li class="flex items-start gap-3 font-bold text-slate-700 text-left">
                            <i data-lucide="check-circle-2" class="<?= htmlspecialchars($w['theme_color'] === 'brand-green' ? 'brand-green' : $w['theme_color']) ?> w-4 h-4 flex-shrink-0 text-left"></i> 
                            <?= htmlspecialchars(trim($point)) ?>
                        </li>
                        <?php endif; endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
            
            <?php if (empty($whitepapers)): ?>
            <div class="text-center text-slate-300 py-20 text-sm">?�皮?�內容�??�中...</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ??��實�?�?-->
    <div id="view-feedback" class="view-content hidden-view max-w-5xl mx-auto">
        <div class="text-center mb-10 md:mb-16">
            <h2 class="text-3xl md:text-5xl font-serif font-black text-slate-900 mb-4 leading-tight text-center">議�???��實�?�?/h2>
            <p class="text-slate-500 text-sm md:text-lg font-medium max-w-2xl mx-auto text-center leading-relaxed text-balance">
                實�??��?案�?�?50 人�?議即�??納入專�?追蹤?�您?��??��??��??�改變�??��???
            </p>
            <?php if (isset($_SESSION['petition_message'])): ?>
            <div class="mt-6 inline-block px-6 py-3 rounded-2xl text-sm font-bold <?= $_SESSION['petition_message_type'] === 'success' ? 'bg-[#E0F2ED] text-[#2D7A60]' : 'bg-red-50 text-red-600' ?>">
                <?= htmlspecialchars($_SESSION['petition_message']) ?>
            </div>
            <?php 
                unset($_SESSION['petition_message'], $_SESSION['petition_message_type']);
            endif; ?>
            
            <div class="mt-8">
                <button onclick="toggleProposeForm()" class="bg-[#2D7A60] hover:bg-[#1f5c48] text-white px-8 py-4 rounded-full font-black shadow-lg shadow-[#2D7A60]/30 transition-all flex items-center justify-center gap-2 mx-auto">
                    <i data-lucide="pen-tool" class="w-5 h-5"></i> ?��??�主?��?
                </button>
            </div>
        </div>

        <!-- ?��?表單?��?(?�設?��?) -->
        <div id="propose-form-section" class="hidden mb-12 max-w-2xl mx-auto bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl border border-slate-50 relative overflow-hidden">
            <button onclick="toggleProposeForm()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
            <h3 class="text-2xl font-black text-slate-800 mb-6 font-serif">?�起??��?��?</h3>
            <p class="text-sm text-slate-500 mb-8 font-medium">?��??�出後�?系統將�?導您?��? LINE 驗�??�實身�?（自?��??�第 1 位�?��人�??��??��??��?審核確�??�容?��?法�?不當言論�?，即?�在此公?��??�大?��?��??/p>
            
            <form action="/api/petition-propose.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-black text-slate-800 mb-2">?��?標�? <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="請簡?�扼要說?��?案核�?
                           class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#E0F2ED] transition-shadow placeholder:text-slate-300 font-medium">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-800 mb-2">?��?說�? <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="5" required placeholder="請詳細說?��?案內容、�??��?訴�?"
                              class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#E0F2ED] transition-shadow placeholder:text-slate-300 font-medium leading-relaxed"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-800 mb-2">議�??��? <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#E0F2ED] transition-shadow font-medium cursor-pointer">
                        <option value="" disabled selected>請選?��??��??�議題�?�?/option>
                        <option value="農業?�產??>農業?�產??/option>
                        <option value="婦幼?�社?�?�活">婦幼?�社?�?�活</option>
                        <option value="?�者照�?>?�者照�?/option>
                        <option value="?�年?��??�地?�創??>?�年?��??�地?�創??/option>
                        <option value="交通建設�?微�?移�?">交通建設�?微�?移�?</option>
                        <option value="?��??��???>?��??��???/option>
                        <option value="?��?綜�?議�?">?��?綜�?議�?</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-800 mb-2">事件?��??�鎮</label>
                    <select name="town" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#E0F2ED] transition-shadow font-medium cursor-pointer">
                        <option value="?�部?��?">?�部?��?</option>
                        <?php foreach ($towns as $t): if($t['name'] === '?�部?��?') continue; ?>
                        <option value="<?= htmlspecialchars($t['name']) ?>"><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-[#06C755] hover:bg-[#05b34d] text-white py-4 rounded-2xl font-black text-sm transition-colors shadow-lg shadow-[#06C755]/20 flex items-center justify-center gap-2">
                        <i class="fa-brands fa-line text-xl"></i> 下�?步�??��? LINE 實�?認�?並發�?
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
            <?php if (empty($petitions)): ?>
            <div class="col-span-1 md:col-span-2 bg-white rounded-[2rem] p-12 text-center shadow-sm border border-slate-50">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="file-text" class="w-8 h-8"></i>
                </div>
                <p class="text-slate-400 font-medium">?��?尚無?�放中�???��?��?</p>
            </div>
            <?php else: ?>
                <?php foreach ($petitions as $p): 
                    $progress = min(100, (int)round($p['current_count'] / max(1, $p['target_count']) * 100));
                    $isNew = (strtotime($p['created_at']) > strtotime('-7 days'));
                    $isHot = ($progress >= 70 || $p['current_count'] >= 30);
                ?>
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-50 flex flex-col justify-between relative overflow-hidden">
                    <?php if ($isNew && !$isHot): ?>
                        <div class="absolute top-4 -right-8 bg-amber-400 text-white text-[10px] font-black py-1 px-10 rotate-45 shadow-sm tracking-wider">
                            ?? ?�?��?�?
                        </div>
                    <?php elseif ($isHot): ?>
                        <div class="absolute top-4 -right-8 bg-rose-500 text-white text-[10px] font-black py-1 px-10 rotate-45 shadow-sm tracking-wider">
                            ?�� ?��??��?
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="flex items-center flex-wrap gap-2 mb-4 pr-10">
                            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 text-xs font-black rounded-lg">
                                <?= htmlspecialchars($p['town'] ?? '?��?') ?>
                            </span>
                            <span class="inline-block px-3 py-1 bg-brand-green/10 text-brand-green text-xs font-black rounded-lg border border-brand-green/20">
                                <?= htmlspecialchars($p['category'] ?? '綜�?議�?') ?>
                            </span>
                            <span class="inline-block px-3 py-1 <?= $p['status'] === '已�?�? ? 'bg-blue-50 text-blue-600' : 'bg-[#E0F2ED] text-[#2D7A60]' ?> text-xs font-black rounded-lg">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800 mb-3 font-serif"><?= htmlspecialchars($p['title']) ?></h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-8 line-clamp-3">
                            <?= nl2br(htmlspecialchars($p['description'])) ?>
                        </p>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm font-bold mb-2">
                            <span class="text-slate-800"><?= (int)$p['current_count'] ?> 人已??��</span>
                            <span class="text-slate-400">?��? <?= (int)$p['target_count'] ?> �?/span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 mb-8 overflow-hidden">
                            <div class="bg-[#2D7A60] h-2 rounded-full transition-all duration-1000" style="width: <?= $progress ?>%"></div>
                        </div>

                        <?php if ($p['status'] === '已�?�?): ?>
                            <button disabled class="w-full bg-slate-100 text-slate-400 py-4 rounded-xl font-black text-sm cursor-not-allowed">
                                已�?標�?專�?追蹤�?
                            </button>
                        <?php else: ?>
                            <a href="/api/line-login.php?id=<?= $p['id'] ?>" class="flex items-center justify-center w-full bg-[#06C755] hover:bg-[#05b34d] text-white py-4 rounded-xl font-black text-sm transition-colors shadow-lg shadow-[#06C755]/20">
                                <i class="fa-brands fa-line text-xl mr-2"></i> 使用 LINE 一?��?��
                            </a>
                        <?php endif; ?>

                        <?php 
                        $petitionSignatures = $signaturesMap[$p['id']] ?? [];
                        if (!empty($petitionSignatures)): 
                        ?>
                        <div class="mt-6 pt-5 border-t border-slate-100 flex items-center gap-3">
                            <div class="flex -space-x-2 overflow-hidden">
                                <?php foreach ($petitionSignatures as $index => $sig): ?>
                                    <?php if ($sig['line_picture_url']): ?>
                                        <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover" src="<?= htmlspecialchars($sig['line_picture_url']) ?>" alt="<?= htmlspecialchars($sig['line_display_name']) ?>" title="<?= htmlspecialchars($sig['line_display_name']) ?>">
                                    <?php else: ?>
                                        <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500" title="<?= htmlspecialchars($sig['line_display_name']) ?>">
                                            <?= mb_substr($sig['line_display_name'], 0, 1) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-xs text-slate-400 font-medium">
                                <span class="font-bold text-slate-700"><?= htmlspecialchars($petitionSignatures[count($petitionSignatures)-1]['line_display_name']) ?></span> 等人已�?��
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Footer（�? demo 一?��? -->
<footer class="bg-white border-t border-[#E0F2ED] py-12 md:py-20 mt-12 md:mt-20">
    <div class="max-w-7xl mx-auto px-6 md:px-4 grid grid-cols-1 md:grid-cols-12 gap-10 md:gap-16">
        <div class="md:col-span-5">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 bg-brand-green rounded-lg flex items-center justify-center text-white font-serif font-black text-lg">�?/div>
                <span class="font-serif font-black text-xl text-slate-800">潘炩�?<span class="brand-green font-sans text-sm ml-0.5">?��?辦公�?/span></span>
            </div>
            <p class="text-slate-400 text-xs md:text-sm leading-relaxed font-medium mb-6">深耕�??�第三選?�?�以農�??�銷?�親子�??�、長?��?心�??��?，承?�您?��?付�?/p>
        </div>
        <div class="md:col-span-3">
            <h4 class="font-black text-xs mb-6 text-slate-800 tracking-[0.1em] uppercase">?��?範�?</h4>
            <div class="grid grid-cols-2 gap-y-3 text-slate-400 text-xs font-bold">
                <?php foreach (['潮�???,'?�埤??,'?��???,'?��???,'竹田??,'?�寮??] as $t): ?>
                <span><?= $t ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="mt-12 text-center text-slate-300 text-[9px] font-black tracking-[0.2em] uppercase">
        © 2026 PAN LING-YI IMPACT OFFICE | DATA SUPPORTED BY MID CREATIVE
    </div>
</footer>

<script>
    const heroSettings = <?= json_encode($settingsMap) ?>;
</script>
<style>.hidden-view { display: none; }</style>
<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    const icon = document.getElementById('mobile-menu-icon');
    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        menu.classList.add('flex');
        icon.setAttribute('data-lucide', 'x');
    } else {
        menu.classList.add('hidden');
        menu.classList.remove('flex');
        icon.setAttribute('data-lucide', 'menu');
    }
    lucide.createIcons();
}

function switchView(name) {
    document.querySelectorAll('.view-content').forEach(v => v.classList.add('hidden-view'));
    document.getElementById('view-' + name)?.classList.remove('hidden-view');
    
    const title = document.getElementById('main-title');
    const isMobile = window.innerWidth < 768;
    const breakTag = isMobile ? '' : '<br>';

    let t1 = '', t2 = '';
    if(name === 'home') {
        t1 = heroSettings.HERO_HOME_TITLE_1 || '';
        t2 = heroSettings.HERO_HOME_TITLE_2 || '';
    } else if(name === 'issues') {
        t1 = heroSettings.HERO_ISSUES_TITLE_1 || '';
        t2 = heroSettings.HERO_ISSUES_TITLE_2 || '';
    } else if(name === 'feedback') {
        t1 = heroSettings.HERO_FEEDBACK_TITLE_1 || '';
        t2 = heroSettings.HERO_FEEDBACK_TITLE_2 || '';
    }

    if(t1 === '' && t2 === '') {
        title.style.display = 'none';
        title.innerHTML = '';
    } else {
        title.style.display = 'block';
        title.innerHTML = `${t1}${breakTag}<span class="brand-green font-sans italic opacity-90">${t2}</span>`;
    }

    // Update Desktop Nav
    document.querySelectorAll('.nav-btn').forEach(b => {
        b.classList.remove('brand-green','border-b-2','border-brand-green','pb-1');
        b.classList.add('text-slate-500');
    });
    
    const btn = document.getElementById('nav-' + name);
    if (btn) { 
        btn.classList.remove('text-slate-500');
        btn.classList.add('brand-green','border-b-2','border-brand-green','pb-1'); 
    }

    // Update Mobile Nav
    document.querySelectorAll('.mobile-nav-btn').forEach(b => {
        b.classList.remove('text-brand-green');
        b.classList.add('text-slate-600');
    });
    
    const mobileBtn = document.getElementById('mobile-nav-' + name);
    if (mobileBtn) {
        mobileBtn.classList.remove('text-slate-600');
        mobileBtn.classList.add('text-brand-green');
    }
    
    window.scrollTo({top: 0, behavior: 'smooth'});
}
function filterTown(el, town) {
    document.querySelectorAll('.town-btn').forEach(b => b.classList.remove('bg-brand-green','text-white','shadow-md'));
    el.classList.add('bg-brand-green','text-white','shadow-md');
    document.querySelectorAll('#service-list article').forEach(card => {
        card.style.display = (town === '?�部?��?' || card.dataset.town === town) ? '' : 'none';
    });
}
function toggleProposeForm() {
    const form = document.getElementById('propose-form-section');
    form.classList.toggle('hidden');
    if (!form.classList.contains('hidden')) {
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>

<?php
$content = ob_get_clean() ?? '';
FrontLayout::render($content, [
    'title'       => '潘炩�??��??��? | 屏東第�??��??�地?��??��??��??��?',
    'description' => '屏東�?��?�第三選?�?�選人�??��?，深?�潮州鎮?�新?��??�內?��??�萬巒�??�竹?��??��?寮�??��??�農漁產?�、長?�、親子�??��??�地?��?案�???,
    'canonical'   => ($_ENV['APP_URL'] ?? 'https://panlingyi.tw') . '/',
    'schema_type' => 'Person',
    'schema_data' => [],
]);