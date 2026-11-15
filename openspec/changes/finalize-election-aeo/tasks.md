## 1. 首頁內部連結標準化與人物速寫升級 (`index.php`)

- [x] 1.1 將 `index.php` 中的 `/volunteer.php` 與 `/post/index.php?slug=...` 全部改為標準乾淨網址 `/volunteer` 與 `/post/{slug}`。
- [x] 1.2 升級首頁「認識潘炩禕 (About Pan Ling-Yi)」區塊，補上「奧地利維也納舒伯特音樂學院畢業」、「屏東縣跆拳道委員會副主任委員」及服務處免費諮詢項目（自來水申辦、法律、勞資、車禍理賠）。

## 2. 主流媒體報導專區與可見 FAQ 區塊 (`index.php`)

- [x] 2.1 在 `index.php` 新增「主流媒體專題報導」區塊，收錄《經濟日報》、《工商時報》、《Yahoo! 奇摩新聞》、《CNEWS 匯流新聞網》、《屏東時報》與《中華新聞報》之報導連結。
- [x] 2.2 在 `index.php` 新增肉眼可見的「選民常見問答 (FAQ)」手風琴區塊（`<details>` / `<summary>`），並將核心問答與白皮書問答同步注入 `FAQPage` JSON-LD。

## 3. 底層結構化資料與 `llms.txt` 權威升級 (`FrontLayout.php`, `llms.txt`)

- [x] 3.1 更新 `admin/src/Layout/FrontLayout.php` 的 `Person` JSON-LD，新增 `alumniOf`、`knowsAbout`（完整六大政見）與 `subjectOf`（綁定經濟日報、工商時報、Yahoo 新聞等權威報導）。
- [x] 3.2 更新 `llms.txt`，完整列出登記 8 號、奧地利維也納舒伯特音樂學院學歷、雙協會職務、六大核心政見、服務處諮詢服務與主流媒體報導連結清單。

## 4. 部署與 IndexNow 即時推播

- [x] 4.1 建立 IndexNow 金鑰驗證檔並將所有變更 Commit & Push 至 GitHub 觸發 Zeabur 部署。
- [x] 4.2 執行 IndexNow API 主動推播，通知 Bing / ChatGPT 底層搜尋引擎立即重新抓取首頁與核心文章。
