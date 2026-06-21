<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;

try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    echo "DB_HOST is: " . ($_ENV['DB_HOST'] ?? 'Not set via ENV') . "\n";
    echo "DB_NAME is: " . ($_ENV['DB_NAME'] ?? 'Not set via ENV') . "\n";
    $pdo = Database::getInstance();
    
    // 1. 建�? petition_signatures 表格
    $sql = "CREATE TABLE IF NOT EXISTS `petition_signatures` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `petition_id` int(10) unsigned NOT NULL,
        `line_user_id` varchar(255) NOT NULL,
        `line_display_name` varchar(255) NOT NULL,
        `line_picture_url` varchar(500) DEFAULT NULL,
        `town` varchar(100) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_petition_line_user` (`petition_id`, `line_user_id`),
        CONSTRAINT `fk_signature_petition` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    echo "Creating petition_signatures table OK.\n";
    
    echo "\nAll migrations (v3) completed successfully.\n";

} catch (\PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (\Throwable $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
