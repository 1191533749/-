<?php
session_start();
require_once '../inc/db.php';

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    http_response_code(403);
    echo json_encode(['error' => '未登录']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$type = $_POST['type'] ?? '';
$index = $_POST['index'] ?? 0;

if (!in_array($type, ['image', 'video']) || $id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => '参数错误']);
    exit;
}

// 获取模块 ID
$stmt = $pdo->prepare("
    SELECT m.id as module_id 
    FROM blacklists b 
    JOIN modules m ON b.module_id = m.id 
    WHERE b.id = ? AND m.user_id = ?
");
$stmt->execute([$id, $user_id]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(403);
    echo json_encode(['error' => '无权限']);
    exit;
}

// 构建保存路径
$relativeDir = "uploads/user_$user_id/module_{$row['module_id']}/blacklist_$id/";
$absoluteDir = dirname(__DIR__) . '/' . $relativeDir;

if (!is_dir($absoluteDir)) mkdir($absoluteDir, 0777, true);

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => '未上传文件']);
    exit;
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// 验证文件类型和大小
if ($type === 'image') {
    if (!in_array($ext, ['jpg', 'jpeg', 'png']) || $file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['error' => '图片格式不正确或超过5MB']);
        exit;
    }
    $filename = "image_" . time() . "_$index.$ext";
} else {
    if ($ext !== 'mp4' || $file['size'] > 150 * 1024 * 1024) {
        echo json_encode(['error' => '视频格式不正确或超过150MB']);
        exit;
    }
    $filename = "video.mp4"; // 每条仅允许上传一个视频
}

// 保存文件
$savePath = $absoluteDir . $filename;
if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    echo json_encode(['error' => '保存失败']);
    exit;
}

// 返回结果
echo json_encode([
    'success' => true,
    'filename' => $filename,
    'url' => '/' . $relativeDir . $filename
]);