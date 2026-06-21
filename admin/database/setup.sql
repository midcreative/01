-- ============================================================
-- 潘炩禕 CMS 資料庫建置 SQL
-- 在 cPanel > phpMyAdmin 執行此檔案
-- DB: midcreat_demo10
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 1. 後台管理帳號
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)      NOT NULL,
  `password`   VARCHAR(255)     NOT NULL,
  `created_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 預設管理員帳號：admin / Admin@2026!
-- 密碼已用 bcrypt 加密
INSERT IGNORE INTO `admin_users` (`username`, `password`) VALUES
('admin', '$2y$12$CadjrtClOGsxtdyiTkxAtemsBRouU.7MZs12Ap8DM2E7WV1bcs8Z.');

-- 2. 服務日記
CREATE TABLE IF NOT EXISTS `posts` (
  `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(255)   NOT NULL,
  `slug`         VARCHAR(255)   NOT NULL,
  `category`     ENUM('農漁牧業關注','親子教育關懷','勞資共榮','身心靈關懷','長照守護') NOT NULL,
  `town`         ENUM('全部地區','潮州鎮','新埤鄉','內埔鄉','萬巒鄉','竹田鄉','枋寮鄉') NOT NULL DEFAULT '全部地區',
  `status_tag`   ENUM('專案跟進','已發函','已完成') NOT NULL DEFAULT '專案跟進',
  `excerpt`      TEXT,
  `content`      LONGTEXT,
  `cover_image`  VARCHAR(500),
  `published_at` DATE,
  `is_published` TINYINT(1)     NOT NULL DEFAULT 0,
  `created_at`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME       ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`),
  KEY `idx_published` (`is_published`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. 首頁數據看板
CREATE TABLE IF NOT EXISTS `stats` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `stat_key`    VARCHAR(100)   NOT NULL,
  `stat_label`  VARCHAR(100)   NOT NULL,
  `stat_value`  INT            NOT NULL DEFAULT 0,
  `stat_unit`   VARCHAR(20)    NOT NULL DEFAULT '項',
  `icon_name`   VARCHAR(50),
  `icon_color`  VARCHAR(50),
  `sort_order`  INT            NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stat_key` (`stat_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 預設數據（對應示意稿數字）
INSERT IGNORE INTO `stats` (`stat_key`, `stat_label`, `stat_value`, `stat_unit`, `icon_name`, `icon_color`, `sort_order`) VALUES
('total_issues',  '已關切議題總數', 284, '項', 'layers',    'brand-green',  1),
('agri_cases',    '農漁牧業關注',   85,  '案', 'sprout',    'text-emerald-500', 2),
('labor_cases',   '勞資共榮協助',   56,  '案', 'briefcase', 'text-blue-500',    3),
('child_cases',   '親子共學成長',   72,  '次', 'smile',     'text-orange-500',  4),
('mental_cases',  '身心靈關懷',     68,  '次', 'heart',     'text-rose-500',    5),
('elder_cases',   '長照尊嚴守護',   95,  '次', 'home',      'text-indigo-500',  6);

-- 4. 連署提案
CREATE TABLE IF NOT EXISTS `petitions` (
  `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `title`         VARCHAR(255)   NOT NULL,
  `description`   TEXT,
  `town`          ENUM('全部地區','潮州鎮','新埤鄉','內埔鄉','萬巒鄉','竹田鄉','枋寮鄉'),
  `status`        ENUM('審核中','公開連署','已達標','已列管') NOT NULL DEFAULT '審核中',
  `target_count`  INT            NOT NULL DEFAULT 100,
  `current_count` INT            NOT NULL DEFAULT 0,
  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. 志工職缺
CREATE TABLE IF NOT EXISTS `volunteer_jobs` (
  `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(255)   NOT NULL,
  `description`  TEXT,
  `town`         ENUM('全部地區','潮州鎮','新埤鄉','內埔鄉','萬巒鄉','竹田鄉','枋寮鄉'),
  `required_num` INT            NOT NULL DEFAULT 1,
  `deadline`     DATE,
  `is_active`    TINYINT(1)     NOT NULL DEFAULT 1,
  `created_at`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. 志工報名
CREATE TABLE IF NOT EXISTS `volunteer_applications` (
  `id`         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `job_id`     INT UNSIGNED   NOT NULL,
  `name`       VARCHAR(100)   NOT NULL,
  `phone`      VARCHAR(20)    NOT NULL,
  `email`      VARCHAR(255),
  `message`    TEXT,
  `status`     ENUM('待審核','已接受','已婉謝') NOT NULL DEFAULT '待審核',
  `applied_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job_id` (`job_id`),
  CONSTRAINT `fk_app_job` FOREIGN KEY (`job_id`) REFERENCES `volunteer_jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. 行動白皮書
CREATE TABLE IF NOT EXISTS `whitepaper_pillars` (
  `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `title`         VARCHAR(255)   NOT NULL,
  `subtitle`      VARCHAR(500)   NOT NULL,
  `category_tag`  VARCHAR(50)    NOT NULL,
  `icon_name`     VARCHAR(50)    NOT NULL,
  `theme_color`   VARCHAR(50)    NOT NULL,
  `description`   TEXT           NOT NULL,
  `bullet_points` TEXT           NOT NULL,
  `sort_order`    INT            NOT NULL DEFAULT 0,
  `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME       ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. 連署參與紀錄
CREATE TABLE IF NOT EXISTS `petition_signatures` (
  `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `petition_id`       INT UNSIGNED   NOT NULL,
  `line_user_id`      VARCHAR(255)   NOT NULL,
  `line_display_name` VARCHAR(255)   NOT NULL,
  `line_picture_url`  VARCHAR(500),
  `town`              VARCHAR(100),
  `created_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_petition_line_user` (`petition_id`, `line_user_id`),
  CONSTRAINT `fk_signature_petition` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. 輿情監測 - 候選人名單
CREATE TABLE IF NOT EXISTS `candidates` (
  `id`         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)   NOT NULL,
  `party`      VARCHAR(50),
  `type`       ENUM('self', 'main_opponent', 'other') NOT NULL DEFAULT 'other',
  `created_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME       ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 預設潘炩禕本人
INSERT IGNORE INTO `candidates` (`name`, `party`, `type`) VALUES ('潘炩禕', '無黨籍', 'self');

-- 10. 輿情監測 - 關鍵字設定
CREATE TABLE IF NOT EXISTS `candidate_keywords` (
  `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `candidate_id` INT UNSIGNED   NOT NULL,
  `keyword`      VARCHAR(100)   NOT NULL,
  `type`         ENUM('alias', 'issue', 'negative') NOT NULL DEFAULT 'alias',
  `is_active`    TINYINT(1)     NOT NULL DEFAULT 1,
  `created_at`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_candidate_id` (`candidate_id`),
  CONSTRAINT `fk_keyword_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. 輿情監測 - 輿情紀錄
CREATE TABLE IF NOT EXISTS `opinions` (
  `id`               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `candidate_id`     INT UNSIGNED,
  `source_type`      ENUM('news', 'ptt', 'dcard', 'fb', 'other') NOT NULL DEFAULT 'other',
  `source_name`      VARCHAR(100),
  `title`            VARCHAR(255)   NOT NULL,
  `url`              VARCHAR(500)   NOT NULL,
  `content_excerpt`  TEXT,
  `sentiment`        ENUM('positive', 'neutral', 'negative') DEFAULT 'neutral',
  `confidence_score` DECIMAL(4,2),
  `published_at`     DATETIME,
  `created_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_url` (`url`),
  KEY `idx_candidate_id` (`candidate_id`),
  KEY `idx_published_at` (`published_at`),
  KEY `idx_sentiment` (`sentiment`),
  CONSTRAINT `fk_opinion_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
