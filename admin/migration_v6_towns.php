<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

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
    
    echo "<h1>Database Migration (V6) - Extending Town Enums</h1>";

    // 1. Insert new towns into `post_towns` for the `posts` table
    $stmtTown = $pdo->prepare("INSERT IGNORE INTO `post_towns` (`name`, `sort_order`) VALUES (?, ?)");
    $stmtTown->execute(['新埤鄉', 6]);
    $stmtTown->execute(['竹田鄉', 7]);
    echo "<p>[OK] Added '新埤鄉' and '竹田鄉' into 'post_towns' table.</p>";

    // 2. Extend `petitions` table ENUM
    $sql2 = "ALTER TABLE petitions MODIFY COLUMN town ENUM('全部地區','潮州鎮','內埔鄉','萬巒鄉','新埤鄉','竹田鄉','枋寮鄉');";
    $pdo->exec($sql2);
    echo "<p>[OK] Updated 'petitions' table ENUM.</p>";

    // 3. Extend `volunteer_jobs` table
    $sql3 = "ALTER TABLE volunteer_jobs MODIFY COLUMN town ENUM('全部地區','潮州鎮','內埔鄉','萬巒鄉','新埤鄉','竹田鄉','枋寮鄉');";
    $pdo->exec($sql3);
    echo "<p>[OK] Updated 'volunteer_jobs' table ENUM.</p>";

    echo "<h3>Migration completed successfully.</h3>";
    echo "<p><a href='/admin/'>Return to Dashboard</a></p>";

} catch (\Throwable $e) {
    echo "<h3 style='color:red;'>Error during migration:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}