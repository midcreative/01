## Why (為什麼需要這個修改)

目前手機版的分類統計區塊（資訊圖表卡片）是單欄顯示（`grid-cols-1`），這會導致如果分類數量較多時，整個區塊在手機上會顯得非常冗長，需要一直往下滑動。使用者希望在手機版上能將這些卡片改為「雙欄（兩欄）」顯示，讓畫面更緊湊、資訊呈現更有效率。

## What Changes (會有哪些改變)

- **Grid 排版調整**：修改 `index.php` 中的分類統計區塊外層容器，將原本的手機版單欄 `grid-cols-1` 調整為雙欄 `grid-cols-2`。
- **內部元素微調 (若需要)**：為了適應手機雙欄較窄的寬度，卡片內的標題、數字字體大小以及 padding 可能需要微調，以避免內容擁擠或破版變形。

## Capabilities (功能)

### New Capabilities (新增功能)
- (無新增功能，純粹調整排版)

### Modified Capabilities (修改的功能)
- `mobile-category-stats-grid`: 首頁分類統計卡片在手機版（小螢幕）的排版變更為雙欄。

## Impact (影響範圍)

- **前端 (`index.php`)**：僅影響分類統計區塊的 HTML class 屬性。
- **CSS**：使用 Tailwind CSS 既有的 utility classes（例如從 `grid-cols-1` 改為 `grid-cols-2`，並搭配 `text-sm` 或 `p-3` 等來微調手機版樣式）。
