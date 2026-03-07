<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=midcreat_demo10;charset=utf8mb4', 'midcreat_demo10', 'Ss@0952826333', [
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
