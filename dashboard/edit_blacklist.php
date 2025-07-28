<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../inc/db.php';
require_once '../inc/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die("无效的黑名单记录ID");

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

// 判断当前用户是否为管理员
$isAdmin = ($_SESSION['is_admin'] ?? 0) == 1;

if ($isAdmin) {
    // 管理员查询时不限制 user_id
    $stmt = $pdo->prepare("
        SELECT b.*, m.user_id, m.id as module_id 
        FROM blacklists b 
        JOIN modules m ON b.module_id = m.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$id]);
} else {
    // 普通用户只允许访问自己模块的数据
    $stmt = $pdo->prepare("
        SELECT b.*, m.user_id, m.id as module_id 
        FROM blacklists b 
        JOIN modules m ON b.module_id = m.id 
        WHERE b.id = ? AND m.user_id = ?
    ");
    $stmt->execute([$id, $user_id]);
}

$record = $stmt->fetch();
if (!$record) die("记录不存在或无权限");

$owner_user_id = $record['user_id'];
$relativeDir = "uploads/user_{$owner_user_id}/module_{$record['module_id']}/blacklist_{$id}/";
$absoluteDir = "../" . $relativeDir;
if (!is_dir($absoluteDir)) mkdir($absoluteDir, 0777, true);

// 获取已有图片文件
$images = glob($absoluteDir . "image*.{jpg,jpeg,png}", GLOB_BRACE);
$images_relative = array_map(function ($img) use ($absoluteDir, $relativeDir) {
    return str_replace($absoluteDir, $relativeDir, $img);
}, $images);

// 处理删除请求
if (isset($_GET['delete_image'])) {
    $img = basename($_GET['delete_image']);
    $imgPath = $absoluteDir . $img;
    if (file_exists($imgPath)) unlink($imgPath);
    header("Location: edit_blacklist.php?id=$id");
    exit();
}

if (isset($_GET['delete_video'])) {
    $videoPath = $absoluteDir . "video.mp4";
    if (file_exists($videoPath)) unlink($videoPath);
    header("Location: edit_blacklist.php?id=$id");
    exit();
}

// 处理提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wx_id = trim($_POST['wx_id']);
    $nickname = trim($_POST['nickname']);
    $reason = trim($_POST['reason']);
    $group_name = trim($_POST['group_name']);
    $blacklist_time = $_POST['blacklist_time'] ?: null;

    // 上传图片（最多5张）
    if (!empty($_FILES['images']['name'][0])) {
        $existingCount = count($images);
        $newFiles = $_FILES['images'];
        for ($i = 0; $i < count($newFiles['name']); $i++) {
            if ($existingCount >= 5) break;

            $tmpName = $newFiles['tmp_name'][$i];
            $ext = strtolower(pathinfo($newFiles['name'][$i], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png']) && $newFiles['size'][$i] <= 5 * 1024 * 1024) {
                $filename = "image" . time() . "_$i.$ext";
                move_uploaded_file($tmpName, $absoluteDir . $filename);
                $existingCount++;
            }
        }
    }

    // 上传视频
    if (!empty($_FILES['video']['name'])) {
        $vid = $_FILES['video'];
        $ext = strtolower(pathinfo($vid['name'], PATHINFO_EXTENSION));
        if ($ext === 'mp4' && $vid['size'] <= 150 * 1024 * 1024) {
            $video_path = $relativeDir . "video.mp4";
            move_uploaded_file($vid['tmp_name'], $absoluteDir . "video.mp4");
        }
    }

    // 更新数据（图片和视频路径不用单独存储，因路径可动态生成）
    $stmt = $pdo->prepare("
        UPDATE blacklists 
        SET wx_id = ?, nickname = ?, reason = ?, group_name = ?, blacklist_time = ? 
        WHERE id = ?
    ");
    $stmt->execute([
        $wx_id, $nickname, $reason, $group_name, $blacklist_time, $id
    ]);

    header("Location: blacklists.php?module_id=" . $record['module_id']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8" />
    <title>编辑黑户信息</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body { margin: 0; padding: 20px; background: linear-gradient(135deg, #ffdde1, #ee9ca7); font-family: "Segoe UI", "Microsoft YaHei", sans-serif; color: #333; }
        .container { background: rgba(255,255,255,0.85); border-radius: 16px; max-width: 700px; margin: auto; padding: 20px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-top: 12px; font-weight: bold; }
        input[type=text], input[type=datetime-local], textarea { width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc; margin-top: 6px; box-sizing: border-box; font-size: 14px; }
        textarea { resize: vertical; min-height: 80px; }
        input[type=file] { margin-top: 6px; }
        button { margin-top: 20px; width: 100%; padding: 12px; font-size: 16px; background: #ee9ca7; border: none; border-radius: 10px; cursor: pointer; color: white; font-weight: bold; transition: background 0.3s ease; }
        button:hover { background: #ffdde1; color: #333; }
        .preview-img, .preview-video { margin-top: 10px; max-width: 100%; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .img-box { position: relative; display: inline-block; margin: 10px 10px 0 0; }
        .img-box img { max-height: 100px; border-radius: 8px; }
        .img-box a { position: absolute; top: 0; right: 0; background: red; color: white; padding: 2px 6px; font-size: 12px; text-decoration: none; border-radius: 0 8px 0 8px; }
        a.back-link { display: inline-block; margin-top: 20px; text-align: center; text-decoration: none; color: #555; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <h2>编辑黑户信息</h2>
    <form method="post" enctype="multipart/form-data">
        <label>黑户原始ID</label>
        <input type="text" name="wx_id" required value="<?=htmlspecialchars($record['wx_id'])?>" />
        <label>黑户昵称</label>
        <input type="text" name="nickname" value="<?=htmlspecialchars($record['nickname'])?>" />
        <label>上报时间</label>
        <input type="datetime-local" name="blacklist_time" value="<?= $record['blacklist_time'] ? date('Y-m-d\TH:i', strtotime($record['blacklist_time'])) : '' ?>" />
        <label>所属群聊</label>
        <input type="text" name="group_name" value="<?=htmlspecialchars($record['group_name'])?>" />
        <label>上报原因</label>
        <textarea name="reason"><?=htmlspecialchars($record['reason'])?></textarea>

        <label>上传图片 (最多5张，每张≤5MB)</label>
        <input type="file" name="images[]" accept="image/jpeg,image/png" multiple />
        <?php foreach ($images_relative as $img): ?>
            <div class="img-box">
                <img src="/<?= htmlspecialchars($img) ?>" />
                <a href="?id=<?=$id?>&delete_image=<?=basename($img)?>" onclick="return confirm('确定要删除这张图片吗？')">X</a>
            </div>
        <?php endforeach; ?>

        <label style="margin-top: 16px;">上传视频 (mp4，≤150MB)</label>
        <input type="file" name="video" accept="video/mp4" />
        <?php if (file_exists($absoluteDir . "video.mp4")): ?>
            <video controls class="preview-video">
                <source src="/<?= $relativeDir . 'video.mp4' ?>" type="video/mp4" />
                您的浏览器不支持视频播放。
            </video>
            <br/>
            <a href="?id=<?=$id?>&delete_video=1" onclick="return confirm('确定要删除这个视频吗？')">删除视频</a>
        <?php endif; ?>

        <button type="submit">保存修改</button>
    </form>
    <a href="blacklists.php?module_id=<?= $record['module_id'] ?>" class="back-link">返回黑户列表</a>
</div>
</body>
</html>