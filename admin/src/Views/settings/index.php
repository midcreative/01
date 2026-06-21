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

    <form method="POST" action="/admin/settings/update" class="relative z-10" enctype="multipart/form-data">
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

        <hr class="border-slate-100 mb-8 mt-8 max-w-2xl">

        <div class="mb-8">
            <h3 class="text-lg font-black text-slate-800 mb-2 border-l-4 border-[#66C2A5] pl-3">首頁與 Hero 區塊設定</h3>
            <p class="text-slate-500 text-sm mb-6 pl-4">設定前台首頁的主視覺文字與「連署實證站」行動呼籲按鈕 (CTA)。</p>

            <div class="space-y-6 max-w-3xl pl-4">
                <!-- 頂部標籤與背景圖 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                    <div>
                        <label class="block text-sm font-black text-slate-800 mb-2">參選人標籤 (HERO_TAG)</label>
                        <input type="text" name="HERO_TAG" value="<?= htmlspecialchars($settings['HERO_TAG']['setting_value'] ?? '') ?>"
                               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-slate-800 focus:ring-2 focus:ring-[#66C2A5]/30 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-800 mb-2">背景圖片 (HERO_BG_IMAGE)</label>
                        <?php if (!empty($settings['HERO_BG_IMAGE']['setting_value'])): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($settings['HERO_BG_IMAGE']['setting_value']) ?>" class="h-16 object-cover rounded shadow-sm border border-slate-200">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="HERO_BG_IMAGE" accept="image/*" class="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#E0F2ED] file:text-[#2D7A60] hover:file:bg-[#66C2A5] hover:file:text-white transition-all cursor-pointer mb-2">
                        <label class="block text-sm font-bold text-slate-700 mt-2 mb-1">背景模糊遮罩</label>
                        <select name="HERO_BG_OVERLAY" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-slate-800 outline-none focus:ring-2 focus:ring-[#66C2A5]/30">
                            <option value="1" <?= (!isset($settings['HERO_BG_OVERLAY']['setting_value']) || $settings['HERO_BG_OVERLAY']['setting_value'] == '1') ? 'selected' : '' ?>>開啟 (加上白色半透明，確保黑字清晰)</option>
                            <option value="0" <?= (isset($settings['HERO_BG_OVERLAY']['setting_value']) && $settings['HERO_BG_OVERLAY']['setting_value'] == '0') ? 'selected' : '' ?>>關閉 (適合已經自帶字體的設計海報)</option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">支援 JPG, PNG, WEBP。若不更改請保持空白</p>
                    </div>
                </div>

                <!-- 標題設定 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">鄉鎮足跡 (首頁)</h4>
                        <label class="block text-sm font-bold text-slate-700 mb-1">主標題</label>
                        <input type="text" name="HERO_HOME_TITLE_1" value="<?= htmlspecialchars($settings['HERO_HOME_TITLE_1']['setting_value'] ?? '') ?>"
                               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 mb-3 text-slate-800 outline-none focus:ring-2 focus:ring-[#66C2A5]/30">
                        
                        <label class="block text-sm font-bold text-slate-700 mb-1">副標題 (綠色斜體)</label>
                        <input type="text" name="HERO_HOME_TITLE_2" value="<?= htmlspecialchars($settings['HERO_HOME_TITLE_2']['setting_value'] ?? '') ?>"
                               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-slate-800 outline-none focus:ring-2 focus:ring-[#66C2A5]/30">
                    </div>

                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">行動白皮書</h4>
                        <label class="block text-sm font-bold text-slate-700 mb-1">主標題</label>
                        <input type="text" name="HERO_ISSUES_TITLE_1" value="<?= htmlspecialchars($settings['HERO_ISSUES_TITLE_1']['setting_value'] ?? '') ?>"
                               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 mb-3 text-slate-800 outline-none focus:ring-2 focus:ring-[#66C2A5]/30">
                        
                        <label class="block text-sm font-bold text-slate-700 mb-1">副標題 (綠色斜體)</label>
                        <input type="text" name="HERO_ISSUES_TITLE_2" value="<?= htmlspecialchars($settings['HERO_ISSUES_TITLE_2']['setting_value'] ?? '') ?>"
                               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-slate-800 outline-none focus:ring-2 focus:ring-[#66C2A5]/30">
                    </div>

                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 md:col-span-2">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">連署實證站</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">主標題</label>
                                <input type="text" name="HERO_FEEDBACK_TITLE_1" value="<?= htmlspecialchars($settings['HERO_FEEDBACK_TITLE_1']['setting_value'] ?? '') ?>"
                                       class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-slate-800 outline-none focus:ring-2 focus:ring-[#66C2A5]/30">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">副標題 (綠色斜體)</label>
                                <input type="text" name="HERO_FEEDBACK_TITLE_2" value="<?= htmlspecialchars($settings['HERO_FEEDBACK_TITLE_2']['setting_value'] ?? '') ?>"
                                       class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-slate-800 outline-none focus:ring-2 focus:ring-[#66C2A5]/30">
                            </div>
                        </div>

                        <hr class="border-slate-200 my-4">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">是否顯示 CTA 按鈕</label>
                                <select name="HERO_CTA_SHOW" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-slate-800 outline-none focus:ring-2 focus:ring-[#66C2A5]/30">
                                    <option value="1" <?= ($settings['HERO_CTA_SHOW']['setting_value'] ?? '1') == '1' ? 'selected' : '' ?>>顯示 (開啟)</option>
                                    <option value="0" <?= ($settings['HERO_CTA_SHOW']['setting_value'] ?? '') == '0' ? 'selected' : '' ?>>不顯示 (關閉)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">CTA 按鈕文字</label>
                                <input type="text" name="HERO_CTA_TEXT" value="<?= htmlspecialchars($settings['HERO_CTA_TEXT']['setting_value'] ?? '參與連署實證') ?>"
                                       class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-slate-800 outline-none focus:ring-2 focus:ring-[#66C2A5]/30">
                            </div>
                        </div>
                    </div>
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
