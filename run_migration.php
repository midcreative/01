<?php
require_once __DIR__ . '/admin/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->safeLoad();

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'midcreat_demo10';
    $user = $_ENV['DB_USER'] ?? 'midcreat_demo10';
    $pass = $_ENV['DB_PASS'] ?? 'Ss@0952826333';
    $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

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
    echo "SUCCESS: petition_signatures created!";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
