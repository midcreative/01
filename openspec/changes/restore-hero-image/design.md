## Context

目前的正式站 (`index.php`) 在讀取資料庫時，並沒有拉取 `settings` 表的資料，導致無法取得 Hero Image 的背景圖設定（`HERO_BG_IMAGE`）以及各視圖對應的標題文字。測試站的舊代碼中曾經有這些功能，但在某次代碼復原或重構時被不小心移除了。

## Goals / Non-Goals

**Goals:**
- 恢復從 `settings` 資料表拉取資料，並組成 `$settingsMap` 的 PHP 陣列。
- 在 HTML 的 `<header>` 元素上動態套用 `background-image` 及相應的 CSS 樣式。
- 在前端 JavaScript 中重新引入 `heroSettings` 以處理切換視圖時的標題變更。

**Non-Goals:**
- 不涉及資料庫 Schema 的修改，預期 `settings` 資料表與相關紀錄已存在。
- 不新增或修改後台 (`admin/`) 設定頁面，僅處理前台展示邏輯。

## Decisions

- **拉取 Settings 的方式**：在 `index.php` 既有的 `try-catch` 區塊中，增加 `$settings = $pdo->query('SELECT * FROM settings')->fetchAll();` 並組裝為 `$settingsMap`。這與現有的 `$statsMap` 邏輯一致，能保持代碼風格統一，並且開銷極低。
- **背景圖片渲染邏輯**：使用 PHP 動態判斷 `$heroBg = $settingsMap['HERO_BG_IMAGE'] ?? null;`。如果存在，則加上 `<header class="... aspect-[1/1] sm:aspect-[16/9] md:aspect-[2.5/1] ... relative rounded-[2.5rem] overflow-hidden shadow-[...]" style="background-image: url('...'); background-size: cover; background-position: center;">` 的樣式；如果不存在，則回退到目前純文字居中的樣式。
- **動態替換標題文字**：在檔案底部補上 `<script>const heroSettings = <?= json_encode($settingsMap) ?>;</script>`，並在 `switchView(name)` 函式中，依據 `name` (如 `home`, `issues`, `feedback`)，從 `heroSettings` 讀取 `HERO_HOME_TITLE_1` 及 `HERO_HOME_TITLE_2` 並組裝，確保能支援 RWD 斷行邏輯 `<br class="hidden md:block">` 或直接使用 JS 中的 `breakTag`。

## Risks / Trade-offs

- **Risk: 編碼或語法錯誤導致白畫面**
  - Mitigation: `index.php` 中的資料庫拉取均包裹在 `try-catch` 中，若是 `settings` 表不存在或拋出異常，會安靜處理，並在 catch 區塊將 `$settingsMap` 設為空陣列。
- **Risk: $settingsMap 缺少對應 key**
  - Mitigation: 使用 PHP `??` null coalescing operator 及 JS `|| ''` 給予預設值，確保即便後台沒有設定對應欄位，前端也不會報錯或顯示 `undefined`。
