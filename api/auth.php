<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    logout();
    header('Location: /admin/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        jsonResponse(['success' => true, 'message' => '登录成功']);
    } else {
        jsonResponse(['success' => false, 'message' => '用户名或密码错误'], 401);
    }
}

if ($action === 'check') {
    jsonResponse([
        'success' => true,
        'loggedIn' => isLoggedIn(),
        'username' => $_SESSION['admin_username'] ?? null
    ]);
}

jsonResponse(['success' => false, 'message' => '未知操作'], 400);
?>