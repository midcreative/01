<?php
use App\Layout\AdminLayout;

ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-black text-slate-800">系統參數設定</h1>
        <p class="text-slate-500 text-sm mt-1">管理與外部 API 整合的相關金鑰與參數設定。</p>
    </div>
</div>

<?php if (isset($_SESSION['setting_message'])): ?>
    <div class="mb-6 bg-[#E0F2ED] text-[#2D7A60] px-4 py-3 rounded-xl text-sm font-bold flex items-center shadow-sm">
        <i class="fa-solid fa-circle-check mr-2"></i>
        <?= htmlspecialchars($_SESSION['setting_message']) ?>
    </div>
    <?php unset($_SESSION['setting_message']); ?>
<?php endif; ?>

<div class="bg-white rounded-[2rem] p-8 border border-slate-50 shadow-sm relative overflow-hidden">
    <!-- Decorative background element -->
    <div class="absolute top-0 right-0 p-12 text-[#2D7A60]/5 pointer-events-none transform translate-x-1/4 -translate-y-1/4">
        <i class="fa-brands fa-line text-[15rem]"></i>
    </div>

    <form method="POST" action="/admin/settings/update" enctype="multipart/form-data" class="relative z-10">
        <div class="mb-8">
            <h3 class="text-lg font-black text-slate-800 mb-2 border-l-4 border-[#06C755] pl-3">LINE Login 整合設定</h3>
            <p class="text-slate-500 text-sm mb-6 pl-4">用於「連署提案」功能的一鍵 LINE 身分驗證與自動加入官方帳號。請至 <a href="https://developers.line.biz/" target="_blank" class="text-[#06C755] hover:underline font-bold">LINE Developers Console</a> 取得以下資訊。</p>
            
            <div class="space-y-6 max-w-2xl pl-4">
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <label class="block text-sm font-black text-slate-800 mb-2 mt-0">Callback URL (重新導向 URI)</label>
                    <div class="flex items-center gap-3">
                        <?php 
                            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'];
                            $callbackUrl = $protocol . '://' . $host . '/api/line-callback.php';
                        ?>
                        <code class="flex-1 bg-white border border-slate-200 px-4 py-3 rounded-xl text-xs text-slate-600 font-mono break-all cursor-text select-all"><?= htmlspecialchars($callbackUrl) ?></code>
                        <span class="text-xs text-slate-400">請將此網址填入 LINE 後台</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-800 mb-2">Channel ID (頻道 ID)</label>
                    <input type="text" name="LINE_CHANNEL_ID" placeholder="請輸入 10 碼數字 ID"
                           value="<?= htmlspecialchars($settings['LINE_CHANNEL_ID']['setting_value'] ?? '') ?>"
                           class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#06C755]/20 focus:bg-white transition-all placeholder:text-slate-300 font-medium font-mono">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-800 mb-2">Channel Secret (頻道密鑰)</label>
                    <input type="password" name="LINE_CHANNEL_SECRET" placeholder="請輸入 32 碼英數密鑰"
                           value="<?= htmlspecialchars($settings['LINE_CHANNEL_SECRET']['setting_value'] ?? '') ?>"
                           class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#06C755]/20 focus:bg-white transition-all placeholder:text-slate-300 font-medium font-mono">
                           <p class="text-xs text-slate-400 mt-2"><i class="fa-solid fa-triangle-exclamation mr-1 text-orange-400"></i> 請妥善保管密鑰，若更改網域可繼續沿用，無需重新設定。</p>
                </div>
            </div>
        </div>

        </div>

        <hr class="border-slate-100 mb-8 max-w-2xl">

        <div class="mb-8">
            <h3 class="text-lg font-black text-slate-800 mb-2 border-l-4 border-[#06C755] pl-3">首頁橫幅 (Hero) 設定</h3>
            <p class="text-slate-500 text-sm mb-6 pl-4">設定前台首頁的主視覺背景圖片。建議尺寸比例為 16:9 或 2.5:1，寬度建議至少 1920px。</p>
            
            <div class="space-y-6 max-w-2xl pl-4">
                <div>
                    <label class="block text-sm font-black text-slate-800 mb-2">上傳背景圖片</label>
                    <input type="file" name="HERO_BG_IMAGE_FILE" accept="image/png, image/jpeg, image/webp"
                           class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-3 text-slate-800 focus:ring-4 focus:ring-[#06C755]/20 focus:bg-white transition-all font-medium">
                    <?php if (!empty($settings['HERO_BG_IMAGE']['setting_value'])): ?>
                        <div class="mt-4">
                            <p class="text-xs text-slate-500 mb-2">目前使用的圖片：</p>
                            <img src="<?= htmlspecialchars($settings['HERO_BG_IMAGE']['setting_value']) ?>" alt="Hero BG" class="h-32 rounded-lg border border-slate-200 object-cover">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <hr class="border-slate-100 mb-8 max-w-2xl">

        <div class="pl-4">
            <button type="submit" class="bg-[#06C755] hover:bg-[#05b34d] text-white px-8 py-3 rounded-2xl font-black transition-colors shadow-lg shadow-[#06C755]/30 focus:ring-4 focus:ring-[#06C755]/20">
                <i class="fa-solid fa-save mr-2"></i> 儲存設定
            </button>
        </div>
    </form>
</div>

<?php AdminLayout::render('系統設定', ob_get_clean() ?: '', 'settings'); ?>
