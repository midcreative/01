<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Services\LineLoginService;
use PDO;
use Throwable;

final class FrontPetitionController
{
    public function __construct(private readonly LineLoginService $lineService) {}

    /**
     * Start the LINE login flow for a specific petition
     */
    public function redirectForLogin(int $petitionId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validate petition exists and is active
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT id, status FROM petitions WHERE id = ?');
            $stmt->execute([$petitionId]);
            $petition = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$petition || !in_array($petition['status'], ['公開連署', '已達標'])) {
                $this->redirectWithError('該提案目前未開放連署');
            }
        } catch (Throwable $e) {
            error_log('Line login db error: ' . $e->getMessage());
            $this->redirectWithError('系統錯誤，請稍後再試');
        }

        // Store intent in session
        $_SESSION['signing_petition_id'] = $petitionId;
        
        // Generate and store state for CSRF protection
        $state = bin2hex(random_bytes(16));
        $_SESSION['line_login_state'] = $state;

        header('Location: ' . $this->lineService->getLoginUrl($state));
        exit;
    }

    /**
     * Handle the callback from LINE
     */
    public function handleCallback(array $params): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $error = $params['error'] ?? null;
        if ($error) {
            $this->redirectWithError('您已拒絕 LINE 授權或發生錯誤');
        }

        $code  = $params['code'] ?? null;
        $state = $params['state'] ?? null;
        $petitionId = $_SESSION['signing_petition_id'] ?? null;
        $savedState = $_SESSION['line_login_state'] ?? null;

        // Clear session data
        unset($_SESSION['line_login_state'], $_SESSION['signing_petition_id']);

        if (!$code || !$state || !$petitionId || $state !== $savedState) {
            $this->redirectWithError('登入驗證失敗或已過期，請重新嘗試');
        }

        // 1. Get Access Token
        $accessToken = $this->lineService->getAccessToken($code);
        if (!$accessToken) {
            $this->redirectWithError('無法取得 LINE 授權碼');
        }

        // 2. Get User Profile
        $profile = $this->lineService->getUserProfile($accessToken);
        if (!$profile || !isset($profile['userId'])) {
            $this->redirectWithError('無法取得 LINE 使用者基本資料');
        }

        // 3. Save Signature to Database
        try {
            $pdo = Database::getInstance();
            $pdo->beginTransaction();

            $userId      = $profile['userId'];
            $displayName = $profile['displayName'] ?? 'LINE 用戶';
            $pictureUrl  = $profile['pictureUrl'] ?? null;
            // 民眾的鄉鎮區，後續可以優化為登入後彈出表單選擇，或目前先預設 NULL 留白
            $town = null; 

            // Insert signature (ignore if already exists due to unique key)
            $stmt = $pdo->prepare('INSERT IGNORE INTO petition_signatures (petition_id, line_user_id, line_display_name, line_picture_url, town) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$petitionId, $userId, $displayName, $pictureUrl, $town]);

            $affected = $stmt->rowCount();

            // Only increment count if the signature was actually inserted (not a duplicate)
            if ($affected > 0) {
                $upStmt = $pdo->prepare('UPDATE petitions SET current_count = current_count + 1 WHERE id = ?');
                $upStmt->execute([$petitionId]);
            }

            $pdo->commit();

            // Success redirect
            if ($affected > 0) {
                $this->redirectWithSuccess('感謝您的連署！');
            } else {
                // Return generic success to avoid exposing duplicate errors gracefully
                $this->redirectWithSuccess('您已經參與過此提案的連署囉！');
            }

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Callback processing error: ' . $e->getMessage());
            $this->redirectWithError('寫入連署資料時發生錯誤');
        }
    }

    private function redirectWithError(string $msg): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['petition_message'] = $msg;
        $_SESSION['petition_message_type'] = 'error';
        header('Location: /#petitions');
        exit;
    }

    private function redirectWithSuccess(string $msg): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['petition_message'] = $msg;
        $_SESSION['petition_message_type'] = 'success';
        header('Location: /#petitions');
        exit;
    }
}
