<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "Starting migration...<br>";

require_once __DIR__ . '/admin/vendor/autoload.php';
use Dotenv\Dotenv;
use App\Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->load();

try {
    $pdo = Database::getInstance();
    
    // Create the whitepaper table
    $sql = "
    CREATE TABLE IF NOT EXISTS `whitepaper_pillars` (
      `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
      `title`         VARCHAR(255)   NOT NULL,
      `subtitle`      VARCHAR(500)   NOT NULL,
      `category_tag`  VARCHAR(50)    NOT NULL,
      `icon_name`     VARCHAR(50)    NOT NULL,
      `theme_color`   VARCHAR(50)    NOT NULL,
      `description`   TEXT           NOT NULL,
      `bullet_points` TEXT           NOT NULL,
      `sort_order`    INT            NOT NULL DEFAULT 0,
      `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
      `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at`    DATETIME       ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Table whitepaper_pillars created or already exists.<br>";
    
    // Check if empty, then seed initial data
    $count = $pdo->query("SELECT COUNT(*) FROM whitepaper_pillars")->fetchColumn();
    if ($count == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO whitepaper_pillars (title, subtitle, category_tag, icon_name, theme_color, description, bullet_points, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $seedData = [
            [
                '農漁牧產銷發展', 
                '打破盤商束縛，讓屏東好貨賣得更有尊嚴', 
                '產業升級', 
                'sprout', 
                'brand-green', 
                '「我們不僅要種得好，更要賣得準。透過冷鏈系統與品牌化經營，提升農漁產品價值。」', 
                "建置區域型冷鏈中心，穩定市場供需。\n打造「屏東優選」直銷品牌，對接全台連鎖通路。",
                1
            ],
            [
                '勞資對話與共榮', 
                '做勞工的後盾，建立企業與人才的互信橋樑', 
                '職場正義', 
                'briefcase', 
                'text-blue-500', 
                '', 
                "設立行動法律諮詢站，保障工業區勞工權益。",
                2
            ],
            [
                '親子共學與友善環境', 
                '聽見家庭的心跳聲，升級教育資源與安全', 
                '教育傳承', 
                'smile', 
                'text-orange-500', 
                '「親子關係是社會穩定的基石。我們要優化特色公園遊具，並引入AI共學講堂，讓孩子在屏東也能接軌國際。」', 
                "",
                3
            ],
            [
                '全齡心靈關懷與支持', 
                '運動與支持，建立社區情緒安全網', 
                '身心健康', 
                'heart', 
                'text-rose-500', 
                '「運動不只是競技，是療癒。結合滑輪溜冰榮耀與專業心理諮商，守護每一位屏東人的情緒健康。」', 
                "",
                4
            ],
            [
                '長照 3.0 數位升級：老朋友的尊嚴守護', 
                '導入數位健康監測與遠距諮詢，讓在宅養老成為一種幸福。', 
                '旗艦溫暖計畫', 
                'home', 
                'text-slate-900', 
                '', 
                "",
                5
            ]
        ];

        foreach ($seedData as $row) {
            $stmt->execute($row);
        }
        echo "Demo data inserted.<br>";
    } else {
        echo "Data already exists, no seeding required.<br>";
    }

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
