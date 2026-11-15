## ADDED Requirements

### Requirement: 主流媒體報導雙向權威閉環
系統必須 (SHALL) 在首頁呈現主流媒體報導專區，並於 `Person` JSON-LD 結構化資料中透過 `subjectOf` 屬性宣告主流媒體報導連結，建立官方網站與權威新聞媒體的雙向實體關聯。

#### Scenario: AI 爬蟲與選民檢視媒體權威背書
- **WHEN** 選民或 AI 爬蟲造訪首頁 (`https://panlingyi.tw/`)
- **THEN** 頁面顯示包含《經濟日報》、《工商時報》、《Yahoo! 奇摩新聞》、《CNEWS 匯流新聞網》、《屏東時報》等報導標題與連結之專區
- **AND** `<head>` 中的 `Person` JSON-LD 包含 `subjectOf` 陣列與 `alumniOf`（奧地利維也納舒伯特音樂學院）結構化宣告

### Requirement: 首頁可見 AEO 常見問答 (Visible FAQ) 區塊
系統必須 (SHALL) 在首頁渲染肉眼可見的 HTML 常見問答區塊（使用語意化 `<details>` 與 `<summary>`），確保不解析 `<script>` 標籤的 AI 爬蟲（如 ChatGPT Search / Bing）能完整擷取所有選民常見問答與六大政見 FAQ。

#### Scenario: 檢索候選人背景、服務處與六大政見問答
- **WHEN** AI 搜尋爬蟲或使用者讀取首頁 HTML
- **THEN** 頁面與 `FAQPage` JSON-LD 同步輸出候選人背景號次、服務處免費諮詢項目及六大核心政見問答

### Requirement: 內部連結 Canonical 標準化與 IndexNow 推播
系統必須 (SHALL) 在首頁使用與 `sitemap.xml` 一致的乾淨標準網址（`/volunteer` 與 `/post/{slug}`），並提供 IndexNow 即時推播機制通知搜尋引擎更新索引。

#### Scenario: 爬蟲巡邏首頁內部連結
- **WHEN** 搜尋引擎爬蟲自首頁爬取志工招募與服務日記文章連結
- **THEN** 所有連結直接指向無 `.php` 或 `?slug=` 參數之標準 Canonical 網址
