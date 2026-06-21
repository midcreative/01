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
        $allowedKeys = ['LINE_CHANNEL_ID', 'LINE_CHANNEL_SECRET'];
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
            foreach ($allowedKeys as $key) {
                if (isset($_POST[$key])) {
                    $value = trim((string)$_POST[$key]);
                    $stmt->execute([$value, $key]);
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
