# Proposal

## Why

The current website has a great foundation for AEO (Answer Engine Optimization) and SEO with a GEO-optimized `FrontLayout` and `llms.txt`. However, the Whitepaper policies (行動白皮書) are currently stored and displayed in a format that AI crawlers (like GPTBot, ClaudeBot, and Perplexity) might not perfectly parse for contextual questions. Adding a structured `FAQPage` JSON-LD schema and an AI Q&A field for the whitepaper policies will allow AI search engines to accurately and fully comprehend Pan Ling-Yi's specific stances on issues like agriculture, youth empowerment, and elderly care.

## What Changes

- Add a new database column `faq_json` (or `ai_qna_text`) to the `whitepaper_pillars` table to store AI-friendly Q&A translations.
- Update the admin panel to allow staff to input the Q&A text for each whitepaper pillar.
- Modify `App\Layout\FrontLayout::render` to dynamically generate and inject `FAQPage` JSON-LD schema into the `<head>` when rendering the public site, based on the Q&A data.

## Capabilities

### New Capabilities
- `aeo-faq-schema`: Expose whitepaper policies as AI-friendly Q&A through `FAQPage` JSON-LD schema.

### Modified Capabilities
- (None)

## Impact

- **Database**: `whitepaper_pillars` table schema modified.
- **Admin**: Whitepaper CRUD UI updated.
- **Frontend**: `FrontLayout.php` rendering logic expanded to include `FAQPage` schema.
