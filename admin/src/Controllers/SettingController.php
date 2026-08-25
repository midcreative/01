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
            'HERO_TAG', 'HERO_BG_BLUR', 'HERO_SHOW_TEXT',
            'TOWN_TITLE', 'TOWN_SUBTITLE',
            'WHITEPAPER_TITLE', 'WHITEPAPER_SUBTITLE',
            'PETITION_TITLE', 'PETITION_SUBTITLE', 'PETITION_CTA_SHOW', 'PETITION_CTA_TEXT'
        ];
        
        $pdo->beginTransaction();
        try {
            $updateStmt = $pdo->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
            $checkStmt = $pdo->prepare('SELECT 1 FROM settings WHERE setting_key = ?');
            $insertStmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
            
            foreach ($allowedKeys as $key) {
                if (isset($_POST[$key])) {
                    $value = trim((string)$_POST[$key]);
                    $checkStmt->execute([$key]);
                    if ($checkStmt->fetch()) {
                        $updateStmt->execute([$value, $key]);
                    } else {
                        $insertStmt->execute([$key, $value]);
                    }
                }
            }
            
            // Handle Hero BG Upload
            if (isset($_FILES['HERO_BG_IMAGE_FILE']) && $_FILES['HERO_BG_IMAGE_FILE']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['HERO_BG_IMAGE_FILE']['tmp_name'];
                $name    = basename($_FILES['HERO_BG_IMAGE_FILE']['name']);
                $ext     = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $uploadDirRel = '/uploads/settings/';
                    $uploadDirAbs = dirname(__DIR__, 3) . $uploadDirRel;
                    if (!is_dir($uploadDirAbs)) {
                        mkdir($uploadDirAbs, 0755, true);
                    }
                    $fileName   = time() . '_' . uniqid() . '.' . $ext;
                    $targetPath = $uploadDirAbs . $fileName;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        // check if setting exists
                        $checkStmtBg = $pdo->prepare('SELECT 1 FROM settings WHERE setting_key = ?');
                        $checkStmtBg->execute(['HERO_BG_IMAGE']);
                        if ($checkStmtBg->fetch()) {
                            $updateStmt->execute([$uploadDirRel . $fileName, 'HERO_BG_IMAGE']);
                        } else {
                            $insertStmtBg = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
                            $insertStmtBg->execute(['HERO_BG_IMAGE', $uploadDirRel . $fileName]);
                        }
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
