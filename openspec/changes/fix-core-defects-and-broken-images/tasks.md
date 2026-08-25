## 1. 志工招募閉環與防刷 (Volunteer Application Flow)

- [x] 1.1 建立 `volunteer-apply.php`，處理 POST 表單驗證、寫入 `volunteer_applications` 與 Session Flash 訊息
- [x] 1.2 更新 `volunteer.php`，加入 Session 提示橫幅（成功/錯誤）與提交防重複點擊處理

## 2. 全站圖片防破圖與容錯 (Image Resilience)

- [x] 2.1 在 `index.php` 首頁服務日記列表加入 `onerror` 降級處理與舊網址路徑自動校正
- [x] 2.2 在 `post/index.php` 詳情頁加入 `onerror` 降級處理與舊網址路徑自動校正
- [x] 2.3 建立 5 大主軸精美預設圖資或 Fallback Banner，確保未上傳或圖檔 404 時呈現專業視覺
- [x] 2.4 在 `uploads/` 目錄建立 `.gitkeep` 確保目錄結構持久化

## 3. AI 輿情戰情室與候選人模組啟用 (Opinion Monitor Activation)

- [x] 3.1 修正 `CandidateController.php` 函式簽名與參數匹配
- [x] 3.2 在 `admin/index.php` 註冊所有 Opinion 與 Candidate 路由
- [x] 3.3 在 `AdminLayout.php` 側邊欄加入「輿情戰情室」與「候選人追蹤」選單項目
- [x] 3.4 升級 `GeminiSentimentService.php` API 端點至 `gemini-1.5-flash` 並加入超時保護
- [x] 3.5 優化 `OpinionCrawlerService.php`，改用 cURL 與執行時間上限控制，防止網頁 504 超時

## 4. 資料庫防護、系統設定與環境修復 (System Stabilization)

- [x] 4.1 補齊 `SettingController.php` 缺少之 Hero 標題等欄位白名單
- [x] 4.2 修復 `admin/migration_*.php` 檔案的編碼損毀與 SQL 語法
- [x] 4.3 清理 `api/petition-propose.php` 殘留死代碼，確保 API 目錄乾淨無語法錯誤
- [x] 4.4 更新 `robots.txt` 加入 `Disallow: /admin/` 與 `Disallow: /api/`
- [x] 4.5 驗證前後台各頁面功能與防破圖效果
