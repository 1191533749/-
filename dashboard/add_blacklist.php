<?php
require_once '../inc/db.php';
session_start();

$mid = (int)($_GET['module_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
$stmt->execute([$mid]);
$mod = $stmt->fetch();
if (!$mod) die("模块ID无效");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wx_id = trim($_POST['wx_id'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $blacklist_time = $_POST['blacklist_time'] ?? null;
    $group_name = trim($_POST['group_name'] ?? '');

    if ($wx_id) {
        $pdo->prepare("INSERT INTO blacklists (module_id, wx_id, nickname, reason, blacklist_time, group_name, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, NOW())")
            ->execute([$mid, $wx_id, $nickname, $reason, $blacklist_time ?: null, $group_name]);
        header("Location: blacklists.php?module_id=$mid");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <title>添加黑户名单</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(135deg, #ffdde1, #ee9ca7);
      padding: 20px;
      color: #333;
    }
    .container {
      max-width: 600px;
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      margin: auto;
      box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    h2 {
      margin-bottom: 20px;
      text-align: center;
      color: #d6336c;
    }
    input, textarea {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
    }
    button {
      background-color: #e75480;
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 8px;
      width: 100%;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover {
      background-color: #d6336c;
    }
    a {
      display: block;
      margin-top: 20px;
      text-align: center;
      color: #666;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>为「<?= htmlspecialchars($mod['module_name']) ?>」添加黑户名单</h2>
    <form method="post">
      <label>黑户微信原始ID（必填）</label>
      <input type="text" name="wx_id" required>

      <label>黑户昵称</label>
      <input type="text" name="nickname">

      <label>上报原因</label>
      <textarea name="reason" rows="3"></textarea>

      <label>处理时间</label>
      <input type="datetime-local" name="blacklist_time">

      <label>群聊昵称</label>
      <input type="text" name="group_name">

      <button type="submit">立即添加</button>
    </form>
    <a href="blacklists.php?module_id=<?= $mid ?>">黑户列表</a>
  </div>
</body>
</html>