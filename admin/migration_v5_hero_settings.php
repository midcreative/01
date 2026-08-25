<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "Starting migration: Add Hero Settings...<br>";

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Dotenv\Dotenv;
use App\Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

try {
    $pdo = Database::getInstance();
    
    $defaults = [
        'HERO_TAG' => '屏東縣議員第三選區參選人',
        'HERO_HOME_TITLE_1' => '聽見地方的心跳，',
        'HERO_HOME_TITLE_2' => '讓服務的溫度延續。',
        'HERO_ISSUES_TITLE_1' => '承接老朋友的託付，',
        'HERO_ISSUES_TITLE_2' => '設計新一代的屏東。',
        'HERO_FEEDBACK_TITLE_1' => '匯集集體的意志，',
        'HERO_FEEDBACK_TITLE_2' => '翻轉家鄉的未來。',
        'HERO_CTA_SHOW' => '1',
        'HERO_CTA_TEXT' => '參與長照連署',
        'HERO_BG_IMAGE' => '',
    ];

    // Read existing settings
    $stmt = $pdo->prepare("SELECT setting_key FROM `settings`");
    $stmt->execute();
    $existingKeys = $stmt->fetchAll(\PDO::FETCH_COLUMN);

    $insertStmt = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)");

    foreach ($defaults as $key => $val) {
        if (!in_array($key, $existingKeys, true)) {
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