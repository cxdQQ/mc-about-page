<?php
require_once __DIR__ . '/includes/functions.php';
$textures = getTextures();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>茉莉柚茶 材质包 - 为 Minecraft 注入全新视觉体验</title>
    <meta name="description" content="探索 茉莉柚茶 多款 Minecraft 材质包，从自然光影到幻想世界，每一款都精心打造">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%2306b6d4'/><text x='50' y='68' font-size='50' font-weight='900' text-anchor='middle' fill='%23ffffff'>茉</text></svg>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar" data-liquid-glass data-liquid-elasticity="0.08" data-liquid-scale="40">
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
                <li><a href="/" class="active">首页</a></li>
                <li><a href="#textures">材质包</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg">
            <div class="orb"></div>
            <div class="orb"></div>
            <div class="orb"></div>
        </div>
        <div class="hero-content">
            <div class="hero-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                茉莉柚茶 材质包系列
            </div>
            <h1>
                为你的 Minecraft<br>
                <span class="highlight">注入全新视觉</span>
            </h1>
            <p>
                探索 茉莉柚茶 精心打造的多款材质包，从自然光影到幻想世界，
                从像素复古到中世纪史诗，每一款都为你带来独特的视觉体验。
            </p>
            <div class="hero-actions">
                <a href="#textures" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                    浏览材质包
                </a>
            </div>
        </div>
    </section>

    <!-- Textures Grid -->
    <section class="section" id="textures">
        <div class="section-header">
            <h2>探索材质包</h2>
            <p>每一款材质包都经过精心打磨，为你的 Minecraft 世界带来全新感受</p>
        </div>

        <?php if (empty($textures)): ?>
        <div class="empty-state">
            <div class="icon">📦</div>
            <h3>暂无材质包</h3>
            <p>管理员正在准备中，请稍后再来</p>
        </div>
        <?php else: ?>
        <div class="texture-grid">
            <?php foreach ($textures as $texture): ?>
            <a href="/texture.php?id=<?= $texture['id'] ?>" class="texture-card" data-liquid-glass data-liquid-elasticity="0.15" data-liquid-scale="60" style="text-decoration:none;color:inherit;display:block;">
                <div class="texture-card-image" style="background: linear-gradient(135deg, 
                    <?php 
                        $colors = ['#1a1a2e,#16213e', '#2d1b3d,#1a1a2e', '#1a2e1a,#16213e', '#2e2e1a,#1a1a2e', '#1a2e2e,#16213e'];
                        echo $colors[$texture['id'] % count($colors)];
                    ?>
                );<?php if (!empty($texture['cover_image'])): ?>background:linear-gradient(135deg,rgba(0,0,0,0.5),rgba(0,0,0,0.3)),url('<?= htmlspecialchars($texture['cover_image']) ?>') center/cover no-repeat;<?php endif; ?>display:flex;align-items:center;justify-content:center;font-size:3rem;font-weight:900;color:rgba(255,255,255,0.1);">
                    <?php if (empty($texture['cover_image'])): ?>
                    <?= function_exists('mb_substr') ? mb_substr($texture['name'], 0, 2) : substr($texture['name'], 0, 6) ?>
                    <?php endif; ?>
                </div>
                <div class="texture-card-body">
                    <?php if (!empty($texture['tags'])): ?>
                    <div class="texture-card-tags">
                        <?php foreach (array_slice($texture['tags'], 0, 3) as $tag): ?>
                        <span class="texture-card-tag"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($texture['name']) ?></h3>
                    <p><?= htmlspecialchars($texture['short_description']) ?></p>
                </div>
                <div class="texture-card-footer">
                    <span>v<?= htmlspecialchars($texture['version']) ?></span>
                    <span><?= htmlspecialchars($texture['author']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer style="text-align:center;padding:40px 24px;color:var(--text-muted);font-size:0.85rem;border-top:1px solid rgba(0,0,0,0.06);">
        <p>&copy; 2026 <strong>抽象のQQ</strong> (QQ: <strong>3883739493</strong>). All rights reserved.</p>
        <p style="margin-top:4px;">为 Minecraft 打造高品质材质包. Minecraft 是 Mojang Studios 的商标.</p>
    </footer>

    <script src="/assets/js/liquid-glass.js" defer></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>