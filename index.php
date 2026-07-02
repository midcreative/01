<?php

declare(strict_types=1);

// ─── Bootstrap ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;
use App\Models\Post;
use App\Models\Town;
use App\Models\Whitepaper;
use App\Layout\FrontLayout;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($uri, '/admin') === 0) {
    require __DIR__ . '/admin/index.php';
    exit;
}

$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->safeLoad();
date_default_timezone_set('Asia/Taipei');

// ─── Read data from DB ──────────────────────────────────────────────────────
try {
    $pdo   = Database::getInstance();

    $posts = Post::allPublished();
    $towns = Town::all();
    $whitepapers = Whitepaper::allActive();

    $petitionsStmt = $pdo->prepare("SELECT * FROM petitions WHERE status IN ('公開連署', '已達標') ORDER BY created_at DESC");
    $petitionsStmt->execute();
    $petitions = $petitionsStmt->fetchAll();

    $categoryStatsStmt = $pdo->query('
        SELECT c.id, c.name, c.color_theme, COUNT(p.id) as post_count 
        FROM post_categories c 
        LEFT JOIN posts p ON c.id = p.category_id AND p.is_published = 1 
        GROUP BY c.id 
        ORDER BY c.sort_order ASC
    ');
    $categoryStats = $categoryStatsStmt->fetchAll();

    $settings = $pdo->query('SELECT * FROM settings')->fetchAll();
    $settingsMap = [];
    foreach ($settings as $s) {
        $settingsMap[$s['setting_key']] = $s['setting_value'];
    }

} catch (\Throwable $e) {
    error_log('Front index error: ' . $e->getMessage());
    $posts       = [];
    $towns       = [];
    $whitepapers = [];
    $categoryStats = [];
    $settingsMap = [];
}

// ─── Render ─────────────────────────────────────────────────────────────────
ob_start();
?>

<!-- 導航欄 -->
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#E0F2ED]">
    <div class="max-w-7xl mx-auto px-4 h-16 md:h-20 flex items-center justify-between">
        <div class="flex items-center gap-2 md:gap-3 py-2 cursor-pointer" onclick="switchView('home')">
            <div class="w-10 h-10 md:w-12 md:h-12 bg-brand-green rounded-xl flex items-center justify-center text-white shadow-lg shadow-[#66C2A5]/20 rotate-3 hover:rotate-0 transition-all">
                <span class="font-serif text-xl md:text-2xl font-bold">潘</span>
            </div>
            <div class="flex flex-col text-left">
                <span class="font-serif text-lg md:text-2xl font-black tracking-tighter text-slate-800">
                    潘炩禕 <span class="brand-green font-sans text-sm md:text-lg font-bold ml-0.5">服務日記</span>
                </span>
                <span class="text-[8px] md:text-[9px] font-bold text-slate-400 uppercase tracking-[0.1em] md:tracking-[0.2em]">Pingtung District 3 Impact Site</span>
            </div>
        </div>
        <div class="hidden lg:flex items-center gap-8 text-sm font-bold tracking-wide">
            <button onclick="switchView('home')"     id="nav-home"     class="nav-btn brand-green border-b-2 border-brand-green pb-1">鄉鎮足跡</button>
            <button onclick="switchView('issues')"   id="nav-issues"   class="nav-btn text-slate-500 hover:brand-green">行動白皮書</button>
            <button onclick="switchView('feedback')" id="nav-feedback" class="nav-btn text-slate-500 hover:brand-green">連署實證站</button>
            <a href="/volunteer.php" class="nav-btn text-slate-500 hover:brand-green">志工招募</a>
        </div>
        <div class="flex items-center gap-2">
            <a href="tel:081234567" class="bg-brand-green text-white px-4 md:px-6 py-2 md:py-2.5 rounded-full text-xs md:text-sm font-black shadow-lg shadow-[#66C2A5]/30 flex items-center gap-2 hover:bg-[#57A891] transition-all">
                <i data-lucide="message-square" class="w-3.5 h-3.5 md:w-4 md:h-4"></i> <span class="hidden xs:inline">線上陳情</span>
            </a>
        </div>
    </div>
</nav>

<!-- 主內容 -->
<main class="max-w-7xl mx-auto px-4 py-8 md:py-12 text-left">
    <?php
    $heroBg = $settingsMap['HERO_BG_IMAGE'] ?? '';
    $bgStyle = $heroBg ? "background-image: url('" . htmlspecialchars($heroBg) . "'); background-size: cover; background-position: center;" : "";
    $headerClass = $heroBg ? "mb-8 md:mb-16 relative rounded-[2.5rem] overflow-hidden shadow-[0_10px_40px_-15px_rgba(102,194,165,0.3)] border border-[#E0F2ED]/50 aspect-[1/1] sm:aspect-[16/9] md:aspect-[2.5/1] flex flex-col items-center justify-center p-6" : "mb-8 md:mb-16 text-center";
    ?>
    <header class="<?= $headerClass ?>" style="<?= $bgStyle ?>">
        <div class="inline-block bg-[#E0F2ED] px-3 py-1 rounded-full text-[#4A937F] text-[9px] md:text-[10px] font-black mb-4 tracking-[0.15em] uppercase">屏東縣議員第三選區參選人</div>
        <h1 id="main-title" class="text-3xl md:text-6xl font-serif font-black text-slate-900 mb-6 md:mb-8 leading-tight px-2">
            聽見地方的心跳，<br class="hidden md:block">
            <span class="brand-green font-sans italic opacity-90">讓服務的溫度延續。</span>
        </h1>
    </header>

    <!-- 首頁：鄉鎮足跡 -->
    <div id="view-home" class="view-content">
        <!-- 鄉鎮切換 -->
        <section class="mb-8 overflow-x-auto whitespace-nowrap pb-4 no-scrollbar -mx-4 px-4 text-center">
            <div class="flex flex-nowrap items-center justify-center gap-2.5">
                <button onclick="filterTown(this,'全部地區')" class="town-btn active-town px-6 py-2 rounded-full text-xs font-bold bg-brand-green text-white shadow-md">全部地區</button>
                <?php foreach ($towns as $town): if($town['name'] === '全部地區') continue; ?>
                <button onclick="filterTown(this,'<?= htmlspecialchars($town['name']) ?>')" class="town-btn px-6 py-2 rounded-full text-xs font-bold bg-white text-slate-400 border border-slate-100"><?= htmlspecialchars($town['name']) ?></button>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 數據看板（從 DB 動態產生） -->
        <p class="text-center text-slate-400 text-[10px] mb-3 font-bold tracking-[0.1em]"><i data-lucide="filter" class="inline w-3 h-3 mr-1 -mt-0.5 opacity-60"></i>點擊分類查看專屬服務日記</p>
        <section class="mb-10 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 text-center">
            <?php foreach ($categoryStats as $c): ?>
            <button onclick="filterCategory('<?= htmlspecialchars($c['name']) ?>')" id="cat-btn-<?= htmlspecialchars(md5($c['name'])) ?>" class="category-btn bg-white py-4 px-2 rounded-[1.5rem] border border-slate-50 shadow-sm flex flex-col items-center justify-center group hover:border-[#66C2A5]/50 transition-all cursor-pointer w-full">
                <i data-lucide="folder" class="brand-green mb-2 w-5 h-5 opacity-70"></i>
                <h3 class="text-sm font-black text-slate-800 leading-tight">
                    <span class="text-xl"><?= (int)$c['post_count'] ?></span> 
                    <span class="text-xs text-slate-500 font-bold ml-0.5">個</span><br>
                    <span class="text-xs"><?= htmlspecialchars($c['name']) ?></span>
                </h3>
            </button>
            <?php endforeach; ?>
        </section>

        <!-- 服務日記列表 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8" id="service-list">
            <?php if (empty($posts)): ?>
            <div class="md:col-span-2 py-20 text-center text-slate-300 text-sm">尚未有服務日記，請透過後台新增。</div>
            <?php endif; ?>

            <?php foreach ($posts as $post):
                $badgeClass = $post['category_color'] ?? 'bg-slate-50 text-slate-500 border-slate-100';
            ?>
            <article class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-50 shadow-sm hover:shadow-lg transition-all flex flex-col group post-item"
                     data-town="<?= htmlspecialchars($post['town_name'] ?? '') ?>"
                     data-category="<?= htmlspecialchars($post['category_name'] ?? '') ?>">
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
                        <?= htmlspecialchars($post['category_name'] ?? '未分類') ?>
                    </span>
                </div>
                <?php endif; ?>
                <div class="p-6 md:p-10 flex flex-col flex-grow">
                    <div class="flex items-center gap-2 text-[9px] font-black text-slate-400 mb-3 tracking-wider">
                        <i data-lucide="map-pin" class="brand-green w-3 h-3"></i>
                        <?= htmlspecialchars($post['town_name'] ?? '未指定') ?>
                        <span class="mx-1 opacity-30">/</span>
                        <?= htmlspecialchars($post['published_at'] ?? '') ?>
                    </div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-800 mb-4 group-hover:brand-green transition-colors font-serif leading-snug">
                        <?= htmlspecialchars($post['title']) ?>
                    </h2>
                    <?php if ($post['excerpt']): ?>
                    <div class="bg-[#F9FBFA] p-5 rounded-2xl mb-6 flex-grow border border-slate-50">
                        <p class="text-xs md:text-sm text-slate-500 leading-relaxed italic font-medium">
                            「<?= htmlspecialchars($post['excerpt']) ?>」
                        </p>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center justify-end">
                        <a href="/post/index.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="brand-green font-black text-xs flex items-center gap-1 hover:gap-3 transition-all">
                            閱讀更多 <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if (count($posts) > 12): ?>
        <div class="mt-12 text-center" id="load-more-container" style="display: none;">
            <button onclick="loadMorePosts()" class="px-8 py-3 bg-white text-slate-500 border border-slate-200 rounded-full font-bold hover:bg-slate-50 hover:text-[#66C2A5] transition-all">
                載入更多日記
            </button>
        </div>
        <?php endif; ?>
    </div>

    <div id="view-issues" class="view-content hidden-view animate-in fade-in max-w-5xl mx-auto text-left">
        <div class="text-center mb-10 md:mb-16">
            <h2 class="text-3xl md:text-5xl font-serif font-black text-slate-900 mb-4 leading-tight text-center">行動白皮書</h2>
            <p class="text-slate-500 text-sm md:text-lg font-medium max-w-2xl mx-auto text-center leading-relaxed text-balance">
                以數據設計未來，用行動回應託付。這是我們為屏東第三選區定義的核心支柱。
            </p>
        </div>

        <div class="space-y-8 md:space-y-12 text-left">
            <?php foreach ($whitepapers as $w): ?>
            <?php if ($w['theme_color'] === 'text-slate-900'): // 特別處理深色樣式 ?>
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
                    <a href="/volunteer.php" class="inline-block px-10 py-4 bg-[#66C2A5] text-white rounded-2xl font-black text-sm shadow-xl hover:scale-105 transition-all text-center">參與長照連署</a>
                </div>
            </div>
            <?php else: // 一般樣式 ?>
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
            <div class="text-center text-slate-300 py-20 text-sm">白皮書內容籌備中...</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 連署實證站 -->
    <div id="view-feedback" class="view-content hidden-view max-w-5xl mx-auto">
        <div class="text-center mb-10 md:mb-16">
            <h2 class="text-3xl md:text-5xl font-serif font-black text-slate-900 mb-4 leading-tight text-center">議題連署實證站</h2>
            <p class="text-slate-500 text-sm md:text-lg font-medium max-w-2xl mx-auto text-center leading-relaxed text-balance">
                實名制提案，滿 100 人覆議即正式納入專案追蹤。您的參與，是驅動改變的開始。
            </p>
            <?php if (isset($_SESSION['petition_message'])): ?>
            <div class="mt-6 inline-block px-6 py-3 rounded-2xl text-sm font-bold <?= $_SESSION['petition_message_type'] === 'success' ? 'bg-[#E0F2ED] text-[#2D7A60]' : 'bg-red-50 text-red-600' ?>">
                <?= htmlspecialchars($_SESSION['petition_message']) ?>
            </div>
            <?php 
                unset($_SESSION['petition_message'], $_SESSION['petition_message_type']);
            endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
            <?php if (empty($petitions)): ?>
            <div class="col-span-1 md:col-span-2 bg-white rounded-[2rem] p-12 text-center shadow-sm border border-slate-50">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="file-text" class="w-8 h-8"></i>
                </div>
                <p class="text-slate-400 font-medium">目前尚無開放中的連署提案</p>
            </div>
            <?php else: ?>
                <?php foreach ($petitions as $p): 
                    $progress = min(100, (int)round($p['current_count'] / max(1, $p['target_count']) * 100));
                ?>
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 text-xs font-black rounded-lg">
                                <?= htmlspecialchars($p['town'] ?? '全區') ?>
                            </span>
                            <span class="inline-block px-3 py-1 <?= $p['status'] === '已達標' ? 'bg-blue-50 text-blue-600' : 'bg-[#E0F2ED] text-[#2D7A60]' ?> text-xs font-black rounded-lg">
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
                            <span class="text-slate-800"><?= (int)$p['current_count'] ?> 人已連署</span>
                            <span class="text-slate-400">目標 <?= (int)$p['target_count'] ?> 人</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 mb-8 overflow-hidden">
                            <div class="bg-[#2D7A60] h-2 rounded-full transition-all duration-1000" style="width: <?= $progress ?>%"></div>
                        </div>

                        <?php if ($p['status'] === '已達標'): ?>
                            <button disabled class="w-full bg-slate-100 text-slate-400 py-4 rounded-xl font-black text-sm cursor-not-allowed">
                                已達標，專案追蹤中
                            </button>
                        <?php else: ?>
                            <a href="/api/line-login.php?id=<?= $p['id'] ?>" class="flex items-center justify-center w-full bg-[#06C755] hover:bg-[#05b34d] text-white py-4 rounded-xl font-black text-sm transition-colors shadow-lg shadow-[#06C755]/20">
                                <i class="fa-brands fa-line text-xl mr-2"></i> 使用 LINE 一鍵連署
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Footer（與 demo 一致） -->
<footer class="bg-white border-t border-[#E0F2ED] py-12 md:py-20 mt-12 md:mt-20">
    <div class="max-w-7xl mx-auto px-6 md:px-4 grid grid-cols-1 md:grid-cols-12 gap-10 md:gap-16">
        <div class="md:col-span-5">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 bg-brand-green rounded-lg flex items-center justify-center text-white font-serif font-black text-lg">潘</div>
                <span class="font-serif font-black text-xl text-slate-800">潘炩禕 <span class="brand-green font-sans text-sm ml-0.5">服務辦公室</span></span>
            </div>
            <p class="text-slate-400 text-xs md:text-sm leading-relaxed font-medium mb-6">深耕屏東第三選區。以農漁產銷、親子教育、長照與心靈支持，承接您的託付。</p>
        </div>
        <div class="md:col-span-3">
            <h4 class="font-black text-xs mb-6 text-slate-800 tracking-[0.1em] uppercase">服務範圍</h4>
            <div class="grid grid-cols-2 gap-y-3 text-slate-400 text-xs font-bold">
                <?php foreach (['潮州鎮','內埔鄉','萬巒鄉','枋寮鄉'] as $t): ?>
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
function switchView(name) {
    document.querySelectorAll('.view-content').forEach(v => v.classList.add('hidden-view'));
    document.getElementById('view-' + name)?.classList.remove('hidden-view');
    
    const title = document.getElementById('main-title');
    const isMobile = window.innerWidth < 768;
    const breakTag = isMobile ? '' : '<br class="hidden md:block">';

    let t1 = '', t2 = '';
    if(name === 'home') {
        t1 = heroSettings.HERO_HOME_TITLE_1 || '聽見地方的心跳，';
        t2 = heroSettings.HERO_HOME_TITLE_2 || '讓服務的溫度延續。';
    } else if(name === 'issues') {
        t1 = heroSettings.HERO_ISSUES_TITLE_1 || '承接老朋友的託付，';
        t2 = heroSettings.HERO_ISSUES_TITLE_2 || '設計新一代的屏東。';
    } else if(name === 'feedback') {
        t1 = heroSettings.HERO_FEEDBACK_TITLE_1 || '匯集集體的意志，';
        t2 = heroSettings.HERO_FEEDBACK_TITLE_2 || '翻轉家鄉的未來。';
    }

    if(t1 === '' && t2 === '') {
        title.style.display = 'none';
        title.innerHTML = '';
    } else {
        title.style.display = 'block';
        title.innerHTML = `${t1}${breakTag}<span class="brand-green font-sans italic opacity-90">${t2}</span>`;
    }

    document.querySelectorAll('.nav-btn').forEach(b => {
        b.classList.remove('brand-green','border-b-2','border-brand-green','pb-1');
        b.classList.add('text-slate-500');
    });
    
    const btn = document.getElementById('nav-' + name);
    if (btn) { 
        btn.classList.remove('text-slate-500');
        btn.classList.add('brand-green','border-b-2','border-brand-green','pb-1'); 
    }
    
    window.scrollTo({top: 0, behavior: 'smooth'});
}
let currentTown = '全部地區';
let currentCategory = '全部類別';
let itemsToShow = 12;

function renderPosts() {
    const posts = document.querySelectorAll('.post-item');
    let visibleCount = 0;
    let totalMatching = 0;

    posts.forEach(card => {
        const matchTown = (currentTown === '全部地區' || card.dataset.town === currentTown);
        const matchCategory = (currentCategory === '全部類別' || card.dataset.category === currentCategory);
        if (matchTown && matchCategory) {
            totalMatching++;
            if (visibleCount < itemsToShow) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        } else {
            card.style.display = 'none';
        }
    });

    const loadMoreContainer = document.getElementById('load-more-container');
    if (loadMoreContainer) {
        if (itemsToShow >= totalMatching) {
            loadMoreContainer.style.display = 'none';
        } else {
            loadMoreContainer.style.display = 'block';
        }
    }
}

function filterTown(el, town) {
    document.querySelectorAll('.town-btn').forEach(b => b.classList.remove('bg-brand-green','text-white','shadow-md'));
    el.classList.add('bg-brand-green','text-white','shadow-md');
    
    currentTown = town;
    itemsToShow = 12;
    renderPosts();
}

function filterCategory(category) {
    const isCurrentlyActive = currentCategory === category;
    
    document.querySelectorAll('.category-btn').forEach(b => {
        b.classList.remove('active-category', 'border-[#66C2A5]', 'border-2');
        b.classList.add('border-slate-50');
    });
    
    if (isCurrentlyActive) {
        currentCategory = '全部類別';
    } else {
        currentCategory = category;
        document.querySelectorAll('.category-btn').forEach(b => {
            const span = b.querySelector('span:last-child');
            if (span && span.textContent === category) {
                b.classList.add('active-category', 'border-[#66C2A5]', 'border-2');
                b.classList.remove('border-slate-50');
            }
        });
    }
    
    itemsToShow = 12;
    renderPosts();
}

function loadMorePosts() {
    itemsToShow += 12;
    renderPosts();
}

document.addEventListener("DOMContentLoaded", () => {
    if (document.querySelectorAll('.post-item').length > 0) {
        renderPosts();
    }
});
</script>

<?php
$content = ob_get_clean() ?? '';
FrontLayout::render($content, [
    'title'       => '潘炩禕 服務日記 | 屏東第三選區在地服務與民意互動站',
    'description' => '屏東縣議員第三選區參選人潘炩禕，深耕潮州鎮、內埔鄉、萬巒鄉、枋寮鄉。紀錄農漁產銷、長照、親子教育等在地服務案例。',
    'canonical'   => ($_ENV['APP_URL'] ?? 'https://panlingyi.tw') . '/',
    'schema_type' => 'Person',
    'schema_data' => [],
]);
