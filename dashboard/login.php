<?php
session_start();
require_once 'inc/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stored = $user['password'];

        if (password_verify($password, $stored)) {
            if (!$user['is_admin']) {
                $error = "你不是管理员";
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                header("Location: index.php");
                exit();
            }
        } elseif (md5($password) === $stored) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$newHash, $user['id']]);

            if (!$user['is_admin']) {
                $error = "你不是管理员";
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                header("Location: index.php");
                exit();
            }
        } else {
            $error = "用户名或密码错误";
        }
    } else {
        $error = "用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8" />
    <title>管理员后台</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ffdde1, #ee9ca7);
            min-height: 100vh;
            font-family: "Segoe UI", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            position: relative;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            max-width: 400px;
            width: 90%;
            color: #fff;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #fff;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
            font-size: 16px;
            outline: none;
        }

        input::placeholder {
            color: #f0f0f0;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            background-color: rgba(255, 255, 255, 0.3);
            color: #fff;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: rgba(255, 255, 255, 0.6);
            color: #000;
        }

        .error {
            color: #ffdede;
            margin-bottom: 10px;
        }

        .normal-login-link {
            position: absolute;
            bottom: -20px; /* 公众号：剑指网络 */
            right: 15px;
            font-size: 14px;
            color: #ffffffc9;
            text-decoration: none;
            user-select: none;
        }

        .normal-login-link:hover {
            text-decoration: underline;
            color: #fff;
        }

        @media (max-width: 500px) {
            .login-container {
                padding: 20px;
            }

            h2 {
                font-size: 22px;
            }

            button {
                font-size: 14px;
            }

            .normal-login-link {
                font-size: 12px;
                bottom: -25px; /* 公众号：剑指网络 */
                right: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>管理员后台</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="text" name="username" placeholder="请输入用户名" required />
            <input type="password" name="password" placeholder="请输入密码" required />
            <button type="submit">立即登录</button>
        </form>
        <a class="normal-login-link" href="/login.php">用户登录</a>
    </div>
</body>
</html>