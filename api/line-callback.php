<?php
declare(strict_types=1);

require_once __DIR__ . '/../admin/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\FrontPetitionController;
use App\Services\LineLoginService;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../admin');
$dotenv->safeLoad();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$service = new LineLoginService();
$controller = new FrontPetitionController($service);

if (isset($_SESSION['propose_login_state'])) {
    // This is a callback from proposing a new petition
    $controller->handleProposeCallback($_GET);
} else {
    // This is a callback from signing an existing petition
    $controller->handleCallback($_GET);
}
