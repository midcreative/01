## Why

使用者在測試站 (`demo10.midcreative.com`) 部署時首頁有一個 Hero 圖片（橫幅圖片），但在正式網域（`panlingyi.tw`）的環境中該圖片卻不見了。經過盤查，這是在代碼重構或還原的過程中遺漏了 `HERO_BG_IMAGE` 相關的背景圖片渲染與設定邏輯，導致首頁 `index.php` 沒有從資料庫中拉取並套用這些設定。為了讓正式站點能夠顯示測試站相同的 Hero 圖片，我們需要將該功能加回。

## What Changes

- 在 `index.php` 讀取資料庫時，將 `settings` 資料表的內容拉取出來並建立 `$settingsMap`。
- 在 `index.php` 的 `<header>` 元素中，若 `$settingsMap['HERO_BG_IMAGE']` 存在，則套用對應的背景圖片 (`background-image`) 及外觀樣式（圓角、陰影、比例等）。
- 補回原先切換視圖（如「鄉鎮足跡」、「行動白皮書」、「連署實證站」）時，動態替換 Hero 標題的 JS 邏輯，使文字與圖片能夠一併正常顯示。
- 確保這些修改不會破壞現有的中文編碼與頁面排版。

## Capabilities

### New Capabilities
- `hero-section`: 定義了如何在首頁套用及動態切換 Hero 橫幅圖片與文字。

### Modified Capabilities

## Impact

- `index.php`: 需要補回資料庫查詢邏輯以及對應的 HTML/JS 渲染邏輯。
- 不會影響現有資料庫結構，因為 `settings` 表與對應資料已經存在於系統中。
