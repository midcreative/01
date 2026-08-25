## Context

本專案為屏東第三選區擬參選人潘炩禕的官方網站與 CMS 後台。在經過多次版本疊代後，系統出現了幾個關鍵問題：
1. 志工報名表單在前端點擊送出後導向不存在的 `volunteer-apply.php`（404 報錯）。
2. 文章圖片因歷史部署覆蓋與路徑問題導致破圖，前端無容錯機制。
3. 輿情監測與候選人管理代碼已完成但未掛載至路由與後台導航。
4. 外鍵設定為 `CASCADE DELETE`，刪除父項目會連帶清空民眾報名名單與連署簽署記錄。

## Goals / Non-Goals

**Goals:**
- **完整閉環**：志工報名可順利送出並寫入 `volunteer_applications`，且具備基本的防重複提交與表單驗證。
- **全站零破圖 (Zero Broken Images)**：前端模板加入 `onerror` 自動降級與 5 大主軸專屬預設封面圖，自動校正歷史 demo 網址。
- **激活輿情戰情室**：將 `OpinionMonitorController` 與 `CandidateController` 正式掛載至 `admin/index.php` 與 `AdminLayout.php`，升級 Gemini 端點至 `gemini-1.5-flash`，並加入超時保護。
- **資料安全與防誤刪**：移除危險的 `CASCADE DELETE` 外鍵，保護志工報名與連署簽署數據；修復 Migration 編碼與 SQL。

**Non-Goals:**
- 不在此次變更中新增複雜的政治獻金線上刷卡金流系統（維持匯款資訊展示與防護）。
- 不重構整體 PHP 基礎架構（維持輕量原生 MVC 模式）。

## Decisions

### 1. 圖片防破圖與容錯架構 (Image Resilience)
- **決策**：在 `index.php` 與 `post/index.php` 引入雙重防破圖機制：
  1. PHP 端：若封面路徑包含舊 demo 網域（如 `demo10.midcreative.com`），自動去除網域保留相對路徑；若為空則依主軸給予預設圖路徑。
  2. 前端 JS：在 `<img>` 標籤加入 `onerror="this.onerror=null; this.src='/assets/defaults/' + this.dataset.categorySlug + '.webp';"`，當伺服器上實體圖檔 404 時自動切換為該主軸預設圖。
- **替代方案**：每次發文強制上傳圖片。缺點：無法修復歷史已發布文章的破圖問題。

### 2. 志工報名表單處理與防刷 (Volunteer Application Flow)
- **決策**：建立 `volunteer-apply.php`，採用 POST-Redirect-GET (PRG) 模式。
  1. 驗證 `job_id`, `name`, `phone` 必填。
  2. 檢查 `volunteer_jobs` 是否存在且 `is_active = 1`。
  3. 加入 Session 級別的提交頻率限制（防同一 Session 連續連點重複送出）。
  4. 寫入 `volunteer_applications`（狀態為 `待審核`），寫入 Session Flash 訊息後轉導回 `/volunteer.php`。

### 3. AI 輿情戰情室整合與執行緒超時保護
- **決策**：
  1. 在 `admin/index.php` 註冊所有 `/admin/opinion/*` 與 `/admin/candidates/*` 路由。
  2. 在 `AdminLayout.php` 側邊欄加入「輿情戰情室」與「候選人追蹤」選單。
  3. 將 `GeminiSentimentService.php` 的 API 端點由過期的 `gemini-pro` 更新為 `gemini-1.5-flash`。
  4. 在 `OpinionCrawlerService.php` 中使用 `curl` 替代 `file_get_contents`，並在網頁手動觸發時加入 `set_time_limit(120)`，限制每人每次手動抓取上限為 3 篇，避免網頁超時 504。

### 4. 資料庫安全防護 (Prevent Accidental Cascades)
- **決策**：
  1. 更新 `volunteer_applications` 的外鍵約束為 `ON DELETE RESTRICT`（或避免無預警級聯刪除個資）。
  2. 修正 `candidate_keywords.type` 欄位與 `opinions` 欄位（補齊 `source_name`, `content_excerpt`）。

## Risks / Trade-offs

- **[Risk]** 正式伺服器缺少 Gemini API Key。
  → **Mitigation**: 在 `GeminiSentimentService` 中加入 API Key 未設定的優雅容錯（自動標記為 `neutral` 中立），不中斷系統運行。
- **[Risk]** 現有伺服器上的 `uploads/` 目錄若權限不足導致新上傳失敗。
  → **Mitigation**: 在 `PostController` 與 `SettingController` 中加入自動 `mkdir(..., 0755, true)` 與上傳失敗之日誌記錄。
