<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "Starting migration: Add Hero Settings...<br>";

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use App\Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

try {
    $pdo = Database::getInstance();
    
    $defaults = [
        'HERO_TAG' => '屏東�?��?�第三選?�?�選�?,
        'HERO_HOME_TITLE_1' => '?��??�方?��?跳�?',
        'HERO_HOME_TITLE_2' => '讓�??��?溫度延�???,
        'HERO_ISSUES_TITLE_1' => '?�接?��??��?託�?�?,
        'HERO_ISSUES_TITLE_2' => '設�??��?�??屏東??,
        'HERO_FEEDBACK_TITLE_1' => '?��??��??��?志�?',
        'HERO_FEEDBACK_TITLE_2' => '翻�?家�??�未來�?,
        'HERO_CTA_SHOW' => '1',
        'HERO_CTA_TEXT' => '?��???��實�?',
        'HERO_BG_IMAGE' => '',
    ];

    // Read existing settings
    $stmt = $pdo->prepare("SELECT setting_key FROM `settings`");
    $stmt->execute();
    $existingKeys = $stmt->fetchAll(\PDO::FETCH_COLUMN);

    $insertStmt = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)");

    foreach ($defaults as $key => $val) {
        if (!in_array($key, $existingKeys)) {
            $insertStmt->execute([$key, $val]);
            echo "Added new setting: {$key}<br>";
        } else {
            echo "Setting already exists: {$key}<br>";
        }
    }

    echo "Migration completed.<br>";

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}