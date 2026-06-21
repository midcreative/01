<?php
declare(strict_types=1);

require_once __DIR__ . '/../admin/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\FrontPetitionController;
use App\Services\LineLoginService;

// Load .env since we need LINE_CHANNEL_ID
$dotenv = Dotenv::createImmutable(__DIR__ . '/../admin');
$dotenv->safeLoad();

// ?¥æ”¶?ç«¯è¡¨å–®è³‡æ?
$title = trim((string)($_POST['title'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$category = trim((string)($_POST['category'] ?? '?¶ä?ç¶œå?è­°é?'));
$town = trim((string)($_POST['town'] ?? '?¨éƒ¨?°å?'));

if ($title === '' || $description === '' || $category === '') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['petition_message'] = '?æ?æ¨™é??–èªª?Žä??¯ç‚ºç©?;
    $_SESSION['petition_message_type'] = 'error';
    header('Location: /#petitions');
    exit;
}

$service = new LineLoginService();
$controller = new FrontPetitionController($service);

$controller->redirectForProposeLogin($title, $description, $category, $town);
