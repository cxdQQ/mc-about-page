<?php
require_once __DIR__ . '/../includes/auth.php';
if (isLoggedIn()) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (login($username, $password)) {
        header('Location: /admin/index.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - 茉莉柚茶 材质包</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%2306b6d4'/><text x='50' y='68' font-size='50' font-weight='900' text-anchor='middle' fill='%23ffffff'>茉</text></svg>">
</head>
<body>
    <div class="login-page">
        <div class="login-card" data-liquid-glass data-liquid-elasticity="0.1" data-liquid-scale="60">
            <a href="/" class="navbar-brand" style="justify-content:center;margin-bottom:24px;">
                <span class="logo">茉</span>
                茉莉柚茶
            </a>
            <h1>管理员登录</h1>
            <p>登录以管理材质包内容</p>

            <?php if ($error): ?>
            <div style="padding:12px 16px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:10px;color:#fca5a5;font-size:0.9rem;margin-bottom:20px;text-align:center;">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" data-validate>
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" class="form-input" placeholder="请输入用户名" required>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="请输入密码" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:1rem;">
                    登 录
                </button>
            </form>

            <div style="text-align:center;margin-top:20px;">
                <a href="/" class="btn btn-ghost btn-sm" style="display:inline-flex;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
                    返回首页
                </a>
            </div>
        </div>
    </div>
    <script src="/assets/js/liquid-glass.js" defer></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>