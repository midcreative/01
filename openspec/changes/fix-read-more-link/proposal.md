## Why

目前前端首頁（`index.php`）中文章的「閱讀更多」連結使用了 `/post/{slug}` 的路由格式，但專案部署在 Zeabur (使用 Nginx 相關的 PREBUILT_V2) 上時，預設無法讀取 Apache 的 `.htaccess` 路由重寫規則，導致點擊後出現 404 Not Found 無法成功前往內文。

## What Changes

我們將修改文章列表的超連結寫法，使其不依賴於 Apache `.htaccess` 的重寫規則，確保在任何伺服器環境下皆可正常訪問內文。
具體來說，將 `index.php` 中的 `/post/<?= htmlspecialchars($post['slug']) ?>` 改為直接指向 `post/index.php?slug=<?= htmlspecialchars($post['slug']) ?>`。

## Capabilities

### New Capabilities

- 無

### Modified Capabilities

- 無（本變更僅為修正現有路由對應問題，不涉及新規格或業務邏輯的改變）

## Impact

- **修改檔案**：`index.php` (前端首頁)。
- **影響範圍**：首頁到單一文章頁面的跳轉連結。
