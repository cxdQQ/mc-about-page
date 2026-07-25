<?php
error_reporting(0);
ob_clean();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/functions.php';
    requireLogin();

    $uploadDir = __DIR__ . '/../assets/uploads/';
    $assetsDir = __DIR__ . '/../assets/';

    // 如果 uploads 目录不存在，尝试创建
    if (!is_dir($uploadDir)) {
        // 先检查父目录是否可写
        if (!is_writable($assetsDir)) {
            throw new Exception(
                '目录 ' . $assetsDir . ' 不可写，请执行以下命令：' .
                'sudo mkdir -p ' . $uploadDir . ' && sudo chown -R www:www ' . $assetsDir,
                500
            );
        }
        if (!@mkdir($uploadDir, 0755, true)) {
            $err = error_get_last();
            throw new Exception(
                '创建上传目录失败: ' . ($err['message'] ?? '未知错误') .
                '，请手动执行: mkdir -p ' . $uploadDir . ' && chmod 755 ' . $uploadDir,
                500
            );
        }
    }

    // 检查目录是否可写
    if (!is_writable($uploadDir)) {
        throw new Exception(
            '上传目录不可写，请执行: chmod 777 ' . $uploadDir,
            500
        );
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('不支持的请求方法', 405);
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = '文件上传失败';
        if (isset($_FILES['file'])) {
            switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMsg = '文件大小超过服务器限制';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMsg = '没有选择文件';
                    break;
            }
        }
        throw new Exception($errorMsg, 400);
    }

    $file = $_FILES['file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
    $maxSize = 50 * 1024 * 1024; // 50MB

    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('不支持的文件类型。支持: JPG, PNG, GIF, WebP, MP4, WebM', 400);
    }

    if ($file['size'] > $maxSize) {
        throw new Exception('文件大小超过限制 (50MB)', 400);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('upload_') . '.' . $ext;
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new Exception('文件保存失败', 500);
    }

    $url = '/assets/uploads/' . $filename;
    echo json_encode([
        'success' => true,
        'message' => '上传成功',
        'data' => [
            'url' => $url,
            'filename' => $filename,
            'type' => $file['type'],
            'size' => $file['size']
        ]
    ]);
    exit;

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}