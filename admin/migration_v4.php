<?php
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use App\Config\Database;

try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    $pdo = Database::getInstance();

    $sql = "CREATE TABLE IF NOT EXISTS `settings` (
      `setting_key` VARCHAR(100) NOT NULL,
      `setting_name` VARCHAR(100) NOT NULL,
      `setting_value` TEXT,
      `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);

    $pdo->exec("INSERT IGNORE INTO `settings` (`setting_key`, `setting_name`, `setting_value`) VALUES
    ('LINE_CHANNEL_ID', 'LINE Channel ID', ''),
    ('LINE_CHANNEL_SECRET', 'LINE Channel Secret', '')");

    echo "SUCCESS: settings table created and populated!";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
