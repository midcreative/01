## ADDED Requirements

### Requirement: Admin toggle for Hero Text Overlay
The admin panel SHALL provide a toggle setting (HERO_SHOW_TEXT) to control whether the dynamic text overlay (title and subtitle) is displayed on the homepage hero section.

#### Scenario: Admin disables hero text overlay
- **WHEN** the admin disables the "Show Hero Text Overlay" setting
- **THEN** the public homepage hero section renders only the background image, without the title, subtitle, or dark overlay gradient.

#### Scenario: Admin enables hero text overlay
- **WHEN** the admin enables the "Show Hero Text Overlay" setting
- **THEN** the public homepage hero section renders the title and subtitle texts over the background image.
