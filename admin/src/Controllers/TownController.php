<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Town;
use App\Config\Database;
use PDO;

final class TownController extends BaseController
{
    private string $user;

    public function __construct(private readonly Auth $auth)
    {
        $this->user = $this->auth->requireAuth();
    }

    public function index(): void
    {
        $towns = Town::all();
        $this->render('towns/index.php', [
            'user'      => $this->user,
            'towns'     => $towns,
            'pageTitle' => '鄉鎮分類管理',
        ]);
    }

    public function store(): void
    {
        $name       = trim((string)($_POST['name'] ?? ''));
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);

        if ($name === '') {
            $this->redirect('/admin/towns?error=鄉鎮名稱不可為空');
        }

        try {
            Town::create([
                'name'       => $name,
                'sort_order' => $sortOrder,
            ]);
            $this->redirect('/admin/towns?success=鄉鎮新增成功');
        } catch (\Throwable $e) {
            error_log('Town store error: ' . $e->getMessage());
            $this->redirect('/admin/towns?error=新增失敗：名稱可能重複');
        }
    }

    public function update(int $id): void
    {
        $name       = trim((string)($_POST['name'] ?? ''));
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);

        if ($name === '') {
            $this->redirect('/admin/towns?error=鄉鎮名稱不可為空');
        }

        try {
            Town::update($id, [
                'name'       => $name,
                'sort_order' => $sortOrder,
            ]);
            $this->redirect('/admin/towns?success=鄉鎮更新成功');
        } catch (\Throwable $e) {
            error_log('Town update error: ' . $e->getMessage());
            $this->redirect('/admin/towns?error=更新失敗');
        }
    }

    public function destroy(int $id): void
    {
        try {
            // Check if town is in use
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE town_id = ?');
            $stmt->execute([$id]);
            $count = (int)$stmt->fetchColumn();

            if ($count > 0) {
                $this->redirect('/admin/towns?error=此鄉鎮下還有 ' . $count . ' 篇文章，請先轉移文章分類後再刪除');
                return;
            }

            Town::delete($id);
            $this->redirect('/admin/towns?success=鄉鎮刪除成功');
        } catch (\Throwable $e) {
            error_log('Town delete error: ' . $e->getMessage());
            $this->redirect('/admin/towns?error=刪除失敗');
        }
    }
}
