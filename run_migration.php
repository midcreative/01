<?php
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->safeLoad();

try {
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_NAME') ?: 'midcreat_demo10';
    $user = getenv('DB_USER') ?: 'midcreat_demo10';
    $pass = getenv('DB_PASS') ?: 'Ss@0952826333';
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("DROP TABLE IF EXISTS `posts`");
    $sql = file_get_contents(__DIR__ . '/admin/database/setup.sql');
    $pdo->exec($sql);
    echo "SUCCESS: Database migrated!";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
