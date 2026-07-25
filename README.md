# 茉莉柚茶 - Minecraft 材质包介绍站

一个基于 Glass UI 设计的 Minecraft 材质包展示网站，支持后台管理、文件上传、响应式布局。

## 功能特性

- **材质展示** - 首页网格展示所有材质包，支持封面图、标签、描述
- **详情页面** - 每个材质包有独立的详情页，展示图片、视频、功能介绍
- **后台管理** - 完整的 CRUD 操作，支持创建、编辑、删除材质包
- **文件上传** - 支持图片和视频批量上传，带进度条和本地预览
- **可视化安装** - 内置安装向导，自动配置数据库和管理员账号
- **响应式布局** - 适配桌面端和手机端
- **玻璃态 UI** - 毛玻璃效果设计，青色主题

## 技术栈

- **前端** - HTML5, CSS3 (Glass UI), JavaScript
- **后端** - PHP 8+
- **数据库** - MySQL 5.7+ / MariaDB 10.3+
- **Web 服务器** - Nginx / Apache

## 安装部署

### 1. 上传文件

将项目所有文件上传到服务器 Web 目录。

### 2. 访问安装向导

浏览器访问 `http://你的域名/install.php`，按步骤完成安装：

1. **数据库配置** - 填写 MySQL 数据库连接信息
2. **创建数据表** - 自动创建所需的数据库表
3. **管理员账号** - 设置后台登录用户名和密码
4. **初始数据** - 可选择导入示例材质包

### 3. 完成安装

安装完成后建议删除 `install.php` 文件以确保安全。

## 后台管理

访问 `http://你的域名/admin/login.php` 登录后台。

- **材质列表** - 查看、搜索所有材质包
- **新增材质** - 创建新的材质包
- **编辑材质** - 修改材质名称、描述、图片、视频等
- **删除材质** - 删除不需要的材质包

## 手动配置

如果安装向导无法使用，可手动配置：

### 1. 配置数据库

编辑 `includes/config.php`：

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', '茉莉柚茶_textures');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### 2. 导入数据库

执行 `setup.sql` 或运行 `init_db.php`。

### 3. 设置目录权限

```bash
chmod -R 755 assets/uploads
chown -R www:www assets/uploads
```

## 目录结构

```
├── admin/               # 后台管理页面
│   ├── index.php        # 材质包列表
│   ├── edit.php         # 新增/编辑材质
│   └── login.php        # 登录页
├── api/                 # API 接口
│   ├── auth.php         # 登录验证
│   ├── textures.php     # 材质包数据
│   └── upload.php       # 文件上传
├── assets/              # 静态资源
│   ├── css/style.css    # 样式表
│   ├── js/main.js       # 主脚本
│   └── uploads/         # 上传文件目录
├── includes/            # 核心文件
│   ├── auth.php         # 权限验证
│   ├── config.php       # 数据库配置
│   ├── db.php           # 数据库连接
│   └── functions.php    # 公共函数
├── index.php            # 首页
├── texture.php          # 材质详情页
├── install.php          # 安装向导
└── README.md            # 本文件
```

## 许可证

MIT License