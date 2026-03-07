<?php
/**
 * Composer 伺服器端安裝腳本
 * 執行後刪除此檔案！
 *
 * 存取網址：https://demo10.midcreative.com/admin/composer-setup.php?key=PanLingYi2026
 */

declare(strict_types=1);

// Simple access key to prevent unauthorized execution
$accessKey = 'PanLingYi2026';
if (($_GET['key'] ?? '') !== $accessKey) {
    http_response_code(403);
    die('<h1>403 Forbidden</h1>');
}

set_time_limit(300);
ini_set('display_errors', '1');
error_reporting(E_ALL);

$dir    = __DIR__;
$output = [];

function run(string $cmd): string
{
    $result = shell_exec($cmd . ' 2>&1');
    return $result ?? '(no output)';
}

function ok(string $msg): string { return "<li>✅ {$msg}</li>"; }
function fail(string $msg): string { return "<li>❌ {$msg}</li>"; }
function info(string $msg): string { return "<li>ℹ️ {$msg}</li>"; }

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<title>Composer Setup</title>
<style>
body { font-family: monospace; background: #0f172a; color: #94a3b8; padding: 2rem; }
h2 { color: #66C2A5; }
ul { list-style: none; padding: 0; }
li { padding: 4px 0; font-size: 14px; }
pre { background: #1e293b; padding: 1rem; border-radius: 8px; overflow-x: auto; color: #e2e8f0; font-size: 12px; }
.done { color: #66C2A5; font-weight: bold; font-size: 18px; }
.warn { color: #fb923c; }
</style>
</head>
<body>
<h2>🛠 Composer 安裝程序</h2>
<ul>

<?php

// Step 1: Check PHP version
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '8.1.0', '>=')) {
    echo ok("PHP 版本：{$phpVersion}");
} else {
    echo fail("PHP 版本過低：{$phpVersion}（需要 8.1+）");
}

// Step 2: Check required extensions
$required = ['pdo_mysql', 'mbstring', 'openssl', 'json', 'curl', 'zip'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo ok("Extension: {$ext}");
    } else {
        echo fail("Extension 缺失: {$ext}");
    }
}

// Step 3: Check shell_exec
if (function_exists('shell_exec') && shell_exec('echo ok') !== null) {
    echo ok("shell_exec 可用");
} else {
    echo fail("shell_exec 不可用，無法自動安裝。請透過 cPanel Terminal 手動執行 composer install。");
    echo '</ul></body></html>';
    exit;
}

// Step 4: Move to admin dir & download composer.phar
echo info("設定 COMPOSER_HOME 環境變數...");

// Set HOME/COMPOSER_HOME for shared hosting environments
$composerHome = $dir . '/composer-home';
if (!is_dir($composerHome)) {
    mkdir($composerHome, 0755, true);
}
putenv("COMPOSER_HOME={$composerHome}");
putenv("HOME={$composerHome}");
$_ENV['COMPOSER_HOME'] = $composerHome;
$_ENV['HOME']          = $composerHome;

echo ok("COMPOSER_HOME 設定為 {$composerHome}");

if (!file_exists($dir . '/composer.phar')) {
    echo info("下載 composer.phar...");
    $composerInstaller = @file_get_contents('https://getcomposer.org/installer');
    if ($composerInstaller === false) {
        // Try alternate download
        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $composerInstaller = @file_get_contents('https://getcomposer.org/installer', false, $ctx);
    }
    if ($composerInstaller === false) {
        echo fail("無法下載 Composer installer");
        echo '</ul></body></html>';
        exit;
    }
    file_put_contents($dir . '/composer-installer.php', $composerInstaller);
    $phpBin = run('which php8.3 || which php8.2 || which php8.1 || which php8 || which php');
    $phpBin = trim(explode("\n", $phpBin)[0]);
    $env    = "export HOME={$composerHome} && export COMPOSER_HOME={$composerHome} && ";
    $setupOut = run("{$env}{$phpBin} {$dir}/composer-installer.php --install-dir={$dir} --filename=composer.phar");
    echo info("Installer output: " . htmlspecialchars(substr($setupOut, 0, 800)));
    @unlink($dir . '/composer-installer.php');
} else {
    echo ok("composer.phar 已存在，跳過下載");
}

if (file_exists($dir . '/composer.phar')) {
    echo ok("composer.phar 下載成功");
} else {
    echo fail("composer.phar 不存在，請手動上傳 https://getcomposer.org/composer.phar 到 admin/ 目錄");
    echo '</ul></body></html>';
    exit;
}

// Step 5: Run composer install
echo info("執行 composer install --no-dev --optimize-autoloader ...");
$phpBin     = trim(run('which php8.3 || which php8.2 || which php8.1 || which php8 || which php'));
$phpBin     = explode("\n", $phpBin)[0];
$env        = "export HOME={$composerHome} && export COMPOSER_HOME={$composerHome} && ";
$installOut = run("{$env}cd {$dir} && {$phpBin} composer.phar install --no-dev --optimize-autoloader --no-interaction");

echo info("<pre>" . htmlspecialchars($installOut) . "</pre>");

if (is_dir($dir . '/vendor')) {
    echo ok("vendor/ 目錄建立成功！");
} else {
    echo fail("vendor/ 目錄未建立 — 請查看上方輸出確認錯誤");
}

// Step 6: Verify autoload
if (file_exists($dir . '/vendor/autoload.php')) {
    echo ok("autoload.php 存在 ✅");
} else {
    echo fail("autoload.php 不存在");
}

// Step 7: Clean up composer.phar (optional)
// @unlink($dir . '/composer.phar'); // Uncomment if you want auto-cleanup

?>
</ul>
<br>
<p class="done">✅ 完成！請立即刪除此檔案：<code>admin/composer-setup.php</code></p>
<p class="warn">⚠️ 安全提醒：安裝完成後，請手動刪除此 composer-setup.php 檔案！</p>
</body>
</html>
