## 1. Backend Controller Updates

- [x] 1.1 In `admin/src/Controllers/SettingController.php`, update the `$allowedKeys` array to include the new keys: `HERO_TAG`, `HERO_BG_BLUR`, `TOWN_TITLE`, `TOWN_SUBTITLE`, `WHITEPAPER_TITLE`, `WHITEPAPER_SUBTITLE`, `PETITION_TITLE`, `PETITION_SUBTITLE`, `PETITION_CTA_SHOW`, `PETITION_CTA_TEXT`.

## 2. Backend UI Updates

- [x] 2.1 In `admin/src/Views/settings/index.php`, add input fields for `HERO_TAG` and a dropdown for `HERO_BG_BLUR` within the Hero section settings.
- [x] 2.2 In `admin/src/Views/settings/index.php`, add a new section card for "鄉鎮足跡 (首頁)" with inputs for `TOWN_TITLE` and `TOWN_SUBTITLE`.
- [x] 2.3 In `admin/src/Views/settings/index.php`, add a new section card for "行動白皮書" with inputs for `WHITEPAPER_TITLE` and `WHITEPAPER_SUBTITLE`.
- [x] 2.4 In `admin/src/Views/settings/index.php`, add a new section card for "連署實證站" with inputs for `PETITION_TITLE`, `PETITION_SUBTITLE`, `PETITION_CTA_SHOW` (dropdown), and `PETITION_CTA_TEXT`.
- [x] 2.5 Ensure all inputs use `htmlspecialchars($settings['KEY']['setting_value'] ?? '')` to pre-fill existing values.

## 3. Frontend Integration

- [x] 3.1 In `index.php`, inject `$settingsMap['HERO_TAG']` into the Hero section (if set).
- [x] 3.2 In `index.php`, apply CSS filter (e.g. `backdrop-filter: blur(10px)`) to the Hero background if `$settingsMap['HERO_BG_BLUR']` is `'1'`.
- [x] 3.3 In `index.php`, replace the hardcoded "鄉鎮足跡" title and subtitle with `$settingsMap['TOWN_TITLE']` and `$settingsMap['TOWN_SUBTITLE']`.
- [x] 3.4 In `index.php`, replace the hardcoded "行動白皮書" title and subtitle with `$settingsMap['WHITEPAPER_TITLE']` and `$settingsMap['WHITEPAPER_SUBTITLE']`.
- [x] 3.5 In `index.php`, replace the hardcoded "連署實證站" title and subtitle with `$settingsMap['PETITION_TITLE']` and `$settingsMap['PETITION_SUBTITLE']`.
- [x] 3.6 In `index.php`, conditionally display the Petition CTA button based on `$settingsMap['PETITION_CTA_SHOW']` and use `$settingsMap['PETITION_CTA_TEXT']` for its label.
