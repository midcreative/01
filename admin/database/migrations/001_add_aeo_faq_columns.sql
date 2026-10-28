ALTER TABLE `whitepaper_pillars`
ADD COLUMN `faq_question` TEXT NULL AFTER `bullet_points`,
ADD COLUMN `faq_answer` TEXT NULL AFTER `faq_question`;
