## Context

專案目前依賴 Zeabur 部署，預設環境下 (PREBUILT_V2) 無法正確載入 Apache `.htaccess` 規則。這導致原本設計為漂亮網址 (Clean URL) 的 `/post/{slug}` 無法被導向至 `post/index.php?slug={slug}`。

## Goals / Non-Goals

**Goals:**
* 修正首頁文章列表中的「閱讀更多」連結，讓使用者點擊後能順利進入文章內文。

**Non-Goals:**
* 改變後端路由系統或強迫 Zeabur 支援 `.htaccess`，因為直接修改前端連結是最快速、風險最低且跨伺服器環境皆相容的解法。

## Decisions

* **直接修改超連結**：在 `index.php` 中，將連結由 `/post/<?= htmlspecialchars($post['slug']) ?>` 改為 `/post/index.php?slug=<?= htmlspecialchars($post['slug']) ?>`。
  * *Rationale (理由)*：避免依賴任何特定的伺服器 Rewrite 規則，無論後續部署在哪個平台（Apache, Nginx 或 Zeabur 預設環境），這個原生的 PHP 參數傳遞方式都保證有效。

## Risks / Trade-offs

* **Risk**: 網址變得比較長且帶有查詢字串 `?slug=...`，在 SEO 上可能不如完全 Clean URL 那麼簡潔。
* **Mitigation**: 對於這個展示性部落格/日記功能來說，確保連結可用優先於極致的 URL 美化。另外 `post/index.php` 內依然會設定正確的 canonical 標籤來指引搜尋引擎。
