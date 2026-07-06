## Why

The current Hero section on the homepage overlays dynamic text (title and subtitle) on top of the hero background image. However, when users upload a custom hero image that already contains typography or text designs, the overlaid HTML text clashes with the image's text, resulting in a messy and unreadable visual presentation. Users need a way to explicitly hide the overlaid text so only the background image is displayed.

## What Changes

- Add a new "Show Hero Text Overlay" (顯示首頁主視覺文字) toggle to the Hero Settings section in the Admin Panel.
- Modify the frontend `index.php` to conditionally render the Hero text (and its text-shadow/background gradient if any) based on this new toggle.
- When toggled off, only the background image will be visible in the Hero section, avoiding visual clashes.

## Capabilities

### New Capabilities
- `hero-text-toggle`: Ability to toggle the visibility of the hero text overlay via the admin panel.

### Modified Capabilities


## Impact

- **Database**: Needs a new key `HERO_SHOW_TEXT` (boolean/string value) in the `settings` table.
- **Backend Admin API/Controller**: `SettingController.php` needs to handle the new toggle setting.
- **Admin Frontend**: The Admin settings UI needs a new switch/checkbox in the Hero settings block.
- **Public Frontend**: `index.php` will update its HTML and JavaScript (`switchView` or related rendering logic) to respect the `HERO_SHOW_TEXT` setting.
