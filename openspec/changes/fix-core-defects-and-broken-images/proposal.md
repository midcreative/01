## Why

當前網站存在數個影響運行的核心缺陷：志工報名表單缺少後端處理導致 404、AI 輿情戰情室與候選人管理模組未掛載於後台、過去版本更新導致既有文章圖片頻繁破圖，以及外鍵串聯刪除導致民眾報名資料存在無預警遺失風險。本提案旨在全面修復上述缺陷，並建立「零破圖、零資料遺失」的防禦性維護標準。

## What Changes

- **志工報名閉環與防刷**：建立 `volunteer-apply.php` 接收端，加入防重複提交與表單驗證，前台 `volunteer.php` 整合 Flash Alert 狀態提示。
- **全站圖片防破圖與自動容錯 (Image Resilience)**：前端模板加入 `onerror` 自動降級為 5 大主軸專屬精美預設圖；過濾舊 demo 網域名稱；確保 `/uploads/` 持久化。
- **啟用 AI 輿情戰情室**：在 `admin/index.php` 與 `AdminLayout.php` 補齊路由與側邊欄；升級 Gemini API 模型端點至 `gemini-1.5-flash`；增加爬蟲執行超時保護。
- **資料庫安全與防連鎖刪除**：解除 `volunteer_applications` 與 `petition_signatures` 的破壞性 `CASCADE DELETE` 外鍵，防止刪除職缺或提案時誤刪民眾報名與連署紀錄。
- **修復 Migration 與設定白名單**：修正所有 migration 檔案的編碼與 SQL 語法；補齊 `SettingController` 缺少之 Hero 標題等欄位；清理無效的死代碼；在 `robots.txt` 封鎖後台與 API。

## Capabilities

### New Capabilities
- `volunteer-recruitment`: 志工報名後端處理、防刷驗證與前台狀態回饋機制。
- `image-resilience`: 前台封面圖片防破圖降級、主軸預設圖集與舊網址自我修復。
- `opinion-monitor`: 後台 AI 輿情戰情室、候選人關鍵字追蹤與 Gemini 1.5 情緒分析整合。
- `system-stabilization`: 資料庫外鍵安全防護、Migration 冪等修復、全域設定白名單與安全配置。

### Modified Capabilities
<!-- 無既有 specs 變更，全部為新規格 -->

## Impact

- **影響檔案**：`volunteer.php`, `volunteer-apply.php`, `index.php`, `post/index.php`, `admin/index.php`, `admin/src/Layout/AdminLayout.php`, `admin/src/Controllers/*`, `admin/src/Services/GeminiSentimentService.php`, `admin/src/Services/OpinionCrawlerService.php`, `admin/migration_*.php`, `robots.txt`
- **資料庫**：修復 `opinions` 與 `candidate_keywords` 資料表欄位定義，更新外鍵約束為安全防護模式。
- **外部依賴**：Gemini API (generativelanguage.googleapis.com) 端點升級。
