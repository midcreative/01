<?php
require_once __DIR__ . '/admin/vendor/autoload.php';
use Dotenv\Dotenv;
use App\Config\Database;
use App\Core\Auth;

$dotenv = Dotenv::createImmutable(__DIR__ . '/admin');
$dotenv->safeLoad();

try {
    $pdo = Database::getInstance();
    $auth = new Auth();
    
    // Check if admin user exists
    $stmt = $pdo->query("SELECT id, username, password FROM admin_users WHERE username = 'admin'");
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "Admin user not found. Creating...\n";
        $password = $auth->hashPassword('Admin@2026!');
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES ('admin', ?)");
        $stmt->execute([$password]);
        echo "Admin user created with password: Admin@2026!\n";
    } else {
        echo "Admin user exists.\n";
        // Verify current password
        $isValid = $auth->verifyPassword('Admin@2026!', $user['password']);
        if ($isValid) {
            echo "Password for admin is indeed Admin@2026!\n";
        } else {
            echo "Password for admin is NOT Admin@2026!. Resetting...\n";
            $password = $auth->hashPassword('Admin@2026!');
            $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
            $stmt->execute([$password]);
            echo "Password reset to Admin@2026!\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
