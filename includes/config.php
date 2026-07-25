<?php
/**
 * 数据库配置文件
 * 
 * 在使用前，请根据实际环境修改以下配置：
 *   1. 创建 MySQL 数据库: mysql -u root -p < setup.sql
 *   2. 或手动创建数据库: CREATE DATABASE qwq_textures DEFAULT CHARACTER SET utf8mb4;
 *   3. 修改下方 DB_HOST / DB_NAME / DB_USER / DB_PASS 为实际值
 *   4. 访问 http://your-domain/init_db.php 初始化管理员账号和示例数据
 *   5. 删除 init_db.php 文件以保障安全
 */

// 数据库主机 (通常为 localhost)
define('DB_HOST', 'localhost');

// 数据库端口 (MySQL 默认 3306)
define('DB_PORT', '3306');

// 数据库名称 (需提前创建)
define('DB_NAME', 'qwq_textures');

// 数据库用户名
define('DB_USER', 'root');

// 数据库密码
define('DB_PASS', '');

// 数据库字符集
define('DB_CHARSET', 'utf8mb4');
?>