<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Handle POST requests (create/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    
    $action = $_POST['action'] ?? '';
    
    // Parse features from textarea (one per line)
    $features = [];
    if (!empty($_POST['features'])) {
        $lines = explode("\n", trim($_POST['features']));
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) $features[] = $line;
        }
    }
    
    // Parse images from textarea (one per line)
    $images = [];
    if (!empty($_POST['images'])) {
        $lines = explode("\n", trim($_POST['images']));
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) $images[] = $line;
        }
    }
    
    // Parse videos from textarea (one per line)
    $videos = [];
    if (!empty($_POST['videos'])) {
        $lines = explode("\n", trim($_POST['videos']));
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) $videos[] = $line;
        }
    }
    
    // Parse tags (comma separated)
    $tags = [];
    if (!empty($_POST['tags'])) {
        $parts = explode(',', $_POST['tags']);
        foreach ($parts as $part) {
            $part = trim($part);
            if (!empty($part)) $tags[] = $part;
        }
    }
    
    $data = [
        'name' => $_POST['name'] ?? '',
        'slug' => ensureUniqueSlug(!empty($_POST['slug']) ? $_POST['slug'] : slugify($_POST['name'] ?? '')),
        'description' => $_POST['description'] ?? '',
        'short_description' => $_POST['short_description'] ?? '',
        'cover_image' => $_POST['cover_image'] ?? '',
        'version' => $_POST['version'] ?? '1.21',
        'author' => $_POST['author'] ?? '茉莉柚茶 Team',
        'release_date' => $_POST['release_date'] ?? date('Y-m-d'),
        'buy_url' => $_POST['buy_url'] ?? '',
        'features' => $features,
        'images' => $images,
        'videos' => $videos,
        'tags' => $tags,
    ];
    
    if ($action === 'create') {
        if (empty($data['name'])) {
            jsonResponse(['success' => false, 'message' => '材质包名称不能为空'], 400);
        }
        $id = createTexture($data);
        header('Location: /admin/index.php?saved=1');
        exit;
    } elseif ($action === 'update') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => '无效的材质包 ID'], 400);
        }
        $data['slug'] = ensureUniqueSlug($data['slug'], $id);
        updateTexture($id, $data);
        header('Location: /admin/index.php?saved=1');
        exit;
    }
    
    jsonResponse(['success' => false, 'message' => '未知操作'], 400);
}

// Handle GET requests (list all or single)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $texture = getTexture((int)$_GET['id']);
        if ($texture) {
            jsonResponse(['success' => true, 'data' => $texture]);
        } else {
            jsonResponse(['success' => false, 'message' => '材质包不存在'], 404);
        }
    } else {
        $textures = getTextures();
        jsonResponse(['success' => true, 'data' => $textures]);
    }
}

jsonResponse(['success' => false, 'message' => '不支持的请求方法'], 405);
?>