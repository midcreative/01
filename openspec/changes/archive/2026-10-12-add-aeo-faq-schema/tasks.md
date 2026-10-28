# Tasks

## 1. Database Update

- [x] 1.1 Add `faq_question` (TEXT) and `faq_answer` (TEXT) columns to `whitepaper_pillars` table in MySQL and verify columns exist via CLI or DB tool.

## 2. Admin Panel Updates

- [x] 2.1 Update the admin Whitepaper model/form to include two textareas for `faq_question` and `faq_answer`.
- [x] 2.2 Update the Whitepaper CRUD controller logic to save and retrieve `faq_question` and `faq_answer`. Verify by creating and editing a Whitepaper pillar with FAQ data in the admin dashboard.

## 3. Frontend Layout Updates

- [x] 3.1 Update `App\Layout\FrontLayout::render` to accept an optional `$seo['additional_schemas']` array and render a separate `<script type="application/ld+json">` for each item inside it.
- [x] 3.2 Update `index.php` (or wherever Whitepapers are rendered) to extract non-empty `faq_question` and `faq_answer` pairs from `$whitepapers`, construct a `FAQPage` schema object, and pass it to `FrontLayout::render` via `additional_schemas`.
- [x] 3.3 Verify by loading the homepage and inspecting the page source to ensure the `application/ld+json` script tag for `FAQPage` is correctly formed, contains the questions and answers, and doesn't overwrite the original `Person` schema.
