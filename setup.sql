-- ============================================
-- QWQ 材质包 - MySQL 数据库建表脚本
-- 使用方法: mysql -u root -p < setup.sql
-- 或者登录 MySQL 后: source setup.sql
-- ============================================

CREATE DATABASE IF NOT EXISTS qwq_textures DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qwq_textures;

-- 材质包表
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 管理员表
-- 网站设置表
CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入默认设置
INSERT IGNORE INTO settings (`key`, `value`) VALUES ('bg_desktop', ''), ('bg_mobile', '');

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入默认管理员 (admin / admin123)
-- 密码 hash 由 PHP 的 password_hash 生成，需运行 init_db.php 来插入
-- 或手动运行: INSERT INTO admins (username, password_hash) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 插入示例数据
INSERT INTO texture_packs (name, slug, description, short_description, version, author, release_date, buy_url, features, images, videos, tags) VALUES
('QWQ 自然光影', 'qwq-nature-light',
 '<p>QWQ 自然光影材质包为 Minecraft 带来最真实的光影体验。通过精心调整的光照算法和色彩映射，让每一个方块都呈现出自然界的真实质感。</p><p>从清晨的第一缕阳光到夜晚的星光点点，每一刻的光影变化都经过精心设计，为您呈现一个栩栩如生的 Minecraft 世界。</p>',
 '为 Minecraft 带来最真实的光影体验',
 '1.21', 'QWQ Team', '2024-12-01', 'https://example.com/buy/nature-light',
 '[\"真实光影追踪\",\"动态天气系统\",\"水面反射效果\",\"柔和阴影渲染\",\"自定义云朵纹理\"]',
 '[]', '[]', '[\"光影\",\"自然\",\"真实\"]'),

('QWQ 幻想世界', 'qwq-fantasy',
 '<p>进入 QWQ 幻想世界材质包，探索一个充满魔法与奇迹的 Minecraft 世界。色彩鲜艳的方块、梦幻的光效和独特的生物纹理，让您仿佛置身于童话故事中。</p><p>每一个细节都散发着奇幻的气息，从发光的蘑菇森林到漂浮的魔法岛屿，带给您前所未有的视觉体验。</p>',
 '充满魔法与奇迹的幻想风格材质包',
 '1.21', 'QWQ Team', '2024-11-15', 'https://example.com/buy/fantasy',
 '[\"梦幻色彩系统\",\"发光粒子效果\",\"魔法生物纹理\",\"独特方块设计\",\"星空背景\"]',
 '[]', '[]', '[\"幻想\",\"魔法\",\"色彩\"]'),

('QWQ 像素复古', 'qwq-pixel-retro',
 '<p>致敬经典！QWQ 像素复古材质包将 Minecraft 带回最原始的像素风格，同时加入现代渲染技术，在保留 nostalgic 情怀的同时提升视觉体验。</p><p>清晰的像素边缘、怀旧的色彩搭配，让您重温第一次踏入 Minecraft 世界时的感动与惊喜。</p>',
 '致敬经典的像素复古风格',
 '1.20', 'QWQ Team', '2024-10-20', 'https://example.com/buy/pixel-retro',
 '[\"16x16 经典像素\",\"怀旧色彩方案\",\"复古GUI界面\",\"原版风格增强\",\"低分辨率纹理\"]',
 '[]', '[]', '[\"复古\",\"像素\",\"经典\"]'),

('QWQ 中世纪史诗', 'qwq-medieval',
 '<p>穿越到中世纪的史诗世界！QWQ 中世纪史诗材质包将 Minecraft 的每一个角落都打造成古老的城堡与村庄。石头城墙、木质要塞、石板道路，每一个细节都透露着中世纪的气息。</p><p>配合全套中世纪风格建筑方块和装饰品，让您能够建造出真正令人惊叹的史诗级建筑。</p>',
 '打造属于你的中世纪史诗王国',
 '1.21', 'QWQ Team', '2024-09-10', 'https://example.com/buy/medieval',
 '[\"中世纪建筑纹理\",\"城堡装饰方块\",\"骑士装备模型\",\"古旧材质效果\",\"旗帜与徽章系统\"]',
 '[]', '[]', '[\"中世纪\",\"史诗\",\"建筑\"]');