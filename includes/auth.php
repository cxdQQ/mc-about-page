<?php
session_start();
require_once __DIR__ . '/db.php';

function isLoggedIn(): bool {
    return isset($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => '未登录']);
        } else {
            header('Location: /admin/login.php');
        }
        exit;
    }
}

function login(string $username, string $password): bool {
    $db = getDb();
    
    $stmt = $db->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        return true;
    }
    return false;
}

function logout(): void {
    session_destroy();
}
?>