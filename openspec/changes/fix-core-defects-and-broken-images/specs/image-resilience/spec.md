## ADDED Requirements

### Requirement: Cover Image Fallback and Self-Healing
The system SHALL prevent broken images from being displayed across all public pages (`index.php` and `post/index.php`) by providing client-side fallback handling and cleaning up legacy domain URLs.

#### Scenario: Image file missing or 404
- **WHEN** an article has a `cover_image` URL that fails to load (returns HTTP 404 or network error)
- **THEN** the client-side `onerror` handler switches the `img` source to the corresponding category default cover image without displaying a broken image icon

#### Scenario: Legacy demo domain in image path
- **WHEN** an article's `cover_image` contains a legacy domain URL (e.g. `demo10.midcreative.com`)
- **THEN** the PHP template parser strips the domain prefix and normalizes the path to a root-relative path `/uploads/...`

#### Scenario: Post without cover image
- **WHEN** a post is created or displayed without an uploaded cover image
- **THEN** the system displays the default branded banner matching the post's category theme
