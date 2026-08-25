<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\FrontPetitionController;
use App\Services\LineLoginService;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../admin');
$dotenv->safeLoad();

$service = new LineLoginService();
$controller = new FrontPetitionController($service);

$controller->handleCallback($_GET);
