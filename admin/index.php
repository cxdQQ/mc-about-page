<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$textures = getTextures();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    deleteTexture((int)$_GET['delete']);
    header('Location: /admin/index.php?deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 茉莉柚茶 材质包</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%2306b6d4'/><text x='50' y='68' font-size='50' font-weight='900' text-anchor='middle' fill='%23ffffff'>茉</text></svg>">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" data-liquid-glass data-liquid-elasticity="0.08" data-liquid-scale="40">
            <div class="admin-sidebar-brand">
                <span class="logo" style="width:32px;height:32px;font-size:15px;">茉</span>
                茉莉柚茶 管理
            </div>
            <ul class="admin-sidebar-nav">
                <li><a href="/admin/index.php" class="active">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    材质管理
                </a></li>
                <li><a href="/admin/edit.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/></svg>
                    添加材质
                </a></li>
                <li><a href="/">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
                    返回首页
                </a></li>
                <li style="margin-top:auto;"><a href="/api/auth.php?action=logout" style="color:var(--text-muted);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    退出登录
                </a></li>
            </ul>
        </aside>

        <!-- Main -->
        <main class="admin-main">
            <div class="admin-header">
                <div>
                    <h1>材质管理</h1>
                    <p style="color:var(--text-secondary);font-size:0.9rem;margin-top:4px;">共 <?= count($textures) ?> 个材质包</p>
                </div>
                <a href="/admin/edit.php" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/></svg>
                    添加材质包
                </a>
            </div>

            <?php if (isset($_GET['deleted'])): ?>
            <div style="padding:14px 20px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);border-radius:12px;color:var(--accent);margin-bottom:24px;font-size:0.9rem;font-weight:500;">
                材质包已删除
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['saved'])): ?>
            <div style="padding:14px 20px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);border-radius:12px;color:var(--accent);margin-bottom:24px;font-size:0.9rem;font-weight:500;">
                材质包已保存
            </div>
            <?php endif; ?>

            <?php if (empty($textures)): ?>
            <div class="empty-state" style="background:rgba(255,255,255,0.02);border-radius:16px;border:1px solid rgba(255,255,255,0.06);">
                <div class="icon">📦</div>
                <h3>暂无材质包</h3>
                <p>点击上方按钮添加第一个材质包</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名称</th>
                            <th>版本</th>
                            <th>作者</th>
                            <th>标签</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($textures as $texture): ?>
                        <tr>
                            <td style="color:var(--text-muted);">#<?= $texture['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($texture['name']) ?></strong>
                                <div style="font-size:0.8rem;color:var(--text-muted);margin-top:2px;"><?= htmlspecialchars(function_exists('mb_substr') ? mb_substr($texture['short_description'], 0, 40) : substr($texture['short_description'], 0, 40)) ?>...</div>
                            </td>
                            <td><?= htmlspecialchars($texture['version']) ?></td>
                            <td><?= htmlspecialchars($texture['author']) ?></td>
                            <td>
                                <?php foreach (array_slice($texture['tags'], 0, 2) as $tag): ?>
                                <span class="texture-card-tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td style="color:var(--text-muted);font-size:0.85rem;"><?= $texture['created_at'] ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="/admin/edit.php?id=<?= $texture['id'] ?>" class="btn btn-ghost btn-sm">编辑</a>
                                    <a href="/texture.php?id=<?= $texture['id'] ?>" class="btn btn-ghost btn-sm" target="_blank">预览</a>
                                    <a href="?delete=<?= $texture['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('确定要删除「<?= htmlspecialchars($texture['name']) ?>」吗？')">删除</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>
    <script src="/assets/js/liquid-glass.js" defer></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>