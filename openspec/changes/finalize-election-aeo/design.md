## Context

距離投票日僅剩最後幾天，我們已經擁有《經濟日報》、《工商時報》、《Yahoo! 奇摩新聞》、《CNEWS 匯流新聞網》、《屏東時報》等高權重外部新聞報導，且驗證發現 ChatGPT 搜尋（帶 `?utm_source=chatgpt.com`）已開始抓取相關報導。為了在選前把所有流量與 AI 信任度收斂至官網 `panlingyi.tw`，我們需要一次性完成站內外權威閉環、可見 FAQ 渲染、完整頭銜對齊與乾淨網址修正。

## Goals / Non-Goals

**Goals:**
- 在 `index.php` 首頁補齊完整人物學經歷（奧地利維也納舒伯特音樂學院、屏東縣文化協會理事長、屏東縣跆拳道委員會副主任委員）與服務處諮詢項目。
- 在 `index.php` 新增「主流媒體專題報導」區塊與「選民常見問答 (AEO FAQ)」可見 HTML 區塊。
- 在 `FrontLayout.php` 擴充 `Person` JSON-LD 的 `alumniOf`、`jobTitle` 與 `subjectOf`（綁定經濟日報、工商時報、Yahoo 新聞等權威網址）。
- 在 `llms.txt` 完整更新六大核心政見、完整學經歷、號次 8 號與主流媒體報導引用清單。
- 將 `index.php` 內的 `/volunteer.php` 與 `/post/index.php?slug=...` 統一改為 `/volunteer` 與 `/post/{slug}`。
- 透過 IndexNow API 主動通知 Bing / IndexNow 聯盟更新索引。

**Non-Goals:**
- 不更動資料庫資料表結構與後台管理介面。

## Decisions

1. **雙層 FAQ 架構（Visible HTML + JSON-LD `FAQPage`）**：
   - 除了從資料庫讀取 `$whitepapers` 的 `faq_question` 與 `faq_answer` 外，在 `index.php` 預設注入 2 題選前最高頻的關鍵問答（候選人學經歷與 8 號背景、服務處據點與免費諮詢服務），同時渲染為頁面上的 `<details>` 手風琴與 `<head>` 內的 `FAQPage` JSON-LD。
2. **主流媒體背書卡片 (`#media-coverage`)**：
   - 放置於首頁「認識潘炩禕」區塊下方或頁面下半部，展示《經濟日報》、《工商時報》、《Yahoo! 奇摩新聞》、《CNEWS 匯流新聞網》、《屏東時報》、《中華新聞報》的真實報導連結，加上 `rel="noopener"`，強化 E-E-A-T 可信度。
3. **無 BOM UTF-8 寫入規範**：
   - 嚴格確保所有 PHP 檔案（特別是 `FrontLayout.php` 與 `index.php`）以無 BOM 之 UTF-8 儲存，確保 `declare(strict_types=1);` 位於第一行最開頭。
