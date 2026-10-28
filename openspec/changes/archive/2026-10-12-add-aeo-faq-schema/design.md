# Design

## Context

We are adding an AEO-friendly FAQ schema generated dynamically from the whitepaper policies. Currently, `App\Models\Whitepaper` handles CRUD operations on `whitepaper_pillars`. The front-end view `index.php` loads all active pillars via `Whitepaper::allActive()` and renders them. The HTML head is managed by `App\Layout\FrontLayout::render()`, which already injects JSON-LD for `Article` and `Person`.

## Goals / Non-Goals

**Goals:**
- Provide a structured way to store Q&A format of Whitepapers in MySQL.
- Inject an additional `FAQPage` schema into the `<head>` without overwriting the existing `Person` or `Article` schemas.

**Non-Goals:**
- Refactoring the entire `FrontLayout` structure.
- Modifying how existing `Article` or `Person` schemas are handled.

## Decisions

1. **Database Schema Update**
   - **Decision:** Add two TEXT columns to the `whitepaper_pillars` table: `faq_question` and `faq_answer`.
   - **Rationale:** Compared to storing raw JSON, having two dedicated text fields guarantees that non-technical admin staff can safely input Q&A content without causing syntax errors. It provides a foolproof UX.

2. **FAQ Data Structure in PHP**
   - **Decision:** When rendering the front end, PHP will read `faq_question` and `faq_answer`. If both are non-empty, they will be combined into a valid `FAQPage` JSON-LD object.

3. **FrontLayout Rendering**
   - **Decision:** Instead of mutating the existing `buildSchema` to support multiple types, `FrontLayout::render` will accept an optional array of `$seo['additional_schemas']`. It will loop through these and output separate `<script type="application/ld+json">` tags.
   - **Rationale:** Google supports multiple JSON-LD script blocks per page. This safely decouples the new FAQ schema from the existing core schema, preventing conflicts.

## Risks / Trade-offs

- **Risk:** Existing database records won't have the new `faq_question` and `faq_answer` fields populated.
  - **Mitigation:** The schema builder logic MUST check `!empty($w['faq_question'])` and fallback gracefully (i.e., skip adding the FAQ item).
