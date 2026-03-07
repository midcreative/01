<?php

declare(strict_types=1);

// ─── Bootstrap ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Auth;
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PostController;
use App\Controllers\StatsController;
use App\Controllers\VolunteerController;
use App\Controllers\PetitionController;
use App\Controllers\WhitepaperController;
use App\Controllers\CategoryController;
use App\Controllers\TownController;
use App\Controllers\SettingController;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set timezone for Taiwan
date_default_timezone_set('Asia/Taipei');

// ─── Router ────────────────────────────────────────────────────────────────
$router = new Router();
$auth   = new Auth();

// Auth
$router->get( '/admin',         fn() => (new AuthController($auth))->showLogin());
$router->get( '/admin/',        fn() => (new AuthController($auth))->showLogin());
$router->post('/admin/login',   fn() => (new AuthController($auth))->handleLogin());
$router->get( '/admin/logout',  fn() => (new AuthController($auth))->logout());

// Dashboard
$router->get('/admin/dashboard', fn() => (new DashboardController($auth))->index());

// Posts (服務日記)
$router->get( '/admin/posts',           fn() => (new PostController($auth))->index());
$router->get( '/admin/posts/create',    fn() => (new PostController($auth))->create());
$router->post('/admin/posts/store',     fn() => (new PostController($auth))->store());
$router->get( '/admin/posts/{id}/edit', fn(string $id) => (new PostController($auth))->edit((int)$id));
$router->post('/admin/posts/{id}',      fn(string $id) => (new PostController($auth))->update((int)$id));
$router->post('/admin/posts/{id}/delete', fn(string $id) => (new PostController($auth))->destroy((int)$id));

// Stats (數據看板)
$router->get( '/admin/stats',        fn() => (new StatsController($auth))->index());
$router->post('/admin/stats/update', fn() => (new StatsController($auth))->update());

// Volunteers (志工招募)
$router->get( '/admin/volunteers',              fn() => (new VolunteerController($auth))->index());
$router->get( '/admin/volunteers/create',       fn() => (new VolunteerController($auth))->create());
$router->post('/admin/volunteers/store',        fn() => (new VolunteerController($auth))->store());
$router->get( '/admin/volunteers/{id}/edit',    fn(string $id) => (new VolunteerController($auth))->edit((int)$id));
$router->post('/admin/volunteers/{id}',         fn(string $id) => (new VolunteerController($auth))->update((int)$id));
$router->get( '/admin/volunteers/{id}/apps',    fn(string $id) => (new VolunteerController($auth))->applications((int)$id));

// Petitions (連署提案)
$router->get( '/admin/petitions',             fn() => (new PetitionController($auth))->index());
$router->get( '/admin/petitions/create',      fn() => (new PetitionController($auth))->create());
$router->post('/admin/petitions/store',       fn() => (new PetitionController($auth))->store());
$router->get( '/admin/petitions/{id}/edit',   fn(string $id) => (new PetitionController($auth))->edit((int)$id));
$router->post('/admin/petitions/{id}',        fn(string $id) => (new PetitionController($auth))->update((int)$id));
$router->post('/admin/petitions/{id}/delete', fn(string $id) => (new PetitionController($auth))->destroy((int)$id));
$router->get( '/admin/petitions/{id}',        fn(string $id) => (new PetitionController($auth))->show((int)$id));
$router->post('/admin/petitions/{id}/status', fn(string $id) => (new PetitionController($auth))->updateStatus((int)$id));

// Whitepaper (行動白皮書)
$router->get( '/admin/whitepaper',              fn() => (new WhitepaperController($auth))->index());
$router->get( '/admin/whitepaper/create',       fn() => (new WhitepaperController($auth))->create());
$router->post('/admin/whitepaper/store',        fn() => (new WhitepaperController($auth))->store());
$router->get( '/admin/whitepaper/{id}/edit',    fn(string $id) => (new WhitepaperController($auth))->edit((int)$id));
$router->post('/admin/whitepaper/{id}',         fn(string $id) => (new WhitepaperController($auth))->update((int)$id));
$router->post('/admin/whitepaper/{id}/delete',  fn(string $id) => (new WhitepaperController($auth))->destroy((int)$id));

// Categories (主軸分類)
$router->get( '/admin/categories',            fn() => (new CategoryController($auth))->index());
$router->post('/admin/categories/store',      fn() => (new CategoryController($auth))->store());
$router->post('/admin/categories/{id}',       fn(string $id) => (new CategoryController($auth))->update((int)$id));
$router->post('/admin/categories/{id}/delete',fn(string $id) => (new CategoryController($auth))->destroy((int)$id));

// Towns (鄉鎮分類)
$router->get( '/admin/towns',            fn() => (new TownController($auth))->index());
$router->post('/admin/towns/store',      fn() => (new TownController($auth))->store());
$router->post('/admin/towns/{id}',       fn(string $id) => (new TownController($auth))->update((int)$id));
$router->post('/admin/towns/{id}/delete',fn(string $id) => (new TownController($auth))->destroy((int)$id));

// Settings (系統設定)
$router->get( '/admin/settings',         fn() => (new SettingController($auth))->index());
$router->post('/admin/settings/update',  fn() => (new SettingController($auth))->update());

// Dispatch
try {
    $router->dispatch();
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>CRASH LOG:</h1><pre>" . (string)$e . "</pre>";
}
