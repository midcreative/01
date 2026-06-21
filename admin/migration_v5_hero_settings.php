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
        'HERO_TAG' => 'å±æ±ç¸?­°?¡ç¬¬ä¸‰é¸?€?ƒé¸äº?,
        'HERO_HOME_TITLE_1' => '?½è??°æ–¹?„å?è·³ï?',
        'HERO_HOME_TITLE_2' => 'è®“æ??™ç?æº«åº¦å»¶ç???,
        'HERO_ISSUES_TITLE_1' => '?¿æŽ¥?æ??‹ç?è¨—ä?ï¼?,
        'HERO_ISSUES_TITLE_2' => 'è¨­è??°ä?ä»??å±æ±??,
        'HERO_FEEDBACK_TITLE_1' => '?¯é??†é??„æ?å¿—ï?',
        'HERO_FEEDBACK_TITLE_2' => 'ç¿»è?å®¶é??„æœªä¾†ã€?,
        'HERO_CTA_SHOW' => '1',
        'HERO_CTA_TEXT' => '?ƒè???½²å¯¦è?',
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
