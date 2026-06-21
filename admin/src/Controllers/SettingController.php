<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Config\Database;

/**
 * Admin: Manage global settings (系統設定).
 */
final class SettingController extends BaseController
{
    public function __construct(private readonly Auth $auth) {}

    public function index(): void
    {
        $this->auth->requireAuth();
        $pdo = Database::getInstance();
        
        $settingsRaw = $pdo->query('SELECT * FROM settings ORDER BY setting_key ASC')->fetchAll();
        $settings = [];
        foreach ($settingsRaw as $row) {
            $settings[$row['setting_key']] = $row;
        }

        $this->render('settings/index.php', [
            'settings' => $settings,
        ]);
    }

    public function update(): void
    {
        $this->auth->requireAuth();
        $pdo = Database::getInstance();
        
        // Allowed keys to update
        $allowedKeys = [
            'LINE_CHANNEL_ID', 'LINE_CHANNEL_SECRET',
            'HERO_TAG', 'HERO_HOME_TITLE_1', 'HERO_HOME_TITLE_2',
            'HERO_ISSUES_TITLE_1', 'HERO_ISSUES_TITLE_2',
            'HERO_FEEDBACK_TITLE_1', 'HERO_FEEDBACK_TITLE_2',
            'HERO_CTA_SHOW', 'HERO_CTA_TEXT', 'HERO_BG_OVERLAY'
        ];
        $pdo->beginTransaction();
        try {
            $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM settings WHERE setting_key = ?');
            $updateStmt = $pdo->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
            $insertStmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
            
            // Handle standard text fields
            foreach ($allowedKeys as $key) {
                if (isset($_POST[$key])) {
                    $value = trim((string)$_POST[$key]);
                    
                    $checkStmt->execute([$key]);
                    if ($checkStmt->fetchColumn() > 0) {
                        $updateStmt->execute([$value, $key]);
                    } else {
                        $insertStmt->execute([$key, $value]);
                    }
                }
            }

            // Handle File Upload for HERO_BG_IMAGE
            if (isset($_FILES['HERO_BG_IMAGE']) && $_FILES['HERO_BG_IMAGE']['error'] === UPLOAD_ERR_OK) {
                $tmpName  = $_FILES['HERO_BG_IMAGE']['tmp_name'];
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['HERO_BG_IMAGE']['name']));
                
                // Keep consistent upload directory
                $uploadDirRel = '/uploads/settings/';
                $uploadDirAbs = dirname(__DIR__, 3) . $uploadDirRel;
                
                if (!is_dir($uploadDirAbs)) {
                    mkdir($uploadDirAbs, 0755, true);
                }
                
                $targetPath = $uploadDirAbs . $fileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $key = 'HERO_BG_IMAGE';
                    $value = $uploadDirRel . $fileName;
                    
                    $checkStmt->execute([$key]);
                    if ($checkStmt->fetchColumn() > 0) {
                        $updateStmt->execute([$value, $key]);
                    } else {
                        $insertStmt->execute([$key, $value]);
                    }
                }
            }

            $pdo->commit();
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['setting_message'] = '設定已成功更新！即時生效。';
            
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Settings update error: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/settings');
    }
}
