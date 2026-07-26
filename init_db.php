<?php
/**
 * 数据库初始化脚本 (MySQL 版)
 * 运行此脚本以创建数据库表和初始数据
 * 访问: /init_db.php
 * 
 * 前提: 已创建数据库 qwq_textures, 或手动运行 setup.sql
 */

require_once __DIR__ . '/includes/db.php';
$db = getDb();

// 创建设置表
$db->exec("
    CREATE TABLE IF NOT EXISTS settings (
        `key` VARCHAR(100) PRIMARY KEY,
        `value` TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// 插入默认设置
$stmt = $db->prepare("SELECT COUNT(*) FROM settings");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?)")->execute(['bg_desktop', '']);
    $db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?)")->execute(['bg_mobile', '']);
}

// 创建数据表
$db->exec("
    CREATE TABLE IF NOT EXISTS texture_packs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        short_description VARCHAR(500) DEFAULT '',
        cover_image VARCHAR(500) DEFAULT '',
        version VARCHAR(50) DEFAULT '',
        author VARCHAR(255) DEFAULT '',
        release_date DATE DEFAULT NULL,
        buy_url VARCHAR(500) DEFAULT '',
        features JSON,
        images JSON,
        videos JSON,
        tags JSON,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$db->exec("
    CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 插入默认管理员 (admin / admin123)
$stmt = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
$stmt->execute(['admin']);
if ($stmt->fetchColumn() == 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)")->execute(['admin', $hash]);
}

// 插入示例数据
$sampleCount = $db->query("SELECT COUNT(*) FROM texture_packs")->fetchColumn();
if ($sampleCount == 0) {
    $samples = [
        [
            'name' => '茉莉柚茶 自然光影',
            'slug' => 'qwq-nature-light',
            'description' => '<p>茉莉柚茶 自然光影材质包为 Minecraft 带来最真实的光影体验。通过精心调整的光照算法和色彩映射，让每一个方块都呈现出自然界的真实质感。</p><p>从清晨的第一缕阳光到夜晚的星光点点，每一刻的光影变化都经过精心设计，为您呈现一个栩栩如生的 Minecraft 世界。</p>',
            'short_description' => '为 Minecraft 带来最真实的光影体验',
            'version' => '1.21',
            'author' => '茉莉柚茶 Team',
            'release_date' => '2024-12-01',
            'buy_url' => 'https://example.com/buy/nature-light',
            'features' => '["真实光影追踪","动态天气系统","水面反射效果","柔和阴影渲染","自定义云朵纹理"]',
            'images' => '[]',
            'videos' => '[]',
            'tags' => '["光影","自然","真实"]'
        ],
        [
            'name' => '茉莉柚茶 幻想世界',
            'slug' => 'qwq-fantasy',
            'description' => '<p>进入 茉莉柚茶 幻想世界材质包，探索一个充满魔法与奇迹的 Minecraft 世界。色彩鲜艳的方块、梦幻的光效和独特的生物纹理，让您仿佛置身于童话故事中。</p><p>每一个细节都散发着奇幻的气息，从发光的蘑菇森林到漂浮的魔法岛屿，带给您前所未有的视觉体验。</p>',
            'short_description' => '充满魔法与奇迹的幻想风格材质包',
            'version' => '1.21',
            'author' => '茉莉柚茶 Team',
            'release_date' => '2024-11-15',
            'buy_url' => 'https://example.com/buy/fantasy',
            'features' => '["梦幻色彩系统","发光粒子效果","魔法生物纹理","独特方块设计","星空背景"]',
            'images' => '[]',
            'videos' => '[]',
            'tags' => '["幻想","魔法","色彩"]'
        ],
        [
            'name' => '茉莉柚茶 像素复古',
            'slug' => 'qwq-pixel-retro',
            'description' => '<p>致敬经典！茉莉柚茶 像素复古材质包将 Minecraft 带回最原始的像素风格，同时加入现代渲染技术，在保留 nostalgic 情怀的同时提升视觉体验。</p><p>清晰的像素边缘、怀旧的色彩搭配，让您重温第一次踏入 Minecraft 世界时的感动与惊喜。</p>',
            'short_description' => '致敬经典的像素复古风格',
            'version' => '1.20',
            'author' => '茉莉柚茶 Team',
            'release_date' => '2024-10-20',
            'buy_url' => 'https://example.com/buy/pixel-retro',
            'features' => '["16x16 经典像素","怀旧色彩方案","复古GUI界面","原版风格增强","低分辨率纹理"]',
            'images' => '[]',
            'videos' => '[]',
            'tags' => '["复古","像素","经典"]'
        ],
        [
            'name' => '茉莉柚茶 中世纪史诗',
            'slug' => 'qwq-medieval',
            'description' => '<p>穿越到中世纪的史诗世界！茉莉柚茶 中世纪史诗材质包将 Minecraft 的每一个角落都打造成古老的城堡与村庄。石头城墙、木质要塞、石板道路，每一个细节都透露着中世纪的气息。</p><p>配合全套中世纪风格建筑方块和装饰品，让您能够建造出真正令人惊叹的史诗级建筑。</p>',
            'short_description' => '打造属于你的中世纪史诗王国',
            'version' => '1.21',
            'author' => '茉莉柚茶 Team',
            'release_date' => '2024-09-10',
            'buy_url' => 'https://example.com/buy/medieval',
            'features' => '["中世纪建筑纹理","城堡装饰方块","骑士装备模型","古旧材质效果","旗帜与徽章系统"]',
            'images' => '[]',
            'videos' => '[]',
            'tags' => '["中世纪","史诗","建筑"]'
        ]
    ];
    
    $stmt = $db->prepare("INSERT INTO texture_packs (name, slug, description, short_description, cover_image, version, author, release_date, buy_url, features, images, videos, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($samples as $s) {
        $stmt->execute([
            $s['name'], $s['slug'], $s['description'], $s['short_description'], '',
            $s['version'], $s['author'], $s['release_date'], $s['buy_url'],
            $s['features'], $s['images'], $s['videos'], $s['tags']
        ]);
    }
}

$count = $db->query("SELECT COUNT(*) FROM texture_packs")->fetchColumn();
$adminCount = $db->query("SELECT COUNT(*) FROM admins")->fetchColumn();

echo "<!DOCTYPE html><html lang='zh-CN'><head><meta charset='UTF-8'><title>数据库初始化完成</title>";
echo "<style>
body{font-family:system-ui,sans-serif;background:#f8fafc;color:#0f172a;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
.card{max-width:480px;padding:40px;background:rgba(255,255,255,0.6);backdrop-filter:blur(30px);border:1px solid rgba(0,0,0,0.06);border-radius:20px;text-align:center;}
h1{font-size:1.5rem;margin-bottom:16px;}.badge{display:inline-block;padding:4px 12px;border-radius:100px;background:rgba(6,182,212,0.15);color:#06b6d4;font-size:0.85rem;font-weight:600;margin-bottom:12px;}
p{color:#64748b;margin:8px 0;}.btn{display:inline-block;margin-top:20px;padding:12px 28px;border-radius:12px;background:linear-gradient(135deg,#06b6d4,#0891b2);color:#ffffff;text-decoration:none;font-weight:600;}.btn:hover{opacity:0.9;}
</style>";
echo "<div class='card'>";
echo "<div class='badge'>✓ 初始化完成</div>";
echo "<h1>数据库已就绪</h1>";
echo "<p>📦 材质包: <strong>{$count}</strong> 个</p>";
echo "<p>👤 管理员: <strong>{$adminCount}</strong> 个 (admin / admin123)</p>";
echo "<a href='/' class='btn'>前往首页</a>";
echo "</div><script src='/assets/js/liquid-glass.js' defer></script></body></html>";
?>