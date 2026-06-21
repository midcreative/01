<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

try {
    require __DIR__ . '/src/Controllers/CategoryController.php';
    echo "CategoryController loaded successfully.<br>";
    require __DIR__ . '/src/Controllers/TownController.php';
    echo "TownController loaded successfully.<br>";
} catch (\Throwable $e) {
    echo "<h1>Error</h1>";
    echo "<pre>" . (string)$e . "</pre>";
}
