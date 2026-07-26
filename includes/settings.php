<?php
/**
 * 网站设置管理
 * 存储背景图片等全局配置
 */
require_once __DIR__ . '/db.php';

function getSettings(): array {
    $db = getDb();
    $settings = [];
    try {
        $stmt = $db->query('SELECT `key`, `value` FROM settings');
        while ($row = $stmt->fetch()) {
            $settings[$row['key']] = $row['value'];
        }
    } catch (PDOException $e) {
        // settings 表可能还不存在
    }
    return $settings;
}

function getSetting(string $key, string $default = ''): string {
    $db = getDb();
    try {
        $stmt = $db->prepare('SELECT `value` FROM settings WHERE `key` = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

function setSetting(string $key, string $value): bool {
    $db = getDb();
    $stmt = $db->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?');
    return $stmt->execute([$key, $value, $value]);
}

/**
 * 输出背景图片 CSS 变量（在 <head> 中调用）
 */
function renderBgStyles(): void {
    $desktop = getSetting('bg_desktop');
    $mobile = getSetting('bg_mobile');
    echo '<style>:root{';
    if ($desktop) {
        echo '--bg-desktop:url(' . htmlspecialchars($desktop) . ');';
    }
    if ($mobile) {
        echo '--bg-mobile:url(' . htmlspecialchars($mobile) . ');';
    }
    echo '}</style>';
}