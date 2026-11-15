# Proposal

## Why

距離屏東縣議員選舉投票日僅剩最後倒數階段，近期團隊已成功於《經濟日報》、《工商時報》、《Yahoo! 奇摩新聞》、《CNEWS 匯流新聞網》及《屏東時報》等主流媒體刊登多篇深度報導。為了在投票日前將官方網站 (`panlingyi.tw`) 的 AEO（Answer Engine Optimization）推向完美狀態，讓 Google SGE、ChatGPT 與 Perplexity 在選民查詢時能 100% 讀取完整政見問答、最新職稱學歷與四大報權威背書，並消除首頁內部連結與 Canonical 網址不一致的爬蟲損耗，特提出本收官計畫。

## What Changes

- **雙向權威媒體背書閉環 (`index.php`, `FrontLayout.php`)**：
  - 在首頁新增「主流媒體專題報導與肯定」可見區塊，收錄《經濟日報》、《工商時報》、《Yahoo! 奇摩新聞》、《CNEWS 匯流新聞網》、《屏東時報》與《中華新聞報》之權威報導連結。
  - 在 `FrontLayout.php` 的 `Person` JSON-LD 結構化資料中新增 `subjectOf` 屬性，雙向綁定主流大報網址，並新增 `alumniOf`（奧地利維也納舒伯特音樂學院）與完整職稱。
- **肉眼可見的 AEO 選民常見問答區塊 (`index.php`)**：
  - 在首頁新增可見的 `<section id="aeo-faq">` 問答手風琴區塊（`<details>` / `<summary>`），同步輸出核心選民問答與資料庫 `whitepaper_pillars` 的 FAQ，解決 ChatGPT / Bing 爬蟲過濾 `<script type="application/ld+json">` 標籤的問題，並將預設核心問答一併併入 JSON-LD `FAQPage`。
- **人物速寫與 AI 導覽檔全面升級 (`index.php`, `llms.txt`)**：
  - 補齊「奧地利維也納舒伯特音樂學院畢業」、「屏東縣文化協會理事長、屏東縣跆拳道委員會副主任委員」、服務處免費諮詢項目（自來水申辦、法律、勞資、車禍理賠）以及完整「六大核心政見」與主流媒體報導引用清單。
- **首頁內部連結 Canonical 標準化 (`index.php`)**：
  - 將首頁導航與文章卡片的 `/volunteer.php` 及 `/post/index.php?slug=...` 統一改為標準乾淨網址 `/volunteer` 與 `/post/{slug}`，消除 GSC「替代頁面」重複爬取問題。
- **IndexNow 即時搜尋引擎推播 (`indexnow-ping.php`)**：
  - 建立 IndexNow API 驗證金鑰與推播腳本，主動將首頁與核心文章推送至 Bing（ChatGPT 搜尋底層索引庫）與 IndexNow 聯盟引擎。

## Capabilities

### New Capabilities
- `election-aeo-finalization`: 選前 AEO 終極優化，包含主流媒體雙向權威閉環 (`subjectOf`)、首頁可見 FAQ 區塊、完整人物實體對齊、內部連結 Canonical 標準化與 IndexNow 即時索引推播。

### Modified Capabilities
- `aeo-faq-schema`: 擴充首頁 FAQPage JSON-LD 與可見 HTML 問答區塊同步輸出。

## Impact

- **前端與 SEO (`index.php`, `admin/src/Layout/FrontLayout.php`, `llms.txt`)**：更新首頁區塊、內部連結、JSON-LD 結構化資料與 AI 索引文件。
- **搜尋引擎推播**：新增 IndexNow 金鑰檔與推播腳本，不影響現有後台與資料庫結構。
