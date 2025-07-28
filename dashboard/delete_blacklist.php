<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT b.*, m.user_id, m.id as module_id 
    FROM blacklists b 
    JOIN modules m ON b.module_id = m.id 
    WHERE b.id = ? AND m.user_id = ?
");
$stmt->execute([$id, $user_id]);
$record = $stmt->fetch();
if (!$record) die("记录不存在或无权限");

// 删除文件夹
$folder = "../uploads/user_{$user_id}/module_{$record['module_id']}/blacklist_{$id}";
if (is_dir($folder)) {
    $files = glob("$folder/*");
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
    rmdir($folder);
}

// 删除记录
$stmt = $pdo->prepare("DELETE FROM blacklists WHERE id = ?");
$stmt->execute([$id]);

header("Location: blacklists.php?module_id=" . $record['module_id']);
exit();
?>