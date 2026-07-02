## ADDED Requirements

### Requirement: Category Stats Calculation
The system SHALL dynamically count the number of published posts (`is_published = 1`) belonging to each category, rather than relying on manual entries in the `stats` table.

#### Scenario: Displaying stats on homepage
- **WHEN** the homepage is loaded
- **THEN** the system queries the `post_categories` table along with the count of published posts in each category.
- **THEN** it renders the "數據看板" (Stats Dashboard) using these categories, displaying the category name as the label and the post count as the value.

### Requirement: Category Filter on Homepage
The homepage SHALL provide a way to filter posts by their category (服務主軸), similar to the existing town filter.

#### Scenario: User clicks a category filter
- **WHEN** a user clicks on a specific category button in the filter section
- **THEN** the post list dynamically updates (via JavaScript or similar mechanism) to show only posts belonging to that category.
- **THEN** the "全部類別" (All Categories) button shows all posts.
