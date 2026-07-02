## Context

根據需求，管理員希望能透過後台「系統設定」介面，自主變更前台首頁的多個區塊文字（主副標題）與 Hero 區塊設定，而不用透過工程師修改程式碼。因為已經具備了 `settings` 資料表，我們可以利用既有的 Key-Value 架構來實作這項需求。

## Goals / Non-Goals

**Goals:**
- 在 `admin/src/Views/settings/index.php` 實作對應的使用者介面，包含卡片式表單分區。
- 讓 `SettingController::update()` 支援並儲存以下新 Key 集合：
  - `HERO_TAG` (參選人標籤)
  - `HERO_BG_BLUR` (背景模糊遮罩：開啟/關閉)
  - `TOWN_TITLE`, `TOWN_SUBTITLE` (鄉鎮足跡主副標題)
  - `WHITEPAPER_TITLE`, `WHITEPAPER_SUBTITLE` (行動白皮書主副標題)
  - `PETITION_TITLE`, `PETITION_SUBTITLE` (連署實證站主副標題)
  - `PETITION_CTA_SHOW`, `PETITION_CTA_TEXT` (連署實證站 CTA 設定)
- 修改 `index.php`，讓前台對應區塊改為從 `$settingsMap` 讀取資料。

**Non-Goals:**
- 改變資料庫 `settings` 資料表的結構（依然維持 `setting_key` 與 `setting_value` 兩欄）。

## Decisions

- **UI 實作方式**: 在後台 `settings/index.php` 內使用 Grid 切割出三個卡片區塊（鄉鎮足跡、行動白皮書、連署實證站），並且更新先前的 Hero 區塊以包含 `HERO_TAG` 與 `HERO_BG_BLUR` 欄位。
- **預設值處理**: 如果使用者清空欄位，資料庫儲存為空字串。前台在 `$settingsMap` 讀取時，若遇到空字串或不存在該 Key，可透過 PHP 的 `??` 或 `empty()` 判斷後提供一個優雅的「預設文案」，避免版面空白破圖。
- **圖片模糊處理**: 前台若 `HERO_BG_BLUR` 為 `'1'`，可以在 CSS 或 style 加上 `backdrop-filter: blur(...)` 或者將設定傳遞至前端。

## Risks / Trade-offs

- **設定項暴增**: 隨著未來首頁區塊變多，`settings` 的表單會越來越長。
  - Mitigation: 我們已經採用卡片群組 (Grid Layout) 來排版，維持了較好的視覺層級。未來如果過多，可考慮拆分頁籤。
