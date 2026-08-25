<?php

declare(strict_types=1);

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/admin/vendor/autoload.php')) {
    require_once __DIR__ . '/admin/vendor/autoload.php';
}

use Dotenv\Dotenv;
use App\Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->safeLoad();
date_default_timezone_set('Asia/Taipei');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /volunteer.php');
    exit;
}

// 防連續重複點擊（3 秒防刷機制）
$now = time();
$lastSubmit = (int)($_SESSION['last_volunteer_submit_time'] ?? 0);
if ($now - $lastSubmit < 3) {
    $_SESSION['volunteer_flash'] = [
        'type' => 'error',
        'message' => '送出過於頻繁，請稍候 3 秒後再試。'
    ];
    header('Location: /volunteer.php');
    exit;
}

$jobId   = (int)($_POST['job_id'] ?? 0);
$name    = trim((string)($_POST['name'] ?? ''));
$phone   = trim((string)($_POST['phone'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($jobId <= 0 || $name === '' || $phone === '') {
    $_SESSION['volunteer_flash'] = [
        'type' => 'error',
        'message' => '請填寫必填欄位（姓名、電話）。'
    ];
    header('Location: /volunteer.php');
    exit;
}

try {
    $pdo = Database::getInstance();

    // 驗證職缺是否存在且處於開放招募狀態
    $stmt = $pdo->prepare('SELECT id, is_active FROM volunteer_jobs WHERE id = ?');
    $stmt->execute([$jobId]);
    $job = $stmt->fetch();

    if (!$job || (int)$job['is_active'] !== 1) {
        $_SESSION['volunteer_flash'] = [
            'type' => 'error',
            'message' => '此志工職缺目前已結束招募或不存在。'
        ];
        header('Location: /volunteer.php');
        exit;
    }

    $insertStmt = $pdo->prepare('
        INSERT INTO volunteer_applications (job_id, name, phone, email, message, status, applied_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ');
    $insertStmt->execute([
        $jobId,
        $name,
        $phone,
        $email !== '' ? $email : null,
        $message !== '' ? $message : null,
        '待審核'
    ]);

    $_SESSION['last_volunteer_submit_time'] = $now;
    $_SESSION['volunteer_flash'] = [
        'type' => 'success',
        'message' => '感謝您的熱情報名！我們將會盡快與您聯繫。'
    ];
} catch (\Throwable $e) {
    error_log('Volunteer application error: ' . $e->getMessage());
    $_SESSION['volunteer_flash'] = [
        'type' => 'error',
        'message' => '寫入資料庫時發生錯誤，請稍後再試。'
    ];
}

header('Location: /volunteer.php');
exit;
