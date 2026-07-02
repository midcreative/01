## ADDED Requirements

### Requirement: Index PHP Settings Fetch
The system SHALL query the `settings` database table in `index.php` and load them into a key-value `$settingsMap`.

#### Scenario: Settings are loaded correctly
- **WHEN** a user visits the homepage
- **THEN** the PHP variable `$settingsMap` is populated with `HERO_BG_IMAGE`, `HERO_HOME_TITLE_1`, etc.

### Requirement: Hero Image Rendering
The homepage `<header>` element SHALL apply the value of `HERO_BG_IMAGE` as its inline background image if the setting is present in the database.

#### Scenario: HERO_BG_IMAGE exists
- **WHEN** the `HERO_BG_IMAGE` setting is defined
- **THEN** the `<header>` element renders with `style="background-image: url('...'); background-size: cover; background-position: center;"` and specific CSS classes for styling the hero image banner.

#### Scenario: HERO_BG_IMAGE does not exist
- **WHEN** the `HERO_BG_IMAGE` setting is empty or undefined
- **THEN** the `<header>` element falls back to a text-only presentation without background image styles.

### Requirement: Hero Dynamic Text Loading
The homepage SHALL export `$settingsMap` to a JavaScript variable `heroSettings` and use it to dynamically render titles when switching between views.

#### Scenario: Switching views with hero text available
- **WHEN** the user switches to 'home', 'issues', or 'feedback' view
- **THEN** the JS function `switchView` updates the `#main-title` element using the text configurations in `heroSettings` (e.g. `HERO_HOME_TITLE_1`, `HERO_HOME_TITLE_2`).
