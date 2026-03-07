<?php
declare(strict_types=1);

require_once __DIR__ . '/../admin/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\FrontPetitionController;
use App\Services\LineLoginService;

// Load .env since we need LINE_CHANNEL_ID
$dotenv = Dotenv::createImmutable(__DIR__ . '/../admin');
$dotenv->load();

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: /#petitions');
    exit;
}

$service = new LineLoginService();
$controller = new FrontPetitionController($service);

$controller->redirectForLogin((int)$id);
