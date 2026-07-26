<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../includes/settings.php';

$db = getDb();

// 确保 settings 表存在
$db->exec("CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 上传目录
$uploadDir = __DIR__ . '/../uploads/backgrounds/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$message = '';
$error = '';

// 处理删除
if (isset($_GET['delete']) && ($_GET['delete'] === 'desktop' || $_GET['delete'] === 'mobile')) {
    $key = 'bg_' . $_GET['delete'];
    $old = getSetting($key);
    if ($old && strpos($old, '/uploads/backgrounds/') !== false) {
        $oldPath = __DIR__ . '/../' . ltrim($old, '/');
        if (file_exists($oldPath)) @unlink($oldPath);
    }
    setSetting($key, '');
    $message = '✓ 背景图片已移除';
}

// 处理上传/保存
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 桌面背景上传
    if (isset($_FILES['bg_desktop_file']) && $_FILES['bg_desktop_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['bg_desktop_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $error = '桌面背景：仅支持 jpg/png/webp/gif 格式';
        } else {
            $filename = 'desktop_' . time() . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // 删除旧文件
                $old = getSetting('bg_desktop');
                if ($old && strpos($old, '/uploads/backgrounds/') !== false) {
                    $oldPath = __DIR__ . '/../' . ltrim($old, '/');
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                setSetting('bg_desktop', '/uploads/backgrounds/' . $filename);
                $message = '✓ 桌面背景已更新';
            } else {
                $error = '桌面背景上传失败';
            }
        }
    }
    // 桌面背景 URL
    elseif (isset($_POST['bg_desktop_url']) && trim($_POST['bg_desktop_url'])) {
        $url = trim($_POST['bg_desktop_url']);
        $old = getSetting('bg_desktop');
        if ($old && strpos($old, '/uploads/backgrounds/') !== false) {
            $oldPath = __DIR__ . '/../' . ltrim($old, '/');
            if (file_exists($oldPath)) @unlink($oldPath);
        }
        setSetting('bg_desktop', $url);
        $message = '✓ 桌面背景已更新';
    }

    // 手机背景上传
    if (isset($_FILES['bg_mobile_file']) && $_FILES['bg_mobile_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['bg_mobile_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $error = '手机背景：仅支持 jpg/png/webp/gif 格式';
        } else {
            $filename = 'mobile_' . time() . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $old = getSetting('bg_mobile');
                if ($old && strpos($old, '/uploads/backgrounds/') !== false) {
                    $oldPath = __DIR__ . '/../' . ltrim($old, '/');
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                setSetting('bg_mobile', '/uploads/backgrounds/' . $filename);
                $message = '✓ 手机背景已更新';
            } else {
                $error = '手机背景上传失败';
            }
        }
    }
    elseif (isset($_POST['bg_mobile_url']) && trim($_POST['bg_mobile_url'])) {
        $url = trim($_POST['bg_mobile_url']);
        $old = getSetting('bg_mobile');
        if ($old && strpos($old, '/uploads/backgrounds/') !== false) {
            $oldPath = __DIR__ . '/../' . ltrim($old, '/');
            if (file_exists($oldPath)) @unlink($oldPath);
        }
        setSetting('bg_mobile', $url);
        $message = '✓ 手机背景已更新';
    }
}

$settings = getSettings();
$bgDesktop = $settings['bg_desktop'] ?? '';
$bgMobile = $settings['bg_mobile'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网站设置 - 茉莉柚茶 管理后台</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .settings-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        @media (max-width:900px) { .settings-grid { grid-template-columns:1fr; } }
        .settings-card { padding:24px; border-radius:var(--radius); background:rgba(255,255,255,0.55); border:1px solid rgba(255,255,255,0.3); }
        .settings-card h3 { font-size:1.05rem; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
        .settings-card .preview-box { width:100%; height:160px; border-radius:12px; background-size:cover; background-position:center; border:1px solid rgba(0,0,0,0.06); margin-bottom:16px; background-color:#eef2f6; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem; color:var(--text-secondary); }
        .form-input { width:100%; padding:10px 14px; border-radius:10px; border:1px solid rgba(0,0,0,0.1); font-size:0.9rem; background:rgba(255,255,255,0.7); transition:all var(--transition); }
        .form-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px var(--accent-glow); }
        .form-file { width:100%; padding:8px 0; font-size:0.9rem; }
        .divider { display:flex; align-items:center; gap:12px; margin:16px 0; color:var(--text-muted); font-size:0.85rem; }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background:rgba(0,0,0,0.06); }
        .btn-group { display:flex; gap:8px; flex-wrap:wrap; }
        .success-msg { padding:12px 18px; border-radius:12px; background:rgba(6,182,212,0.1); color:#0891b2; font-weight:600; margin-bottom:20px; border:1px solid rgba(6,182,212,0.2); display:flex; align-items:center; gap:8px; }
        .error-msg { padding:12px 18px; border-radius:12px; background:rgba(239,68,68,0.1); color:#dc2626; font-weight:600; margin-bottom:20px; border:1px solid rgba(239,68,68,0.2); display:flex; align-items:center; gap:8px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar" data-liquid-glass data-liquid-elasticity="0.08" data-liquid-scale="40">
            <div class="admin-sidebar-brand">
                <span class="logo" style="width:32px;height:32px;font-size:15px;">茉</span>
                <span>管理后台</span>
            </div>
            <ul class="admin-sidebar-nav">
                <li><a href="/admin/index.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    材质管理
                </a></li>
                <li><a href="/admin/settings.php" class="active">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    网站设置
                </a></li>
                <li><a href="/">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    返回首页
                </a></li>
                <li style="margin-top:auto;"><a href="/admin/logout.php" style="color:#ef4444;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    退出登录
                </a></li>
            </ul>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    网站设置
                </h1>
            </div>

            <?php if ($message): ?>
                <div class="success-msg">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error-msg">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="settings-grid">
                <!-- 桌面背景 -->
                <div class="settings-card" data-liquid-glass>
                    <h3>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        桌面端背景
                    </h3>
                    <div class="preview-box" style="background-image:url('<?= htmlspecialchars($bgDesktop ?: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="160" fill="%23eef2f6"><rect width="400" height="160"/><text x="200" y="85" text-anchor="middle" fill="%2394a3b8" font-size="14">暂无背景图片</text></svg>') ?>');"></div>
                    
                    <div class="form-group">
                        <label>上传图片</label>
                        <input type="file" name="bg_desktop_file" class="form-file" accept="image/jpeg,image/png,image/webp,image/gif">
                    </div>

                    <div class="divider">或输入图片 URL</div>

                    <div class="form-group">
                        <label>图片 URL</label>
                        <input type="url" name="bg_desktop_url" class="form-input" placeholder="https://example.com/bg.jpg" value="">
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" style="flex:1;">保存桌面背景</button>
                        <?php if ($bgDesktop): ?>
                            <a href="?delete=desktop" class="btn btn-ghost" style="color:#ef4444;" onclick="return confirm('确定移除桌面背景？')">移除</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 手机背景 -->
                <div class="settings-card" data-liquid-glass>
                    <h3>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        手机端背景
                    </h3>
                    <div class="preview-box" style="background-image:url('<?= htmlspecialchars($bgMobile ?: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="160" fill="%23eef2f6"><rect width="400" height="160"/><text x="200" y="85" text-anchor="middle" fill="%2394a3b8" font-size="14">暂无背景图片</text></svg>') ?>');"></div>

                    <div class="form-group">
                        <label>上传图片</label>
                        <input type="file" name="bg_mobile_file" class="form-file" accept="image/jpeg,image/png,image/webp,image/gif">
                    </div>

                    <div class="divider">或输入图片 URL</div>

                    <div class="form-group">
                        <label>图片 URL</label>
                        <input type="url" name="bg_mobile_url" class="form-input" placeholder="https://example.com/bg-mobile.jpg" value="">
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" style="flex:1;">保存手机背景</button>
                        <?php if ($bgMobile): ?>
                            <a href="?delete=mobile" class="btn btn-ghost" style="color:#ef4444;" onclick="return confirm('确定移除手机背景？')">移除</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <div style="margin-top:20px;font-size:0.85rem;color:var(--text-muted);text-align:center;">
                建议：桌面端使用 1920×1080 以上横图 · 手机端使用 768×1024 以上竖图 · 支持 jpg/png/webp/gif
            </div>
        </main>
    </div>
    <script src="/assets/js/liquid-glass.js" defer></script>
</body>
</html>