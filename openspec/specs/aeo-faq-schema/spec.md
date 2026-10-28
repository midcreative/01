# AEO FAQ Schema

## Purpose

Expose whitepaper policies as AI-friendly Q&A through FAQPage JSON-LD schema so that generative AI engines can accurately summarize the candidate's stances.

## Requirements

### Requirement: FAQ JSON schema generation
The system MUST dynamically generate a valid `FAQPage` JSON-LD snippet containing `Question` and `Answer` pairs whenever a public page displaying whitepapers is rendered, pulling the Q&A text from the `whitepaper_pillars` database table.

#### Scenario: User visits homepage with whitepapers
- **WHEN** a user or crawler visits the homepage (which renders `Whitepaper::allActive()`)
- **THEN** the `<head>` of the page MUST contain an `application/ld+json` script block with `@type: FAQPage` that includes the defined questions and answers for each whitepaper.

### Requirement: Whitepaper Q&A data storage
The `whitepaper_pillars` table MUST store an optional JSON or text representation of the FAQ data so it can be managed by administrators.

#### Scenario: Managing whitepaper AEO data
- **WHEN** an admin creates or edits a whitepaper pillar
- **THEN** they MUST be able to save AI-friendly Q&A text to a dedicated field in the database.
