## 1. 頂部漢堡選單實作 (`index.php`)

- [x] 1.1 在 `index.php` 頂部 `<nav>` 右側「線上陳情」按鈕旁新增 `lg:hidden` 的漢堡選單按鈕 (`onclick="toggleMobileMenu()"`)。
- [x] 1.2 在頂部 `<nav>` 內新增 `#mobile-menu` 下拉選單區塊，包含「鄉鎮足跡」、「行動白皮書」、「連署實證站」與「志工招募」四個項目。

## 2. 手機底部固定導航列實作 (`index.php`)

- [x] 2.1 在 `index.php` 新增 `lg:hidden fixed bottom-0 inset-x-0 z-50` 的手機底部固定導航列 (`#mobile-bottom-nav`)，以 4 欄網格呈現圖示與文字。
- [x] 2.2 調整 `<footer>` 的底部 padding 為 `pb-28 md:pb-28 lg:py-20`，防止手機版底部固定導航列遮擋頁尾版權文字。

## 3. JavaScript 互動與狀態同步 (`index.php`)

- [x] 3.1 新增 `toggleMobileMenu()` 函式，控制 `#mobile-menu` 顯示/隱藏並切換漢堡圖示 (`menu` / `x`)。
- [x] 3.2 更新 `switchView(name)` 函式，使其同步更新桌面頂部選單 (`#nav-*`)、手機下拉選單 (`#mobile-nav-*`) 與手機底部導航列 (`#bottom-nav-*`) 的品牌綠高亮狀態。
