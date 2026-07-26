<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$texture = null;
$isEdit = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $texture = getTexture((int)$_GET['id']);
    if ($texture) {
        $isEdit = true;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? '编辑' : '添加' ?>材质包 - 茉莉柚茶 管理</title>
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
                <li><a href="/admin/index.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    材质管理
                </a></li>
                <li><a href="/admin/edit.php" class="active">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/></svg>
                    <?= $isEdit ? '编辑材质' : '添加材质' ?>
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
                    <h1><?= $isEdit ? '编辑材质包' : '添加材质包' ?></h1>
                    <p style="color:var(--text-secondary);font-size:0.9rem;margin-top:4px;">
                        <?= $isEdit ? '修改「' . htmlspecialchars($texture['name']) . '」的内容' : '创建一个新的材质包介绍' ?>
                    </p>
                </div>
            </div>

            <form action="/api/textures.php<?= $isEdit ? '?id=' . $texture['id'] : '' ?>" method="POST" style="max-width:800px;" data-validate>
                <?php if ($isEdit): ?>
                <input type="hidden" name="action" value="update">
                <?php else: ?>
                <input type="hidden" name="action" value="create">
                <?php endif; ?>

                <!-- Basic Info -->
                <div class="sidebar-card" style="margin-bottom:24px;">
                    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;">基本信息</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">材质包名称 *</label>
                            <input type="text" id="name" name="name" class="form-input" placeholder="例如：茉莉柚茶 自然光影" value="<?= $isEdit ? htmlspecialchars($texture['name']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="slug">URL 标识</label>
                            <input type="text" id="slug" name="slug" class="form-input" placeholder="自动生成或手动输入" value="<?= $isEdit ? htmlspecialchars($texture['slug']) : '' ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="short_description">简短描述</label>
                        <input type="text" id="short_description" name="short_description" class="form-input" placeholder="一句话概括材质包特点" value="<?= $isEdit ? htmlspecialchars($texture['short_description']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">详细介绍 (支持 HTML)</label>
                        <textarea id="description" name="description" class="form-textarea" placeholder="材质包的详细介绍内容..."><?= $isEdit ? htmlspecialchars($texture['description']) : '' ?></textarea>
                    </div>
                </div>

                <!-- Cover Image -->
                <div class="sidebar-card" style="margin-bottom:24px;">
                    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;">封面图片</h3>
                    <div class="form-group">
                        <div style="display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                            <label class="btn btn-ghost btn-sm" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                上传封面
                                <input type="file" accept="image/*" class="upload-input" data-target="cover_image" hidden>
                            </label>
                        </div>
                        <input type="text" id="cover_image" name="cover_image" class="form-input" placeholder="封面图片 URL" value="<?= $isEdit ? htmlspecialchars($texture['cover_image'] ?? '') : '' ?>" oninput="previewMedia('cover_image', this.value)" style="margin-bottom:8px;">
                        <div id="cover_image-preview" class="upload-preview" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
                    </div>
                </div>

                <!-- Meta Info -->
                <div class="sidebar-card" style="margin-bottom:24px;">
                    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;">详细信息</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="version">游戏版本</label>
                            <input type="text" id="version" name="version" class="form-input" placeholder="例如：1.21" value="<?= $isEdit ? htmlspecialchars($texture['version']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label for="author">作者</label>
                            <input type="text" id="author" name="author" class="form-input" placeholder="例如：茉莉柚茶 Team" value="<?= $isEdit ? htmlspecialchars($texture['author']) : '' ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="release_date">发布日期</label>
                            <input type="date" id="release_date" name="release_date" class="form-input" value="<?= $isEdit ? htmlspecialchars($texture['release_date']) : date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="buy_url">购买链接</label>
                            <input type="url" id="buy_url" name="buy_url" class="form-input" placeholder="https://..." value="<?= $isEdit ? htmlspecialchars($texture['buy_url']) : '' ?>">
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="sidebar-card" style="margin-bottom:24px;">
                    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;">材质特性</h3>
                    <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:12px;">每行一个特性描述</p>
                    <div class="form-group">
                        <textarea id="features" name="features" class="form-textarea" placeholder="真实光影追踪&#10;动态天气系统&#10;水面反射效果" style="min-height:100px;"><?= $isEdit && !empty($texture['features']) ? implode("\n", $texture['features']) : '' ?></textarea>
                    </div>
                </div>

                <!-- Images -->
                <div class="sidebar-card" style="margin-bottom:24px;">
                    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;">截图</h3>
                    <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:12px;">每行一个图片 URL，或点击上传图片</p>
                    <div class="form-group">
                        <div style="display:flex;gap:8px;margin-bottom:8px;">
                            <label class="btn btn-ghost btn-sm" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                上传图片
                                <input type="file" accept="image/*" class="upload-input" data-target="images" hidden multiple>
                            </label>
                        </div>
                        <textarea id="images" name="images" class="form-textarea" placeholder="https://example.com/screenshot1.png&#10;https://example.com/screenshot2.png" style="min-height:80px;" oninput="previewMedia('images', this.value)"><?= $isEdit && !empty($texture['images']) ? implode("\n", $texture['images']) : '' ?></textarea>
                        <div id="images-preview" class="upload-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
                    </div>
                </div>

                <!-- Videos -->
                <div class="sidebar-card" style="margin-bottom:24px;">
                    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;">视频</h3>
                    <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:12px;">每行一个视频 URL，或点击上传视频文件（MP4/WebM）</p>
                    <div class="form-group">
                        <div style="display:flex;gap:8px;margin-bottom:8px;">
                            <label class="btn btn-ghost btn-sm" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                上传视频
                                <input type="file" accept="video/*" class="upload-input" data-target="videos" hidden multiple>
                            </label>
                        </div>
                        <textarea id="videos" name="videos" class="form-textarea" placeholder="https://www.youtube.com/embed/xxxxx&#10;或上传后自动填入上传URL" style="min-height:80px;" oninput="previewMedia('videos', this.value)"><?= $isEdit && !empty($texture['videos']) ? implode("\n", $texture['videos']) : '' ?></textarea>
                        <div id="videos-preview" class="upload-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
                    </div>
                </div>

                <!-- Tags -->
                <div class="sidebar-card" style="margin-bottom:24px;">
                    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;">标签</h3>
                    <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:12px;">用逗号分隔</p>
                    <div class="form-group">
                        <input type="text" id="tags" name="tags" class="form-input" placeholder="光影, 自然, 真实" value="<?= $isEdit && !empty($texture['tags']) ? htmlspecialchars(implode(', ', $texture['tags'])) : '' ?>">
                    </div>
                </div>

                <!-- Submit -->
                <div style="display:flex;gap:12px;padding-top:8px;">
                    <button type="submit" class="btn btn-primary" style="padding:14px 32px;font-size:1rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <?= $isEdit ? '保存修改' : '创建材质包' ?>
                    </button>
                    <a href="/admin/index.php" class="btn btn-ghost" style="padding:14px 32px;font-size:1rem;">取消</a>
                </div>
            </form>
        </main>
    </div>
    <script src="/assets/js/liquid-glass.js" defer></script>
    <script src="/assets/js/main.js"></script>

    <!-- Upload Progress Modal -->
    <div id="upload-modal" class="upload-modal">
        <div class="upload-modal-content" data-liquid-glass data-liquid-elasticity="0.15" data-liquid-scale="60">
            <div class="upload-modal-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span>上传文件中</span>
            </div>
            <div class="upload-modal-body">
                <div class="upload-filename" id="upload-filename">准备上传...</div>
                <div class="upload-batch-info" id="upload-batch-info"></div>
                <div class="upload-progress-bar">
                    <div class="upload-progress-fill" id="upload-progress-fill"></div>
                </div>
                <div class="upload-progress-text" id="upload-progress-text">0%</div>
            </div>
            <div class="upload-modal-footer">
                <button type="button" class="btn btn-ghost btn-sm" id="upload-cancel-btn" style="padding:8px 20px;font-size:0.85rem;">取消上传</button>
            </div>
        </div>
    </div>
</body>
</html>