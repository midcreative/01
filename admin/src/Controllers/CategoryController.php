<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Category;
use App\Models\Post;
use App\Config\Database;
use PDO;

final class CategoryController extends BaseController
{
    private string $user;

    public function __construct(private readonly Auth $auth)
    {
        $this->user = $this->auth->requireAuth();
    }

    public function index(): void
    {
        $categories = Category::all();
        $this->render('categories/index.php', [
            'user'       => $this->user,
            'categories' => $categories,
            'pageTitle'  => '主軸分類管理',
        ]);
    }

    public function store(): void
    {
        $name       = trim((string)($_POST['name'] ?? ''));
        $colorTheme = trim((string)($_POST['color_theme'] ?? ''));
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);

        if ($name === '') {
            $this->redirect('/admin/categories?error=分類名稱不可為空');
        }

        try {
            Category::create([
                'name'        => $name,
                'color_theme' => $colorTheme,
                'sort_order'  => $sortOrder,
            ]);
            $this->redirect('/admin/categories?success=分類新增成功');
        } catch (\Throwable $e) {
            error_log('Category store error: ' . $e->getMessage());
            $this->redirect('/admin/categories?error=新增失敗：名稱可能重複');
        }
    }

    public function update(int $id): void
    {
        $name       = trim((string)($_POST['name'] ?? ''));
        $colorTheme = trim((string)($_POST['color_theme'] ?? ''));
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);

        if ($name === '') {
            $this->redirect('/admin/categories?error=分類名稱不可為空');
        }

        try {
            Category::update($id, [
                'name'        => $name,
                'color_theme' => $colorTheme,
                'sort_order'  => $sortOrder,
            ]);
            $this->redirect('/admin/categories?success=分類更新成功');
        } catch (\Throwable $e) {
            error_log('Category update error: ' . $e->getMessage());
            $this->redirect('/admin/categories?error=更新失敗');
        }
    }

    public function destroy(int $id): void
    {
        try {
            // Check if category is in use
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE category_id = ?');
            $stmt->execute([$id]);
            $count = (int)$stmt->fetchColumn();

            if ($count > 0) {
                $this->redirect('/admin/categories?error=此分類下還有 ' . $count . ' 篇文章，請先轉移文章分類後再刪除');
                return;
            }

            Category::delete($id);
            $this->redirect('/admin/categories?success=分類刪除成功');
        } catch (\Throwable $e) {
            error_log('Category delete error: ' . $e->getMessage());
            $this->redirect('/admin/categories?error=刪除失敗');
        }
    }
}
