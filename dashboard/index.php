<?php
require_once '../inc/auth.php';
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>总后台 - 首页</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ffdde1, #ee9ca7);
            font-family: "Segoe UI", "Microsoft YaHei", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px 30px;
            width: 90%;
            max-width: 480px;
            color: #fff;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        }

        h2 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .menu a {
            display: block;
            padding: 15px;
            background: rgba(255, 255, 255, 0.25);
            border-radius: 12px;
            text-decoration: none;
            color: #fff;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: background 0.3s ease, color 0.3s ease;
        }

        .menu a:hover {
            background: rgba(255, 255, 255, 0.6);
            color: #000;
        }

        @media (max-width: 500px) {
            h2 {
                font-size: 20px;
            }

            .menu a {
                font-size: 14px;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🎉 欢迎回来，<?= htmlspecialchars($_SESSION['username'] ?? '用户') ?>！</h2>
        <div class="menu">
            <a href="../index.php">🏠 返回首页</a>
            <a href="change_password.php">🔒 修改密码</a>
            <a href="users.php">👤 用户管理</a>
            <a href="modules.php">📦 模块管理</a>
            <a href="logout.php">🚪 退出登录</a>
        </div>
    </div>
</body>
</html>