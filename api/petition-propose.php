<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\FrontPetitionController;
use App\Services\LineLoginService;

// Load .env since we need LINE_CHANNEL_ID
 = Dotenv::createImmutable(__DIR__ . '/../admin');
->safeLoad();

// 接收前端表單資料
 = trim((string)(['title'] ?? ''));
 = trim((string)(['description'] ?? ''));
 = trim((string)(['category'] ?? '綜合建議'));
 = trim((string)(['town'] ?? '全部鄉鎮'));

if ( === '' ||  === '' ||  === '') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    ['petition_message'] = '請填寫標題與說明必填欄位。';
    ['petition_message_type'] = 'error';
    header('Location: /#petitions');
    exit;
}

 = new LineLoginService();
 = new FrontPetitionController();

->redirectForProposeLogin(, , , );
