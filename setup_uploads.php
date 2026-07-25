<?php
/**
 * 快速设置 - 创建上传目录并设置权限
 * 上传到服务器后访问此文件即可
 */
$uploadDir = __DIR__ . '/assets/uploads/';

if (!is_dir($uploadDir)) {
    if (@mkdir($uploadDir, 0755, true)) {
        echo "✅ 上传目录已创建: $uploadDir\n";
    } else {
        echo "❌ 创建失败，请手动执行: mkdir -p $uploadDir\n";
        echo "   chmod -R 755 " . __DIR__ . "/assets/\n";
        exit(1);
    }
} else {
    echo "✅ 上传目录已存在\n";
}

if (is_writable($uploadDir)) {
    echo "✅ 上传目录可写\n";
} else {
    echo "❌ 目录不可写，执行: chmod -R 755 " . $uploadDir . "\n";
    exit(1);
}

echo "\n🎉 设置完成！\n";