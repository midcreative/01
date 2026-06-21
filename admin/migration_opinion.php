<?php
// admin/migration_opinion.php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;

try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    $pdo = Database::getInstance();
    
    // 1. candidates
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `candidates` (
            `id`         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
            `name`       VARCHAR(100)   NOT NULL,
            `party`      VARCHAR(50),
            `type`       ENUM('self', 'main_opponent', 'other') NOT NULL DEFAULT 'other',
            `created_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME       ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ")";
    echo "Table 'candidates' created/verified successfully.\n";

    // 2. candidate_keywords
    $pdo->exec("
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
    ")";
    echo "Table 'candidate_keywords' created/verified successfully.\n";

    // 3. opinions (輿�?紀??
    $pdo->exec("
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
    ")";
    echo "Table 'opinions' created/verified successfully.\n";

    // Insert Default Candidate
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidates WHERE type = 'self'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO candidates (name, party, type) VALUES ('潘炩�?, '?�黨�?, 'self')");
        $candidateId = $pdo->lastInsertId();
        $pdo->exec("INSERT INTO candidate_keywords (candidate_id, keyword, type) VALUES ($candidateId, '潘炩�?, 'alias')")';
        echo "Default self candidate '潘炩�? inserted.\n"';
    }

    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}