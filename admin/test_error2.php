<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Models\Category;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

try {
    $categories = Category::all();
    $pageTitle = 'Testing';
    $user = 'admin';
    
    // We recreate what CategoryController::index() does, but bypassing Auth which might redirect.
    ob_start();
    include __DIR__ . '/src/Views/categories/index.php';
    $content = ob_get_clean();
    echo $content;
    
} catch (\Throwable $e) {
    echo "<h1>Error</h1>";
    echo "<pre>" . (string)$e . "</pre>";
}
