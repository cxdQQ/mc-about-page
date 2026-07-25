<?php
/**
 * 茉莉柚茶 材质包 - 可视化安装向导
 * 
 * 访问此页面进行 MySQL 数据库配置和初始化安装
 * 安装完成后请删除此文件以保证安全
 */

session_start();

// 检测是否已安装
$configFile = __DIR__ . '/includes/config.php';
$lockFile = __DIR__ . '/includes/installed.lock';
$force = isset($_GET['force']) || isset($_POST['force']);
$alreadyInstalled = file_exists($lockFile) && !$force;

// 如果是强制重装，删除锁文件
if ($force && file_exists($lockFile)) {
    @unlink($lockFile);
    @unlink($configFile);
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'test_connection') {
        $host = $_POST['db_host'] ?? 'localhost';
        $port = $_POST['db_port'] ?? '3306';
        $dbname = $_POST['db_name'] ?? 'qwq_textures';
        $user = $_POST['db_user'] ?? 'root';
        $pass = $_POST['db_pass'] ?? '';
        
        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            
            // 测试是否能创建数据库
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            $success = '数据库连接成功！数据库已创建或已存在。';
            $_SESSION['install_db'] = compact('host', 'port', 'dbname', 'user', 'pass');
            $step = 2;
        } catch (PDOException $e) {
            $error = '连接失败：' . $e->getMessage();
        }
    }
    
    if ($action === 'create_tables') {
        if (!isset($_SESSION['install_db'])) {
            $error = '请先完成数据库连接配置';
            $step = 1;
        } else {
            $db = $_SESSION['install_db'];
            try {
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset=utf8mb4";
                $pdo = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                
                $pdo->exec("
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
                
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS admins (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(100) NOT NULL UNIQUE,
                        password_hash VARCHAR(255) NOT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                $success = '数据表创建成功！';
                $step = 3;
            } catch (PDOException $e) {
                $error = '建表失败：' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'setup_admin') {
        if (!isset($_SESSION['install_db'])) {
            $error = '请先完成数据库配置';
            $step = 1;
        } else {
            $db = $_SESSION['install_db'];
            $adminUser = $_POST['admin_user'] ?? 'admin';
            $adminPass = $_POST['admin_pass'] ?? '';
            $adminPass2 = $_POST['admin_pass2'] ?? '';
            
            if (strlen($adminPass) < 6) {
                $error = '密码长度不能少于6位';
            } elseif ($adminPass !== $adminPass2) {
                $error = '两次密码输入不一致';
            } else {
                try {
                    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    ]);
                    
                    // 清空已有管理员
                    $pdo->exec("DELETE FROM admins");
                    
                    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
                    $stmt->execute([$adminUser, $hash]);
                    
                    $success = '管理员账号创建成功！';
                    $_SESSION['install_admin'] = $adminUser;
                    $step = 4;
                } catch (PDOException $e) {
                    $error = '创建管理员失败：' . $e->getMessage();
                }
            }
        }
    }
    
    if ($action === 'seed_data') {
        if (!isset($_SESSION['install_db'])) {
            $error = '请先完成数据库配置';
            $step = 1;
        } else {
            $db = $_SESSION['install_db'];
            $seed = isset($_POST['seed_data']) && $_POST['seed_data'] === 'yes';
            
            try {
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset=utf8mb4";
                $pdo = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                
                if ($seed) {
                    // 清空已有数据
                    $pdo->exec("DELETE FROM texture_packs");
                    
                    $samples = [
                        ['茉莉柚茶 自然光影', 'qwq-nature-light', '<p>茉莉柚茶 自然光影材质包为 Minecraft 带来最真实的光影体验。通过精心调整的光照算法和色彩映射，让每一个方块都呈现出自然界的真实质感。</p>', '为 Minecraft 带来最真实的光影体验', '1.21', '茉莉柚茶 Team', '2024-12-01', 'https://example.com/buy/nature-light', '["真实光影追踪","动态天气系统","水面反射效果","柔和阴影渲染","自定义云朵纹理"]', '[]', '[]', '["光影","自然","真实"]'],
                        ['茉莉柚茶 幻想世界', 'qwq-fantasy', '<p>进入 茉莉柚茶 幻想世界材质包，探索一个充满魔法与奇迹的 Minecraft 世界。</p>', '充满魔法与奇迹的幻想风格材质包', '1.21', '茉莉柚茶 Team', '2024-11-15', 'https://example.com/buy/fantasy', '["梦幻色彩系统","发光粒子效果","魔法生物纹理","独特方块设计","星空背景"]', '[]', '[]', '["幻想","魔法","色彩"]'],
                        ['茉莉柚茶 像素复古', 'qwq-pixel-retro', '<p>致敬经典！茉莉柚茶 像素复古材质包将 Minecraft 带回最原始的像素风格。</p>', '致敬经典的像素复古风格', '1.20', '茉莉柚茶 Team', '2024-10-20', 'https://example.com/buy/pixel-retro', '["16x16 经典像素","怀旧色彩方案","复古GUI界面","原版风格增强","低分辨率纹理"]', '[]', '[]', '["复古","像素","经典"]'],
                        ['茉莉柚茶 中世纪史诗', 'qwq-medieval', '<p>穿越到中世纪的史诗世界！茉莉柚茶 中世纪史诗材质包将 Minecraft 的每一个角落都打造成古老的城堡与村庄。</p>', '打造属于你的中世纪史诗王国', '1.21', '茉莉柚茶 Team', '2024-09-10', 'https://example.com/buy/medieval', '["中世纪建筑纹理","城堡装饰方块","骑士装备模型","古旧材质效果","旗帜与徽章系统"]', '[]', '[]', '["中世纪","史诗","建筑"]'],
                    ];
                    
                    $stmt = $pdo->prepare("INSERT INTO texture_packs (name, slug, description, short_description, version, author, release_date, buy_url, features, images, videos, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    foreach ($samples as $s) {
                        $stmt->execute($s);
                    }
                    
                    $success = '示例数据导入成功！共 ' . count($samples) . ' 个材质包。';
                } else {
                    $success = '已跳过示例数据导入。';
                }
                
                $step = 5;
            } catch (PDOException $e) {
                $error = '导入数据失败：' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'complete') {
        // 写入 config.php
        if (!isset($_SESSION['install_db'])) {
            $error = '请先完成数据库配置';
            $step = 1;
        } else {
            $db = $_SESSION['install_db'];
            $configContent = <<<PHP
<?php
/**
 * 数据库配置文件
 * 由安装向导自动生成，请勿手动修改
 */

define('DB_HOST', '{$db['host']}');
define('DB_PORT', '{$db['port']}');
define('DB_NAME', '{$db['dbname']}');
define('DB_USER', '{$db['user']}');
define('DB_PASS', '{$db['pass']}');
define('DB_CHARSET', 'utf8mb4');
PHP;
            
            if (file_put_contents($configFile, $configContent)) {
                // 创建上传目录
                $uploadDir = __DIR__ . '/assets/uploads/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                
                // 生成安装锁文件
                file_put_contents(__DIR__ . '/includes/installed.lock', date('Y-m-d H:i:s'));
                
                // 保存信息到 cookie 以便完成页显示
                $installHost = $db['host'];
                $installDbName = $db['dbname'];
                $installAdmin = $_SESSION['install_admin'] ?? 'admin';
                
                // 清理 session
                unset($_SESSION['install_db']);
                unset($_SESSION['install_admin']);
                
                $success = '安装完成！';
                $step = 6;
            } else {
                $error = '无法写入配置文件，请检查目录权限。';
            }
        }
    }
}

// 检测步骤 CSS 样式
$stepLabels = ['连接数据库', '创建数据表', '设置管理员', '导入数据', '完成安装'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>茉莉柚茶 安装向导</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        body::before {
            content: '';
            position: fixed;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(ellipse 80% 60% at 20% 20%, rgba(6, 182, 212, 0.03) 0%, transparent 60%),
                        radial-gradient(ellipse 60% 50% at 80% 80%, rgba(6, 182, 212, 0.02) 0%, transparent 50%);
            pointer-events: none; z-index: 0;
        }
        .container { 
            width: 100%; max-width: 640px; 
            position: relative; z-index: 1;
        }
        .card {
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        }
        .logo {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-bottom: 24px;
        }
        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 20px; color: #ffffff;
        }
        .logo-text { font-size: 1.5rem; font-weight: 800; }
        h1 { text-align: center; font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; }
        .subtitle { text-align: center; color: #94a3b8; font-size: 0.9rem; margin-bottom: 32px; }

        /* Steps Progress */
        .steps { display: flex; justify-content: center; gap: 4px; margin-bottom: 32px; }
        .step-dot {
            width: 32px; height: 4px; border-radius: 2px;
            background: rgba(255,255,255,0.1); transition: all 0.3s;
        }
        .step-dot.active { background: #06b6d4; }
        .step-dot.done { background: rgba(6,182,212,0.4); }

        /* Form */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #94a3b8; margin-bottom: 6px; }
        .form-input {
            width: 100%; padding: 10px 14px;
            background: rgba(255,255,255,0.8);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 10px; color: #0f172a; font-size: 0.9rem;
            font-family: inherit; outline: none; transition: all 0.3s;
        }
        .form-input:focus { border-color: #06b6d4; box-shadow: 0 0 0 3px rgba(6,182,212,0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-hint { color: #64748b; font-size: 0.8rem; margin-top: 4px; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 28px; border-radius: 12px; font-size: 0.95rem;
            font-weight: 600; text-decoration: none; cursor: pointer;
            border: none; transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: #ffffff; box-shadow: 0 4px 20px rgba(6,182,212,0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(6,182,212,0.4); }
        .btn-secondary {
            background: rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.08);
            color: #0f172a;
        }
        .btn-secondary:hover { background: rgba(0,0,0,0.08); }
        .btn-block { width: 100%; justify-content: center; padding: 14px; }
        .btn-group { display: flex; gap: 12px; margin-top: 24px; }
        .btn-group .btn { flex: 1; justify-content: center; }

        /* Alert */
        .alert {
            padding: 14px 18px; border-radius: 12px; font-size: 0.9rem;
            margin-bottom: 20px; display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #fca5a5; }
        .alert-success { background: rgba(6,182,212,0.1); border: 1px solid rgba(6,182,212,0.2); color: #06b6d4; }

        /* Success Page */
        .success-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(6,182,212,0.15); display: flex;
            align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 32px;
        }
        .info-list { 
            background: rgba(255,255,255,0.6); border-radius: 12px;
            padding: 16px 20px; margin: 16px 0;
        }
        .info-list li { 
            list-style: none; padding: 8px 0; 
            border-bottom: 1px solid rgba(0,0,0,0.04);
            display: flex; justify-content: space-between; font-size: 0.9rem;
        }
        .info-list li:last-child { border-bottom: none; }
        .info-list .label { color: #64748b; }
        .info-list .value { color: #0f172a; font-weight: 500; }

        @media (max-width: 480px) {
            .card { padding: 24px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <span class="logo-icon">茉</span>
                <span class="logo-text">茉莉柚茶 安装向导</span>
            </div>

            <?php if ($alreadyInstalled && !isset($_GET['force'])): ?>
                <h1>已安装</h1>
                <p class="subtitle">系统似乎已完成安装。如需重新安装，请删除 <code>includes/installed.lock</code> 文件。</p>
                <div class="btn-group">
                    <a href="/" class="btn btn-primary btn-block">前往首页</a>
                    <a href="?force=1" class="btn btn-secondary btn-block">强制重新安装</a>
                </div>

            <?php elseif ($step <= 5): ?>
                <!-- Step Progress -->
                <div class="steps">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <div class="step-dot <?= $i < $step ? 'done' : ($i === $step ? 'active' : '') ?>"></div>
                    <?php endfor; ?>
                </div>

                <h1>第 <?= $step ?> 步：<?= $stepLabels[$step - 1] ?></h1>
                <p class="subtitle"><?= $step === 1 ? '请输入 MySQL 数据库连接信息' : ($step === 2 ? '将在数据库中创建所需的数据表' : ($step === 3 ? '设置后台管理员账号和密码' : ($step === 4 ? '可选导入示例材质包数据' : '即将完成安装'))) ?></p>

                <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Step 1: Database Connection -->
                <?php if ($step === 1): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="test_connection">
                    <div class="form-row">
                        <div class="form-group">
                            <label>数据库主机</label>
                            <input type="text" name="db_host" class="form-input" value="localhost" required>
                        </div>
                        <div class="form-group">
                            <label>端口</label>
                            <input type="text" name="db_port" class="form-input" value="3306" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>数据库名称</label>
                        <input type="text" name="db_name" class="form-input" value="qwq_textures" required>
                        <div class="form-hint">如不存在将自动创建</div>
                    </div>
                    <div class="form-group">
                        <label>数据库用户名</label>
                        <input type="text" name="db_user" class="form-input" value="root" required>
                    </div>
                    <div class="form-group">
                        <label>数据库密码</label>
                        <input type="password" name="db_pass" class="form-input" placeholder="如无密码留空">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">测试连接并继续</button>
                </form>

                <!-- Step 2: Create Tables -->
                <?php elseif ($step === 2): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="create_tables">
                    <p style="color:#94a3b8;font-size:0.9rem;line-height:1.6;margin-bottom:16px;">
                        即将在数据库 <strong style="color:#0f172a;"><?= htmlspecialchars($_SESSION['install_db']['dbname'] ?? '') ?></strong> 中创建以下数据表：
                    </p>
                    <div class="info-list">
                        <ul style="list-style:none;">
                            <li style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.05);display:flex;align-items:center;gap:8px;">
                                <span style="width:8px;height:8px;border-radius:50%;background:#06b6d4;display:inline-block;"></span>
                                <code style="color:#0f172a;">texture_packs</code> 
                                <span style="color:#64748b;font-size:0.85rem;">— 材质包数据</span>
                            </li>
                            <li style="padding:8px 0;display:flex;align-items:center;gap:8px;">
                                <span style="width:8px;height:8px;border-radius:50%;background:#06b6d4;display:inline-block;"></span>
                                <code style="color:#0f172a;">admins</code>
                                <span style="color:#64748b;font-size:0.85rem;">— 管理员账号</span>
                            </li>
                        </ul>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">创建数据表</button>
                </form>

                <!-- Step 3: Admin Account -->
                <?php elseif ($step === 3): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="setup_admin">
                    <div class="form-group">
                        <label>管理员用户名</label>
                        <input type="text" name="admin_user" class="form-input" value="admin" required>
                    </div>
                    <div class="form-group">
                        <label>密码</label>
                        <input type="password" name="admin_pass" class="form-input" placeholder="至少6位字符" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>确认密码</label>
                        <input type="password" name="admin_pass2" class="form-input" placeholder="再次输入密码" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">创建管理员</button>
                </form>

                <!-- Step 4: Seed Data -->
                <?php elseif ($step === 4): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="seed_data">
                    <p style="color:#94a3b8;font-size:0.9rem;line-height:1.6;margin-bottom:16px;">
                        是否导入示例材质包数据？包含 4 款材质包的介绍内容，方便你快速体验系统功能。
                    </p>
                    <div class="btn-group">
                        <button type="submit" name="seed_data" value="yes" class="btn btn-primary">导入示例数据</button>
                        <button type="submit" name="seed_data" value="no" class="btn btn-secondary">跳过</button>
                    </div>
                </form>

                <!-- Step 5: Complete -->
                <?php elseif ($step === 5): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="complete">
                    <p style="color:#94a3b8;font-size:0.9rem;line-height:1.6;margin-bottom:16px;">
                        点击完成按钮，系统将自动写入数据库配置文件，之后你就可以开始使用了。
                    </p>
                    <button type="submit" class="btn btn-primary btn-block">完成安装</button>
                </form>
                <?php endif; ?>

            <?php elseif ($step === 6): ?>
                <!-- Success -->
                <div class="success-icon">✓</div>
                <h1>安装完成！</h1>
                <p class="subtitle">茉莉柚茶 材质包系统已成功安装并配置完毕</p>
                <div class="info-list">
                    <ul>
                        <li><span class="label">数据库主机</span><span class="value"><?= htmlspecialchars($installHost ?? 'localhost') ?></span></li>
                        <li><span class="label">数据库名称</span><span class="value"><?= htmlspecialchars($installDbName ?? 'qwq_textures') ?></span></li>
                        <li><span class="label">管理员账号</span><span class="value"><?= htmlspecialchars($installAdmin ?? 'admin') ?></span></li>
                    </ul>
                </div>
                <div class="btn-group">
                    <a href="/" class="btn btn-primary btn-block">前往首页</a>
                    <a href="/admin/login.php" class="btn btn-secondary btn-block">登录后台</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>