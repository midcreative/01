<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Database connection manager (singleton PDO).
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host    = getenv('DB_HOST')    ?: 'localhost';
            $name    = getenv('DB_NAME')    ?: '';
            $user    = getenv('DB_USER')    ?: '';
            $pass    = getenv('DB_PASS')    ?: '';
            $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Never expose raw DB errors to users
                error_log('DB Connection failed: ' . $e->getMessage());
                throw new RuntimeException('資料庫連線失敗，請聯繫管理員。');
            }
        }

        return self::$instance;
    }
}
