## 1. PHP Database Query Update

- [x] 1.1 In `index.php`, remove the `$stats` and `$statsMap` query logic.
- [x] 1.2 In `index.php`, add a new query to calculate `$categoryStats` by grouping `post_categories` and `posts` to get the count of published posts.

## 2. Dynamic Stats Dashboard Rendering

- [x] 2.1 In `index.php`, update the stats dashboard section to iterate over `$categoryStats` instead of `$statsMap`.
- [x] 2.2 For each category card, render the category name and its post count.
- [x] 2.3 Modify the stats card HTML to be a `<button>` with `onclick="filterCategory('...')"`.

## 3. Post Filtering Logic (JavaScript & HTML)

- [x] 3.1 In `index.php`, add `data-category` attribute to each `.post-item` loop rendering.
- [x] 3.2 In `index.php`, add a "全部類別" (All Categories) button above or within the stats section to reset the category filter.
- [x] 3.3 In `index.php`, define `let currentCategory = '全部類別';` in JavaScript.
- [x] 3.4 In `index.php`, implement the `filterCategory(category)` JS function.
- [x] 3.5 In `index.php`, update the `renderPosts()` JS function to filter by both `currentTown` and `currentCategory`.
