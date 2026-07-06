## 1. Backend Updates

- [x] 1.1 In `admin/src/Controllers/SettingController.php`, add `'HERO_SHOW_TEXT'` to the `$allowedKeys` array to allow the toggle value to be saved.

## 2. Admin UI Updates

- [x] 2.1 In `admin/src/Views/settings/index.php`, add a new settings row for "顯示主視覺文字 (Show Hero Text)".
- [x] 2.2 Inside that row, add a hidden input `<input type="hidden" name="HERO_SHOW_TEXT" value="0">`.
- [x] 2.3 Immediately following the hidden input, add a checkbox `<input type="checkbox" name="HERO_SHOW_TEXT" value="1">`. Add `checked` attribute if `$settings['HERO_SHOW_TEXT']['setting_value'] ?? '1' === '1'`.

## 3. Frontend Updates

- [x] 3.1 In `index.php`, inside the `switchView()` JavaScript function, check the value of `heroSettings.HERO_SHOW_TEXT`. Treat undefined as `'1'`.
- [x] 3.2 If the value is `'0'`, hide the hero title and subtitle DOM elements, and remove or hide any dark gradient/shadow overlays that sit behind the text.
- [x] 3.3 If the value is `'1'`, ensure the text and overlays are visible.
