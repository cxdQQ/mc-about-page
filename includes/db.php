<?php
require_once __DIR__ . '/config.php';

function getDb(): PDO {
    static $db = null;
    
    if ($db === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        
        try {
            $db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            // 记录错误但不暴露敏感信息
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '数据库连接失败']);
            } else {
                echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>错误</title>';
                echo '<style>body{font-family:system-ui,sans-serif;background:#f8fafc;color:#0f172a;display:flex;align-items:center;justify-content:center;min-height:100vh;}</style>';
                echo '</head><body><div style="text-align:center;padding:40px;">';
                echo '<h1>数据库连接失败</h1>';
                echo '<p style="color:#94a3b8;">请检查数据库配置和连接状态</p>';
                echo '<a href="/" style="display:inline-block;margin-top:20px;padding:12px 28px;border-radius:12px;background:linear-gradient(135deg,#06b6d4,#0891b2);color:#ffffff;text-decoration:none;font-weight:600;">返回首页</a>';
                echo '</div></body></html>';
            }
            exit;
        }
    }
    
    return $db;
}
?>