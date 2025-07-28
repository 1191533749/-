<?php
require_once '../inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

if ($id > 0) {
    if ($is_admin) {
        $pdo->prepare("DELETE FROM blacklists WHERE module_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM modules WHERE id = ?")->execute([$id]);
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM modules WHERE id = ?");
        $stmt->execute([$id]);
        $mod = $stmt->fetch();
        if ($mod && $mod['user_id'] == $user_id) {
            $pdo->prepare("DELETE FROM blacklists WHERE module_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM modules WHERE id = ?")->execute([$id]);
        }
    }
}

// ✅ 跳转到模块管理页（同目录下）
header("Location: modules.php");
exit;