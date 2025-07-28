<?php
session_start();
require_once '../inc/auth.php';
require_once '../inc/db.php';

if (!($_SESSION['is_admin'] ?? false)) {
    die('无权限操作');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('无效用户ID');
}

if ($id === $_SESSION['user_id']) {
    die('不能删除当前登录用户');
}

// 找出该用户所有模块id
$stmt = $pdo->prepare("SELECT id FROM modules WHERE user_id = ?");
$stmt->execute([$id]);
$moduleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($moduleIds) {
    $placeholders = implode(',', array_fill(0, count($moduleIds), '?'));
    // 删除黑名单数据
    $stmt = $pdo->prepare("DELETE FROM blacklists WHERE module_id IN ($placeholders)");
    $stmt->execute($moduleIds);

    // 删除模块
    $stmt = $pdo->prepare("DELETE FROM modules WHERE user_id = ?");
    $stmt->execute([$id]);
}

// 删除用户上传的资源目录：uploads/user_X/
$userFolder = realpath(__DIR__ . '/../uploads/user_' . $id);
if ($userFolder && is_dir($userFolder)) {
    function deleteDir($dirPath) {
        foreach (scandir($dirPath) as $item) {
            if ($item == '.' || $item == '..') continue;
            $itemPath = $dirPath . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                deleteDir($itemPath);
            } else {
                @unlink($itemPath);
            }
        }
        @rmdir($dirPath);
    }
    deleteDir($userFolder);
}

// 删除用户
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header('Location: users.php');
exit;