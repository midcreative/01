## ADDED Requirements

### Requirement: Admin UI for Homepage Settings
The system SHALL provide an interface in the admin "系統設定" (System Settings) page to configure homepage and hero section texts, images, and visual settings.

#### Scenario: Update Hero Section Settings
- **WHEN** the admin uploads a new `HERO_BG_IMAGE` and inputs a `HERO_TAG` (參選人標籤) and `HERO_BG_BLUR` (背景模糊遮罩)
- **THEN** these values are saved to the `settings` database table.

#### Scenario: Update Town Footprint Settings
- **WHEN** the admin inputs "主標題" and "副標題 (綠色斜體)" for the Town Footprint section (`TOWN_TITLE`, `TOWN_SUBTITLE`)
- **THEN** these values are saved to the `settings` database table.

#### Scenario: Update Whitepaper Settings
- **WHEN** the admin inputs "主標題" and "副標題 (綠色斜體)" for the Whitepaper section (`WHITEPAPER_TITLE`, `WHITEPAPER_SUBTITLE`)
- **THEN** these values are saved to the `settings` database table.

#### Scenario: Update Petition Settings
- **WHEN** the admin inputs "主標題", "副標題 (綠色斜體)", "是否顯示 CTA 按鈕" (`PETITION_CTA_SHOW`), and "CTA 按鈕文字" (`PETITION_CTA_TEXT`)
- **THEN** these values are saved to the `settings` database table.

### Requirement: Dynamic Rendering on Homepage
The system SHALL dynamically read these settings from the database and render the corresponding sections on the homepage. If a setting is empty or undefined, a default text or behavior SHALL be used.

#### Scenario: Render Homepage
- **WHEN** a visitor loads the homepage
- **THEN** the system reads `settings` and injects them into the respective sections (Hero, Town Footprint, Whitepaper, Petition).
