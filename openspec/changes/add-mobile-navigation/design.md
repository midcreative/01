## Context

目前 `index.php` 的頂部導航欄 `<nav>` 僅保留了 `hidden lg:flex` 的桌面版選單，缺少 `demo/index.html` 原有的手機版漢堡按鈕與 `#mobile-menu`。此外，為了讓使用手機的選民與長輩能更直覺地瀏覽「鄉鎮足跡」、「行動白皮書」、「連署實證站」與「志工招募」，我們決定採用「方案 B」：同時提供「頂部漢堡下拉選單」與「手機底部固定導航列 (Bottom App Bar)」。後台選單 (`AdminLayout.php`) 則依使用者決策維持現狀不處理。

## Goals / Non-Goals

**Goals:**
- 在 `index.php` 頂部導航欄補回 `lg:hidden` 的漢堡選單按鈕與 `#mobile-menu` 下拉選單（含志工招募連結）。
- 在 `index.php` 底部新增 `lg:hidden` 的固定式 Bottom App Bar（4 個圖示＋文字按鈕）。
- 讓 `switchView(name)` 同步更新桌面頂部選單、手機下拉選單與手機底部導航列的 Active 樣式。
- 調整 `footer` 的手機版底部 padding，避免底部固定導航列遮擋頁尾內容。

**Non-Goals:**
- 不修改 CMS 後台 (`AdminLayout.php` 與 `admin/index.php`) 的任何選單或路由。
- 不改變桌面版（`>= 1024px`）原有的導航欄視覺與排版。

## Decisions

1. **雙重行動導航結構 (`index.php`)**：
   - **頂部漢堡選單**：在「線上陳情」按鈕旁加入 `<button onclick="toggleMobileMenu()" class="lg:hidden p-2 text-slate-500 hover:brand-green">`，並在 `<nav>` 內部下方加入 `<div id="mobile-menu" class="hidden lg:hidden ...">`。
   - **底部固定導航列**：在 `<footer>` 後方加入 `<nav id="mobile-bottom-nav" class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white/95 backdrop-blur-md border-t border-[#E0F2ED] shadow-[0_-4px_20px_-5px_rgba(102,194,165,0.15)]">`，採用 `grid grid-cols-4` 平均分配四個按鈕。
2. **選取狀態同步 (`switchView`)**：
   - 桌面版頂部按鈕使用 `id="nav-{name}"` 與 `.nav-btn`。
   - 手機下拉選單按鈕使用 `id="mobile-nav-{name}"` 與 `.mobile-nav-btn`。
   - 手機底部導航列按鈕使用 `id="bottom-nav-{name}"` 與 `.bottom-nav-btn`。
   - 當呼叫 `switchView(name)` 時，同時重置並高亮上述三組按鈕對應的 `name` 項目，並呼叫 `lucide.createIcons()` 確保圖示正常渲染。

## Risks / Trade-offs

- **風險：iOS Safari 底部安全區域 (Safe Area) 與頁尾遮擋**
  - *緩解措施*：在 `<footer>` 加上 `pb-28 lg:pb-20`，並在底部導航列加上適當的 `py-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))]`，確保在 iPhone 等裝置上按鈕好點且不遮擋頁尾。
