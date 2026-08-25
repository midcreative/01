## ADDED Requirements

### Requirement: Database Safety, Migration Reliability, and Security Hardening
The system SHALL prevent accidental cascading data loss, ensure idempotent migrations without character corruption, preserve existing admin configuration settings, and block crawler access to admin routes.

#### Scenario: Preventing cascading deletion of applications and signatures
- **WHEN** an admin deletes a volunteer job or petition with existing records
- **THEN** the database rejects or prevents cascading destruction of citizen applications and LINE petition signatures

#### Scenario: Settings persistence across updates
- **WHEN** an administrator saves settings on `/admin/settings` including Hero section texts and API keys
- **THEN** the system persists all allowed setting keys into the `settings` table without discarding any valid field

#### Scenario: Search engine crawler blocking
- **WHEN** a search engine crawler inspects `/robots.txt`
- **THEN** it encounters `Disallow: /admin/` and `Disallow: /api/` directives
