## ADDED Requirements

### Requirement: Frontend links to post details bypass Rewrite rules
The system SHALL use direct parameterized queries for post links (`post/index.php?slug=...`) instead of relying on URL rewriting (`/post/...`) to ensure compatibility across all deployment platforms.

#### Scenario: User clicks read more on a post
- **WHEN** user clicks "閱讀更多" on the frontend
- **THEN** user is navigated to `post/index.php?slug={post_slug}` and successfully views the post content.
