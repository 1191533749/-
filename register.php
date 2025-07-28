<?php
require_once 'inc/db.php';

$message = '';

// 定义禁止注册的保留用户名（全部小写）
$reservedUsernames = ['admin', 'root', 'superuser', 'administrator'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($username && $password) {
        // 检查用户名是否在保留名单中
        if (in_array(strtolower($username), $reservedUsernames, true)) {
            $message = "用户名已被禁止使用";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);

            if ($stmt->rowCount() > 0) {
                $message = "用户名已存在";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $hashedPassword, $email])) {
                    header("Location: login.php");
                    exit;
                } else {
                    $message = "注册失败，请重试";
                }
            }
        }
    } else {
        $message = "用户名和密码不能为空";
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>注册</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at top left, #fcb69f, #ffdde1, #fbc2eb);
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }

        .register-box {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 18px;
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: dropIn 1s ease forwards;
            color: #fff;
            text-align: center;
        }

        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            font-size: 26px;
            margin-bottom: 25px;
            color: #fff;
        }

        .register-box input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.75);
            font-size: 16px;
            transition: 0.3s;
        }

        .register-box input:focus {
            outline: none;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
            background-color: rgba(255, 255, 255, 0.9);
        }

        .register-box button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            background: linear-gradient(to right, #ff5e62, #ff9966);
            cursor: pointer;
            transition: 0.3s;
        }

        .register-box button:hover {
            background: linear-gradient(to right, #ff3c41, #ff803d);
        }

        .message {
            color: yellow;
            margin-bottom: 10px;
            font-weight: bold;
        }

        a {
            color: #fff;
            text-decoration: underline;
        }

        a:hover {
            color: #ffefef;
        }

        @media (max-width: 500px) {
            .register-box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>🎀 注册账号</h2>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="请输入用户名" required>
            <input type="password" name="password" placeholder="请输入密码" required>
            <input type="email" name="email" placeholder="请输入邮箱（可选）">
            <button type="submit">立即注册</button>
        </form>
        <p>已有账号？<a href="login.php">点我登录</a></p>
    </div>
</body>
</html>