<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

try {
    $pdo = Database::getInstance();

    echo "1. 建立 post_categories 資料表...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `post_categories` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `name` VARCHAR(100) NOT NULL,
          `color_theme` VARCHAR(100) NOT NULL DEFAULT 'bg-slate-50 text-slate-600 border-slate-100',
          `sort_order` INT NOT NULL DEFAULT 0,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "2. 建立 post_towns 資料表...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `post_towns` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `name` VARCHAR(100) NOT NULL,
          `sort_order` INT NOT NULL DEFAULT 0,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "3. 插入預設主軸分類...\n";
    $defaultCategories = [
        ['農漁牧業關注', 'bg-emerald-50 text-emerald-600 border-emerald-100', 1],
        ['親子教育關懷', 'bg-orange-50 text-orange-600 border-orange-100', 2],
        ['勞資共榮', 'bg-blue-50 text-blue-600 border-blue-100', 3],
        ['身心靈關懷', 'bg-rose-50 text-rose-600 border-rose-100', 4],
        ['長照守護', 'bg-indigo-50 text-indigo-600 border-indigo-100', 5],
    ];
    $stmtCategory = $pdo->prepare("INSERT IGNORE INTO `post_categories` (`name`, `color_theme`, `sort_order`) VALUES (?, ?, ?)");
    foreach ($defaultCategories as $cat) {
        $stmtCategory->execute($cat);
    }

    echo "4. 插入預設鄉鎮分類...\n";
    $defaultTowns = [
        ['全部地區', 1],
        ['潮州鎮', 2],
        ['內埔鄉', 3],
        ['萬巒鄉', 4],
        ['枋寮鄉', 5],
    ];
    $stmtTown = $pdo->prepare("INSERT IGNORE INTO `post_towns` (`name`, `sort_order`) VALUES (?, ?)");
    foreach ($defaultTowns as $town) {
        $stmtTown->execute($town);
    }

    echo "5. 修改 posts 資料表邏輯...\n";
    
    // 檢查 posts 表是否已經有新欄位
    $columns = $pdo->query("SHOW COLUMNS FROM `posts`")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('category_id', $columns)) {
        echo "   - 新增 category_id 與 town_id 欄位\n";
        $pdo->exec("ALTER TABLE `posts` ADD COLUMN `category_id` INT UNSIGNED NULL AFTER `title`");
        $pdo->exec("ALTER TABLE `posts` ADD COLUMN `town_id` INT UNSIGNED NULL AFTER `category_id`");
        
        echo "   - 遷移舊資料關聯...\n";
        $posts = $pdo->query("SELECT id, category, town FROM `posts`")->fetchAll(PDO::FETCH_ASSOC);
        
        $updateStmt = $pdo->prepare("UPDATE `posts` SET `category_id` = ?, `town_id` = ? WHERE `id` = ?");
        
        $catMap = [];
        foreach ($pdo->query("SELECT id, name FROM post_categories")->fetchAll() as $c) {
            $catMap[$c['name']] = $c['id'];
        }
        $townMap = [];
        foreach ($pdo->query("SELECT id, name FROM post_towns")->fetchAll() as $t) {
            $townMap[$t['name']] = $t['id'];
        }
        
        foreach ($posts as $p) {
            // 如果舊的分類不存在於 map，預設給 ID = 1
            $catId = $catMap[$p['category']] ?? 1;
            $townId = $townMap[$p['town']] ?? 1;
            
            $updateStmt->execute([$catId, $townId, $p['id']]);
        }
        
        echo "   - 刪除舊的 ENUM 欄位與狀態欄位...\n";
        $pdo->exec("ALTER TABLE `posts` DROP COLUMN `category`");
        $pdo->exec("ALTER TABLE `posts` DROP COLUMN `town`");
        $pdo->exec("ALTER TABLE `posts` DROP COLUMN `status_tag`");
        
        echo "   - 增加 Foreign Key 約束...\n";
        $pdo->exec("ALTER TABLE `posts` ADD CONSTRAINT `fk_post_category` FOREIGN KEY (`category_id`) REFERENCES `post_categories`(`id`) ON DELETE SET NULL");
        $pdo->exec("ALTER TABLE `posts` ADD CONSTRAINT `fk_post_town` FOREIGN KEY (`town_id`) REFERENCES `post_towns`(`id`) ON DELETE SET NULL");
    } else {
        echo "   - posts 表已經更新過了，跳過欄位修改。\n";
    }

    echo "完成！遷移成功。\n";

} catch (\Throwable $e) {
    echo "發生錯誤: " . $e->getMessage() . "\n";
}
