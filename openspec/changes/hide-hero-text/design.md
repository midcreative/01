## Context

The system currently renders a text overlay (title and subtitle) on the homepage hero image. When users upload hero images containing their own typography, the HTML text overlay creates visual noise. We need to introduce an admin toggle to hide this overlay.

## Goals / Non-Goals

**Goals:**
- Provide an admin UI to toggle the display of the homepage hero text overlay.
- Ensure the public frontend respects this setting, hiding the text and any related styling (like dark background gradient/shadows) when toggled off.

**Non-Goals:**
- Removing the text data from the database entirely. (Data is preserved in case they toggle it back on).

## Decisions

**1. Database and Backend Updates**
- We will use the existing `settings` table. We'll introduce a new key `HERO_SHOW_TEXT`.
- In `SettingController.php`, we will add `HERO_SHOW_TEXT` to the `$allowedKeys` array so it can be updated via the admin form.

**2. Admin UI Implementation**
- In `admin/src/Views/settings/index.php`, we will add a checkbox for "顯示主視覺文字 (Show Hero Text)".
- **Alternative Considered**: A select dropdown (Yes/No). **Rationale**: A checkbox is a more idiomatic UI for a simple boolean toggle. To ensure unchecking sends a value, we will place a hidden input with value `0` immediately preceding the checkbox with value `1`.

**3. Frontend Rendering (`index.php`)**
- `index.php` already dumps the `$settingsMap` into a JS variable `heroSettings`.
- We will update the `switchView` or initialization logic to check `heroSettings.HERO_SHOW_TEXT`.
- If the value is `'0'`, we will apply a CSS class (e.g., `hidden` or `opacity-0`) to the text container or directly manipulate the DOM to hide the title, subtitle, and any dark gradient overlays. If it is `'1'` or undefined (backward compatibility), we show the text.

## Risks / Trade-offs

- [Risk] If the text is hidden but the dark overlay remains, the image might look unnecessarily dim.
  → Mitigation: Ensure the conditional logic in `index.php` hides both the text elements and the container's background gradient classes.
