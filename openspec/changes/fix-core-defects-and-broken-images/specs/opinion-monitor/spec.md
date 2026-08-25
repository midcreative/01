## ADDED Requirements

### Requirement: Admin Opinion Monitor and Candidate Management Integration
The system SHALL provide administrative routes and UI navigation for candidate tracking, keyword configuration, and sentiment analysis dashboard.

#### Scenario: Admin views opinion dashboard
- **WHEN** an authenticated administrator navigates to `/admin/opinion/dashboard`
- **THEN** the system renders the Opinion Monitor dashboard displaying candidate sentiment statistics, opponent comparison charts, and recent crawled opinions

#### Scenario: Admin manages candidates and keywords
- **WHEN** an administrator adds or deletes candidates or keywords on `/admin/candidates`
- **THEN** the system persists changes in `candidates` and `candidate_keywords` tables with correct `type` enum values (`alias`, `issue`, `negative`)

#### Scenario: Sentiment analysis with Gemini 1.5
- **WHEN** the crawler or admin triggers sentiment analysis for an article
- **THEN** the service sends a structured prompt to Google Gemini API (`gemini-1.5-flash`) and parses the response into `positive`, `neutral`, or `negative`
