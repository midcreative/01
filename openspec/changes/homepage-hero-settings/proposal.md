## Why

目前前台首頁的多個區塊（如 Hero 區塊、鄉鎮足跡、行動白皮書、連署實證站等）標題與文字大多為寫死或分散在程式碼中，且後台尚無完整的統一介面可供管理員自行調整。建立一個整合式的「首頁與 Hero 區塊設定」介面，能讓管理員無需修改程式碼，即可隨時更新首頁活動視覺（Hero 橫幅）與各區塊之主副標題。

## What Changes

- 在後台 `系統設定` 頁面中，新增一個完整的「首頁與 Hero 區塊設定」卡片群組。
- 支援修改 Hero 標籤、背景圖片、背景模糊效果設定。
- 支援修改「鄉鎮足跡」、「行動白皮書」、「連署實證站」的主標題與副標題。
- 支援修改連署區塊是否顯示 CTA 按鈕，及其按鈕文字。
- 將上述欄位寫入 `settings` 資料表。
- 更新前台首頁 (`index.php`) 對應區塊，將寫死的文字改為讀取上述設定值。

## Capabilities

### New Capabilities
- `homepage-hero-settings`: 管理前台首頁各區塊標題與 Hero 橫幅視覺呈現的各項系統設定。

### Modified Capabilities
- `hero-section` (如果存在): 擴充原本僅有的 HERO_BG_IMAGE，增加標籤、模糊遮罩等功能。

## Impact

- `admin/src/Views/settings/index.php`: 新增大量輸入表單欄位。
- `admin/src/Controllers/SettingController.php`: 擴充 `allowedKeys` 陣列以允許儲存新的欄位。
- `index.php`: 前台首頁讀取 `settings` 表並動態渲染標題。
