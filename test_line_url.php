<?php
require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->safeLoad();
require __DIR__ . '/admin/src/Config/Database.php';
require __DIR__ . '/admin/src/Services/LineLoginService.php';
$svc = new App\Services\LineLoginService();
echo "Login URL: " . $svc->getLoginUrl('dummy_state') . "\n";
