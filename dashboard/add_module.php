<?php
require_once '../inc/db.php';
session_start();

// 未登录跳转
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $module_name = trim($_POST['module_name']);
    $user_id = $_SESSION['user_id'];

    if (!empty($module_name)) {
        $stmt = $pdo->prepare("INSERT INTO modules (module_name, user_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$module_name, $user_id]);
        header("Location: modules.php");
        exit;
    } else {
        $error = "模块名称不能为空";
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>创建模块</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #ffdde1, #ee9ca7);
            font-family: "Segoe UI", "Microsoft YaHei", sans-serif;
            color: #333;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .form-box {
            background: rgba(255,255,255,0.25);
            backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #fff;
            color: #ff4081;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #f8f8f8;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        a.back {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="form-box">
    <h2>群主昵称</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="module_name" placeholder="请输入群主昵称" required />
        <button type="submit">提交</button>
    </form>
    <a class="back" href="modules.php">返回模块列表</a>
</div>
</body>
</html>