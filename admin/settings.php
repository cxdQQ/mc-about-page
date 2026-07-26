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

// 处理保存
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bg_desktop'])) {
        setSetting('bg_desktop', trim($_POST['bg_desktop']));
    }
    if (isset($_POST['bg_mobile'])) {
        setSetting('bg_mobile', trim($_POST['bg_mobile']));
    }
    $message = '<div class="success-msg">✓ 背景图片已保存</div>';
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
        .settings-form { max-width:600px; }
        .settings-form .form-group { margin-bottom:24px; }
        .settings-form label { display:block; font-weight:600; margin-bottom:8px; font-size:0.95rem; }
        .settings-form .form-input { width:100%; padding:12px 16px; border-radius:12px; border:1px solid rgba(0,0,0,0.1); font-size:0.95rem; background:rgba(255,255,255,0.6); transition:all var(--transition); }
        .settings-form .form-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px var(--accent-glow); }
        .settings-form .hint { display:block; margin-top:4px; font-size:0.8rem; color:var(--text-muted); }
        .settings-form .preview-box { margin-top:8px; width:100%; height:100px; border-radius:12px; background-size:cover; background-position:center; border:1px solid rgba(0,0,0,0.06); }
        .success-msg { padding:12px 18px; border-radius:12px; background:rgba(6,182,212,0.1); color:#0891b2; font-weight:600; margin-bottom:24px; border:1px solid rgba(6,182,212,0.2); }
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

            <?= $message ?>

            <form method="POST" class="settings-form">
                <div class="sidebar-card" style="margin-bottom:24px;" data-liquid-glass>
                    <h3>桌面端背景图片</h3>
                    <div class="form-group">
                        <label for="bg_desktop">图片 URL</label>
                        <input type="url" name="bg_desktop" id="bg_desktop" class="form-input" 
                               placeholder="https://example.com/desktop-bg.jpg" 
                               value="<?= htmlspecialchars($bgDesktop) ?>">
                        <span class="hint">建议尺寸 1920×1080 以上，留空则使用默认背景</span>
                        <?php if ($bgDesktop): ?>
                        <div class="preview-box" style="background-image:url('<?= htmlspecialchars($bgDesktop) ?>');"></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sidebar-card" style="margin-bottom:24px;" data-liquid-glass>
                    <h3>手机端背景图片</h3>
                    <div class="form-group">
                        <label for="bg_mobile">图片 URL</label>
                        <input type="url" name="bg_mobile" id="bg_mobile" class="form-input" 
                               placeholder="https://example.com/mobile-bg.jpg" 
                               value="<?= htmlspecialchars($bgMobile) ?>">
                        <span class="hint">建议尺寸 768×1024 以上，留空则使用默认背景</span>
                        <?php if ($bgMobile): ?>
                        <div class="preview-box" style="background-image:url('<?= htmlspecialchars($bgMobile) ?>');"></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display:flex;gap:12px;">
                    <button type="submit" class="btn btn-primary">保存设置</button>
                    <a href="/admin/index.php" class="btn btn-ghost">返回后台</a>
                </div>
            </form>
        </main>
    </div>
    <script src="/assets/js/liquid-glass.js" defer></script>
</body>
</html>