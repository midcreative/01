<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "Starting migration: Add category to petitions...<br>";

require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;
use App\Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

try {
    $pdo = Database::getInstance();
    
    // Check if category column already exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `petitions` LIKE 'category'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE `petitions` ADD COLUMN `category` VARCHAR(50) DEFAULT '?��?綜�?議�?' AFTER `town`";
        $pdo->exec($sql);
        echo "Column `category` added to `petitions` table successfully.<br>";
    } else {
        echo "Column `category` already exists in `petitions` table.<br>";
    }

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}