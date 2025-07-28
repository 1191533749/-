<?php
session_start();
require_once '../inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "新密码与确认密码不一致";
    } else {
        // 获取管理员信息
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password'])) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$hashed, $_SESSION['user_id']]);
            $success = "密码修改成功！";
        } else {
            $error = "当前密码不正确";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>修改密码 - 管理后台</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: linear-gradient(135deg, #ffdde1, #ee9ca7);
            font-family: "Segoe UI", "Microsoft YaHei", sans-serif;
            color: #333;
            padding: 30px;
            margin: 0;
        }
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 8px 14px;
            border-radius: 10px;
            color: #e91e63;
            text-decoration: none;
            font-weight: bold;
            border: 1px solid rgba(255,255,255,0.3);
            transition: background 0.3s;
        }
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.6);
        }
        .container {
            max-width: 500px;
            background: rgba(255,255,255,0.9);
            padding: 30px;
            border-radius: 16px;
            margin: 60px auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            position: relative;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 16px;
            background-color: #ee9ca7;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
        }
        .message {
            margin-top: 10px;
            color: red;
            text-align: center;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>

<a href="index.php" class="back-btn">返回后台</a>

<div class="container">
    <h2>修改密码</h2>
    <form method="post">
        <label>当前密码：</label>
        <input type="password" name="current_password" required>

        <label>新密码：</label>
        <input type="password" name="new_password" required>

        <label>确认新密码：</label>
        <input type="password" name="confirm_password" required>

        <input type="submit" value="修改密码">
    </form>

    <?php if ($error): ?>
        <div class="message"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
</div>
</body>
</html>