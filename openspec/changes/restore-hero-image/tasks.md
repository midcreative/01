## 1. PHP Database Query

- [x] 1.1 In `index.php`, query the `settings` table and build `$settingsMap` within the `try-catch` block.

## 2. Hero Background Rendering

- [x] 2.1 In `index.php`, conditionally inject `background-image` and related tailwind classes to the `<header>` element if `$settingsMap['HERO_BG_IMAGE']` exists.

## 3. Dynamic Text Rendering (JS)

- [x] 3.1 Expose `$settingsMap` to a global JavaScript variable `heroSettings` in `index.php`.
- [x] 3.2 Update the `switchView()` JavaScript function in `index.php` to swap the title and subtitle based on `heroSettings` text configurations (e.g. `HERO_HOME_TITLE_1`).
