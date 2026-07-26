<?php
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$texture = getTexture($id);

if (!$texture) {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($texture['name']) ?> - 茉莉柚茶 材质包</title>
    <meta name="description" content="<?= htmlspecialchars($texture['short_description']) ?>">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%2306b6d4'/><text x='50' y='68' font-size='50' font-weight='900' text-anchor='middle' fill='%23ffffff'>茉</text></svg>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" data-liquid-glass data-liquid-elasticity="0.08" data-liquid-scale="40">
        <div class="navbar-inner">
            <a href="/" class="navbar-brand">
                <span class="logo">茉</span>
                茉莉柚茶
            </a>
            <button class="navbar-toggle" onclick="document.querySelector('.navbar-links').classList.toggle('open')" aria-label="切换菜单">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12h18M3 6h18M3 18h18"/>
                </svg>
            </button>
            <ul class="navbar-links">
                <li><a href="/">首页</a></li>
                <li><a href="/#textures">材质包</a></li>
            </ul>
        </div>
    </nav>

    <!-- Detail Hero -->
    <section class="detail-hero">
        <div class="detail-hero-bg">
            <?php if (!empty($texture['cover_image'])): ?>
            <div style="width:100%;height:100%;background:linear-gradient(135deg, rgba(0,0,0,0.6), rgba(0,0,0,0.4)), url('<?= htmlspecialchars($texture['cover_image']) ?>') center/cover no-repeat;"></div>
            <?php else: ?>
            <div style="width:100%;height:100%;background:linear-gradient(135deg,
                <?php 
                    $colors = ['#1a1a2e,#16213e', '#2d1b3d,#1a1a2e', '#1a2e1a,#16213e', '#2e2e1a,#1a1a2e'];
                    echo $colors[$texture['id'] % count($colors)];
                ?>
            );"></div>
            <?php endif; ?>
        </div>
        <div class="detail-hero-overlay"></div>
        <div class="detail-hero-content">
            <a href="/#textures" class="btn btn-glass btn-sm" style="margin-bottom:20px;display:inline-flex;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
                返回
            </a>
            <h1><?= htmlspecialchars($texture['name']) ?></h1>
            <div class="detail-meta">
                <span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    v<?= htmlspecialchars($texture['version']) ?>
                </span>
                <span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <?= htmlspecialchars($texture['author']) ?>
                </span>
                <span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?= htmlspecialchars($texture['release_date']) ?>
                </span>
            </div>
        </div>
    </section>

    <!-- Detail Body -->
    <div class="detail-body">
        <div class="detail-main">
            <!-- Description -->
            <div class="detail-description">
                <?= $texture['description'] ?>
            </div>

            <!-- Features -->
            <?php if (!empty($texture['features'])): ?>
            <div class="detail-features">
                <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:16px;">材质特性</h2>
                <?php foreach ($texture['features'] as $feature): ?>
                <div class="feature-item" data-liquid-glass data-liquid-elasticity="0.2" data-liquid-scale="30">
                    <div class="icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <span><?= htmlspecialchars($feature) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Image Gallery -->
            <?php if (!empty($texture['images'])): ?>
            <div class="gallery">
                <h2>截图展示</h2>
                <div class="gallery-grid">
                    <?php foreach ($texture['images'] as $img): ?>
                    <div class="gallery-item glass-sm" data-liquid-glass data-liquid-elasticity="0.1" data-liquid-scale="30">
                        <?php if (strpos($img, '/assets/uploads/') === 0): ?>
                        <img src="<?= htmlspecialchars($img) ?>" alt="截图" loading="lazy">
                        <?php else: ?>
                        <img src="<?= htmlspecialchars($img) ?>" alt="截图" loading="lazy" onerror="this.parentElement.innerHTML='<div style=\"width:100%;height:100%;background:linear-gradient(135deg,#1a1a2e,#16213e);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.1);font-size:2rem;\">🖼️</div>'">
                        <?php endif; ?>
                        <div class="overlay">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Video Section -->
            <?php if (!empty($texture['videos'])): ?>
            <div class="video-section">
                <h2>视频展示</h2>
                <div class="video-grid">
                    <?php foreach ($texture['videos'] as $video): ?>
                    <div class="video-wrapper glass-sm">
                        <?php if (strpos($video, '/assets/uploads/') === 0): ?>
                        <video src="<?= htmlspecialchars($video) ?>" controls preload="metadata" style="width:100%;height:100%;border-radius:12px;background:#000;"></video>
                        <?php else: ?>
                        <iframe src="<?= htmlspecialchars($video) ?>" allowfullscreen loading="lazy"></iframe>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="detail-sidebar">
            <div class="sidebar-card glass" data-liquid-glass data-liquid-elasticity="0.12" data-liquid-scale="50">
                <h3>材质信息</h3>
                <div class="sidebar-info">
                    <div class="sidebar-info-row">
                        <span class="label">名称</span>
                        <span class="value"><?= htmlspecialchars($texture['name']) ?></span>
                    </div>
                    <div class="sidebar-info-row">
                        <span class="label">版本</span>
                        <span class="value"><?= htmlspecialchars($texture['version']) ?></span>
                    </div>
                    <div class="sidebar-info-row">
                        <span class="label">作者</span>
                        <span class="value"><?= htmlspecialchars($texture['author']) ?></span>
                    </div>
                    <div class="sidebar-info-row">
                        <span class="label">发布日期</span>
                        <span class="value"><?= htmlspecialchars($texture['release_date']) ?></span>
                    </div>
                    <?php if (!empty($texture['tags'])): ?>
                    <div class="sidebar-info-row">
                        <span class="label">标签</span>
                        <span class="value" style="display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end;">
                            <?php foreach ($texture['tags'] as $tag): ?>
                            <span class="texture-card-tag"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($texture['buy_url'])): ?>
                <a href="<?= htmlspecialchars($texture['buy_url']) ?>" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:20px;" target="_blank">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    购买材质包
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="text-align:center;padding:40px 24px;color:var(--text-muted);font-size:0.85rem;border-top:1px solid rgba(0,0,0,0.06);">
        <p>&copy; 2026 <strong>抽象のQQ</strong> (QQ: <strong>3883739493</strong>). All rights reserved.</p>
        <p style="margin-top:4px;">为 Minecraft 打造高品质材质包. Minecraft 是 Mojang Studios 的商标.</p>
    </footer>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close" data-liquid-glass data-liquid-elasticity="0.25" data-liquid-scale="20" onclick="document.getElementById('lightbox').classList.remove('active')">✕</button>
        <img id="lightbox-img" src="" alt="预览">
    </div>

    <script src="/assets/js/liquid-glass.js" defer></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>