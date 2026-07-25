<?php
/**
 * 数据库迁移脚本 - 将 download_url 字段改为 buy_url
 * 访问此文件即可自动迁移
 */
require_once __DIR__ . '/includes/db.php';

try {
    $db = getDb();
    
    // 检查 buy_url 字段是否存在
    $stmt = $db->query("SHOW COLUMNS FROM texture_packs LIKE 'buy_url'");
    $hasBuyUrl = $stmt->fetch();
    
    if (!$hasBuyUrl) {
        // 检查 download_url 字段是否存在
        $stmt = $db->query("SHOW COLUMNS FROM texture_packs LIKE 'download_url'");
        $hasDownloadUrl = $stmt->fetch();
        
        if ($hasDownloadUrl) {
            $db->exec("ALTER TABLE texture_packs CHANGE download_url buy_url VARCHAR(500) DEFAULT ''");
            $msg = '字段 download_url 已成功重命名为 buy_url';
        } else {
            $db->exec("ALTER TABLE texture_packs ADD COLUMN buy_url VARCHAR(500) DEFAULT ''");
            $msg = '已添加 buy_url 字段';
        }
    } else {
        $msg = 'buy_url 字段已存在，无需迁移';
    }
    
    echo "<!DOCTYPE html><html lang='zh-CN'><head><meta charset='UTF-8'><title>迁移完成</title>";
    echo "<style>body{font-family:system-ui,sans-serif;background:#f8fafc;color:#0f172a;display:flex;align-items:center;justify-content:center;min-height:100vh;}</style>";
    echo "</head><body><div style='text-align:center;padding:40px;background:rgba(255,255,255,0.6);border-radius:16px;border:1px solid rgba(0,0,0,0.06);max-width:480px;'>";
    echo "<div style='font-size:48px;margin-bottom:16px;'>✅</div>";
    echo "<h1 style='font-size:1.5rem;margin-bottom:8px;'>迁移成功</h1>";
    echo "<p style='color:#94a3b8;'>" . htmlspecialchars($msg) . "</p>";
    echo "<p style='margin-top:20px;'><a href='/admin/index.php' style='display:inline-block;padding:12px 28px;border-radius:12px;background:linear-gradient(135deg,#06b6d4,#0891b2);color:#ffffff;text-decoration:none;font-weight:600;'>前往后台</a></p>";
    echo "</div></body></html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html><html lang='zh-CN'><head><meta charset='UTF-8'><title>迁移失败</title></head><body>";
    echo "<h1>迁移失败</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</body></html>";
}