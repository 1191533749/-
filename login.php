<?php
require_once 'inc/db.php';
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard/index.php");
        exit;
    } else {
        $message = "用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>登录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ffafbd, #ffc3a0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(3px);
        }

        .login-box {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 380px;
            color: #fff;
            text-align: center;
            animation: fadeIn 1s ease forwards;
            position: relative;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            margin-bottom: 20px;
            font-size: 26px;
            color: #fff;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.7);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            box-shadow: 0 0 6px rgba(255, 255, 255, 0.7);
        }

        button {
            padding: 12px 30px;
            border: none;
            background: #ff5e62;
            color: white;
            font-size: 16px;
            border-radius: 50px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #ff3035;
        }

        .message {
            color: yellow;
            margin-bottom: 15px;
            font-weight: bold;
        }

        a {
            color: #ffffff;
            text-decoration: underline;
        }

        a:hover {
            color: #ffe6e6;
        }

        .back-home {
            display: block;
            margin-top: 25px;
            font-size: 14px;
            text-align: right;
        }

        .back-home a {
            color: #fff;
            font-weight: bold;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .back-home a:hover {
            background: rgba(255,255,255,0.4);
            color: #000;
        }

        @media (max-width: 500px) {
            .login-box {
                padding: 30px 20px;
            }

            .back-home {
                text-align: center;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 用户登录</h2>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="请输入用户名" required>
            <input type="password" name="password" placeholder="请输入密码" required>
            <button type="submit">立即登录</button>
        </form>
        <p>还没有账号？<a href="register.php">点我注册</a></p>

        <!-- 返回主页按钮 -->
        <div class="back-home">
            <a href="https://hmd.apj.我爱你/">返回主页</a>
        </div>
    </div>
</body>
</html>