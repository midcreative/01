# Proposal

## Why

目前前台首頁 (`index.php`) 的頂部導航欄選單設定為 `hidden lg:flex`（僅在螢幕寬度 `>= 1024px` 顯示），且在由 `demo/index.html` 轉移至 `index.php` 時漏掉了手機版漢堡選單按鈕與下拉選單。這導致使用手機、平板或窄視窗瀏覽的選民完全看不到導航選單，無法切換至「行動白皮書」、「連署實證站」與「志工招募」。為了解決此問題並提升長輩與行動端選民的操作體驗，我們採用「方案 B」：同時補回頂部漢堡下拉選單，並新增手機底部固定導航列（Bottom App Bar）。

## What Changes

- **頂部導航欄漢堡選單 (`index.php`)**：在 `<nav>` 右側新增行動版漢堡按鈕（`lg:hidden`），點擊可展開/收合 `#mobile-menu` 下拉選單，內含「鄉鎮足跡」、「行動白皮書」、「連署實證站」、「志工招募」四個導航項目。
- **手機底部固定導航列 (`index.php`)**：在頁面底部新增 `lg:hidden` 的固定式 Bottom App Bar（搭配 Lucide 圖示與文字），讓手機使用者可用單手大拇指直接切換「鄉鎮足跡」、「行動白皮書」、「連署實證站」或前往「志工招募」。
- **選取狀態同步與防遮擋調整 (`index.php`)**：更新 `switchView(name)` 與新增 `toggleMobileMenu()` JavaScript 函式，確保切換分頁時，桌面頂部選單、手機下拉選單與手機底部固定導航列的選中狀態（`brand-green`）同步更新，並為 Footer 增加手機版底部內距（`pb-24 lg:pb-20`）避免內容遭底部導航列遮擋。
- **後台選單維持現狀**：依決策不變動 CMS 後台 (`AdminLayout.php`) 選單。

## Capabilities

### New Capabilities
- `mobile-navigation`: 提供前台行動裝置與小視窗（`< 1024px`）的頂部漢堡下拉選單與底部固定導航列（Bottom App Bar），並與桌面版分頁切換狀態同步。

### Modified Capabilities
- (無既有規格變更)

## Impact

- **前端 (`index.php`)**：修改 `<nav>` 結構、新增底部固定導航列 `<nav id="mobile-bottom-nav">`、調整 `<footer>` 底部間距，並擴充前端 JavaScript (`toggleMobileMenu` 與 `switchView`)。
- **後台 (`admin/`)**：完全不受影響，維持現狀。
