## Context

目前的數據看板是透過 `stats` 資料表手動更新，這不僅增加維護負擔，且無法即時反映網站實際發布的文章篇數。為了解決此問題，我們將直接計算每個「服務主軸分類 (`post_categories`)」下的文章數量，以此動態生成數據看板。同時，首頁也需要加上分類的文章篩選功能。

## Goals / Non-Goals

**Goals:**
- 將首頁的數據看板改為讀取 `post_categories` 並 Join `posts` 計算發布數量。
- 在首頁加入以「服務主軸」為過濾條件的篩選列（或讓數據看板本身支援點擊過濾）。
- 確保前台 JS (`renderPosts`) 能同時支援鄉鎮與分類的雙重/單一過濾。

**Non-Goals:**
- 不改變後台對 `post_categories` 的管理介面（因為分類本來就存在）。
- 不刪除 `stats` 資料表，僅從前台 `index.php` 移除對它的依賴。

## Decisions

- **數據查詢方式**: 在 `index.php` 的 PHP 區塊中，將原本 `SELECT * FROM stats` 改為：
  ```sql
  SELECT c.id, c.name, c.color_theme, COUNT(p.id) as post_count 
  FROM post_categories c 
  LEFT JOIN posts p ON c.id = p.category_id AND p.is_published = 1 
  GROUP BY c.id 
  ORDER BY c.sort_order ASC
  ```
  這可以一次取得所有分類及其文章數量。
- **數據看板顯示**: 捨棄 `stats` 的 `icon_name` 和 `stat_unit`，改為統一使用一個預設 Icon（例如 `folder-heart` 或 `file-text`），並以「篇」為單位顯示 `post_count`，標題則是 `c.name`。
- **前台過濾邏輯**: 
  - 增加一個 JavaScript 變數 `currentCategory = '全部類別';`。
  - 讓點擊數據看板的卡片時，能夠觸發 `filterCategory(categoryName)`。
  - 修改 `renderPosts()`：`const matchCategory = (currentCategory === '全部類別' || card.dataset.category === currentCategory);`，並且與 `matchTown` 進行 `&&` 判斷（或依據需求，讓鄉鎮與分類互相獨立，通常是交集 `&&` 體驗較好）。
- **HTML 結構調整**: 
  - 為 `.post-item` 加上 `data-category="<?= htmlspecialchars($post['category_name']) ?>"`。
  - 將數據看板的 `<div class="bg-white p-5...">` 改為 `<button onclick="filterCategory('<?= $categoryName ?>')" class="...">` 以支援點擊過濾。並加上一個「全部類別」的按鈕或狀態。

## Risks / Trade-offs

- **Icon 遺失**: 因為 `post_categories` 表沒有 `icon_name` 欄位，所以無法像之前那樣為每個分類設定專屬 Icon。
  - Mitigation: 在設計上統一使用通用的 Icon（如 `bookmark` 或 `folder`），或者在前端透過 JS/PHP 根據分類名稱寫死對應的 Icon Map（如果分類固定的話）。為求彈性，目前先採用統一通用 Icon 或是保留原本的設計，並建議後續若有需要可由工程師在資料庫增加 `icon_name` 欄位。
- **點擊體驗**: 將數據看板變為過濾按鈕，可能會讓使用者不清楚它「可以點」。
  - Mitigation: 加入 `:hover` 效果及 `cursor-pointer`，並在板塊上方加上提示文字，例如「點擊分類查看專屬服務日記」。
