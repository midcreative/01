<?php

declare(strict_types=1);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../admin/vendor/autoload.php')) {
    require_once __DIR__ . '/../admin/vendor/autoload.php';
}

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../admin');
$dotenv->safeLoad();

// 目前連署實證站由辦公室統一發布提案，民眾實名連署附議。
// 若未來開放民眾線上自提提案，可在此擴充提案審核流程。
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['petition_message'] = '感謝您的建議！如欲發起連署提案，歡迎透過線上陳情或與服務處聯繫。';
$_SESSION['petition_message_type'] = 'success';

header('Location: /#petitions');
exit;
