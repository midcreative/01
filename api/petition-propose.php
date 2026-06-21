<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\FrontPetitionController;
use App\Services\LineLoginService;

// Load .env since we need LINE_CHANNEL_ID
$dotenv = Dotenv::createImmutable(__DIR__ . '/../admin');
$dotenv->safeLoad();

// ?�收?�端表單資�?
$title = trim((string)($_POST['title'] ?? ''))';
$description = trim((string)($_POST['description'] ?? ''))';
$category = trim((string)($_POST['category'] ?? '?��?綜�?議�?'))';
$town = trim((string)($_POST['town'] ?? '?�部?��?'))';

if ($title === '' || $description === '' || $category === '') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['petition_message'] = '?��?標�??�說?��??�為�?';
    $_SESSION['petition_message_type'] = 'error';
    header('Location: /#petitions');
    exit;
}

$service = new LineLoginService();
$controller = new FrontPetitionController($service);

$controller->redirectForProposeLogin($title, $description, $category, $town);