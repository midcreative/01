<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Config\Database;

/**
 * Handles admin login / logout flow.
 */
final class AuthController extends BaseController
{
    public function __construct(private readonly Auth $auth) {}

    /** Show the login page. */
    public function showLogin(): void
    {
        // Already logged in → redirect to dashboard
        $token = $_COOKIE['admin_token'] ?? '';
        if ($token && $this->auth->validateToken($token)) {
            $this->redirect('/admin/dashboard');
        }

        $error = $_GET['error'] ?? '';
        ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>後台登入 — 潘炩禕服務辦公室</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans TC', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#E0F2ED] to-[#F9FBFA] flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#66C2A5] rounded-2xl text-white font-black text-3xl mb-4 shadow-xl shadow-[#66C2A5]/30">潘</div>
            <h1 class="text-2xl font-black text-slate-800">後台管理系統</h1>
            <p class="text-slate-400 text-sm mt-1">潘炩禕服務辦公室</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8 border border-slate-50">
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 text-sm rounded-2xl px-4 py-3 mb-6 flex items-center gap-2">
                <span>⚠️</span> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/admin/login">
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">帳號</label>
                        <input type="text" name="username" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-slate-50 text-slate-800 font-medium focus:outline-none focus:border-[#66C2A5] focus:ring-2 focus:ring-[#66C2A5]/20 transition-all"
                               placeholder="輸入管理者帳號" autocomplete="username">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">密碼</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-slate-50 text-slate-800 font-medium focus:outline-none focus:border-[#66C2A5] focus:ring-2 focus:ring-[#66C2A5]/20 transition-all"
                               placeholder="輸入密碼" autocomplete="current-password">
                    </div>
                    <button type="submit"
                            class="w-full py-4 bg-[#66C2A5] text-white rounded-2xl font-black text-sm shadow-lg shadow-[#66C2A5]/30 hover:bg-[#57A891] transition-all active:scale-95">
                        登入後台
                    </button>
                </div>
            </form>
        </div>
        <p class="text-center text-slate-300 text-[10px] mt-6 tracking-widest uppercase">© 2026 Pan Ling-Yi Service Office</p>
    </div>
</body>
</html>
        <?php
    }

    /** Handle POST login form submission. */
    public function handleLogin(): void
    {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));

        if ($username === '' || $password === '') {
            $this->redirect('/admin/?error=請填寫帳號和密碼');
        }

        try {
            $pdo  = Database::getInstance();
            $stmt = $pdo->prepare('SELECT id, username, password FROM admin_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user || !$this->auth->verifyPassword($password, (string) $user['password'])) {
                $this->redirect('/admin/?error=帳號或密碼錯誤');
            }

            $token = $this->auth->generateToken($username);

            setcookie('admin_token', $token, [
                'expires'  => time() + 60 * 60 * 8,
                'path'     => '/admin',
                'httponly' => true,
                'secure'   => isset($_SERVER['HTTPS']),
                'samesite' => 'Lax',
            ]);

            $this->redirect('/admin/dashboard');
        } catch (\Throwable $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->redirect('/admin/?error=' . urlencode($e->getMessage()));
        }
    }

    /** Clear cookie and redirect to login. */
    public function logout(): void
    {
        setcookie('admin_token', '', ['expires' => time() - 3600, 'path' => '/admin']);
        $this->redirect('/admin/');
    }
}
