<?php
require_once 'inc/db.php';

if (!isset($_GET['module_id']) || !is_numeric($_GET['module_id'])) {
    echo "<script>alert('模块ID无效');location.href='/';</script>";
    exit;
}

$module_id = (int)$_GET['module_id'];

$stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
$stmt->execute([$module_id]);
$module = $stmt->fetch();

if (!$module) {
    echo "<script>alert('模块不存在');location.href='/';</script>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM blacklists WHERE module_id = ? ORDER BY created_at DESC");
$stmt->execute([$module_id]);
$blacklists = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <title>分享模块：<?= htmlspecialchars($module['module_name']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: "Microsoft YaHei", sans-serif;
      margin: 0;
      padding: 20px;
      background: #fdf5f5;
    }
    .container {
      max-width: 960px;
      margin: auto;
      background: #fff;
      border-radius: 10px;
      padding: 24px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #c0392b;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      text-align: center;
    }
    th {
      background-color: #e74c3c;
      color: #fff;
    }
    .note {
      margin-top: 20px;
      font-size: 14px;
      color: #555;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>模块分享：<?= htmlspecialchars($module['module_name']) ?></h2>

    <?php if (empty($blacklists)): ?>
      <p style="text-align:center;">该模块暂无黑名单记录</p>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>微信号</th>
          <th>昵称</th>
          <th>拉黑时间</th>
          <th>群名称</th>
          <th>原因</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($blacklists as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['wx_id']) ?></td>
          <td><?= htmlspecialchars($b['nickname']) ?></td>
          <td><?= $b['blacklist_time'] ?></td>
          <td><?= htmlspecialchars($b['group_name']) ?></td>
          <td><?= nl2br(htmlspecialchars($b['reason'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <div class="note">此为分享页面，信息仅供参考</div>
  </div>
</body>
</html>