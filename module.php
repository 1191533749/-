<?php
require_once 'inc/db.php';
session_start();

$stmt = $pdo->query("SELECT * FROM modules ORDER BY created_at DESC");
$modules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <title>模块列表</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body {margin:0;padding:0;background:linear-gradient(135deg,#a8edea,#fed6e3);font-family:"Segoe UI","Microsoft YaHei",sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;}
    .container{background:rgba(255,255,255,0.2);backdrop-filter:blur(15px);border-radius:20px;padding:30px;width:95%;max-width:800px;box-shadow:0 8px 32px rgba(0,0,0,0.25);}
    h2{text-align:center;color:#333;margin-bottom:20px;}
    .top-actions{text-align:center;margin-bottom:15px;}
    .top-actions a{margin:0 5px;padding:8px 14px;background:#28a745;color:#fff;text-decoration:none;border-radius:6px;}
    table{width:100%;border-collapse:collapse;background:rgba(255,255,255,0.6);color:#333;border-radius:12px;overflow:hidden;}
    th,td{padding:12px;text-align:center;border-bottom:1px solid #ddd;}
    th{background:rgba(255,255,255,0.9);}
    tr:hover{background:rgba(255,255,255,0.3);}
    .btn-view{padding:6px 12px;background:#007bff;color:#fff;text-decoration:none;border-radius:6px;transition:.3s;}
    .btn-delete{background:#dc3545;margin-left:8px;}
    .btn-view:hover{background:#0056b3;}
    .btn-delete:hover{background:#c82333;}
  </style>
</head>
<body>
  <div class="container">
    <h2>📦 所有模块</h2>
    <div class="top-actions">
      <a href="dashboard/add_module.php">➕ 添加模块</a>
    </div>
    <table>
      <thead>
        <tr><th>ID</th><th>模块名称</th><th>创建时间</th><th>操作</th></tr>
      </thead>
      <tbody>
        <?php foreach ($modules as $m): ?>
        <tr>
          <td><?= $m['id'] ?></td>
          <td><?= htmlspecialchars($m['module_name']) ?></td>
          <td><?= $m['created_at'] ?></td>
          <td>
            <a class="btn-view" href="dashboard/blacklists.php?module_id=<?= $m['id'] ?>">查看黑名单</a>
            <a class="btn-view btn-delete" href="dashboard/delete_module.php?id=<?= $m['id'] ?>" onclick="return confirm('确定删除此模块？')">删除</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>