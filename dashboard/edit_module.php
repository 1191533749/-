<?php
session_start();
require_once '../inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

// 先获取模块信息，验证权限
$stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
$stmt->execute([$id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    $error = "模块不存在";
} elseif (!$is_admin && $module['user_id'] != $user_id) {
    $error = "你无权编辑此模块";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $module_name = trim($_POST['module_name'] ?? '');

    if ($module_name === '') {
        $error = "模块名称不能为空";
    } else {
        $update = $pdo->prepare("UPDATE modules SET module_name = ? WHERE id = ?");
        if ($update->execute([$module_name, $id])) {
            header("Location: index.php?msg=模块修改成功");
            exit;
        } else {
            $error = "数据库更新失败";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8" />
    <title>修改模块名</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            margin: 0; 
            padding: 40px 20px;
            min-height: 100vh;
            background: linear-gradient(135deg, #ffe4e6, #f9d5d3);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #5a3e3d;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-radius: 28px;
            box-shadow:
                0 8px 32px rgba(197, 148, 148, 0.25),
                inset 0 0 40px 5px rgba(245, 180, 180, 0.15);
            padding: 32px 40px;
            width: 100%;
            max-width: 480px;
            box-sizing: border-box;
            text-align: center;
        }
        h2 {
            margin-bottom: 32px;
            font-weight: 700;
            font-size: 2rem;
            color: #784c4b;
            text-shadow: 0 1px 3px rgba(245, 180, 180, 0.6);
        }
        input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            border-radius: 36px;
            border: 1px solid #dba8a7;
            font-size: 1.1rem;
            font-weight: 500;
            color: #5a3e3d;
            box-shadow:
                inset 0 3px 6px rgba(255,255,255,0.8),
                0 2px 6px rgba(217, 108, 106, 0.3);
            outline: none;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus {
            border-color: #e97474;
            box-shadow:
                inset 0 3px 6px rgba(255,255,255,0.9),
                0 0 12px rgba(233, 30, 99, 0.6);
        }
        button {
            margin-top: 30px;
            padding: 14px 38px;
            border-radius: 36px;
            border: none;
            font-weight: 700;
            font-size: 1.2rem;
            background: linear-gradient(135deg, #f7cac9, #f5a4a2);
            color: #5a3e3d;
            box-shadow:
                0 8px 30px rgba(245, 164, 162, 0.5),
                inset 0 -3px 5px rgba(255, 255, 255, 0.35);
            cursor: pointer;
            transition: background 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
        }
        button:hover {
            background: linear-gradient(135deg, #f5a4a2, #d96c6a);
            box-shadow:
                0 14px 45px rgba(217, 108, 106, 0.75),
                inset 0 -3px 5px rgba(255, 255, 255, 0.4);
            color: #3a1f1e;
        }
        .message {
            margin-top: 20px;
            font-weight: 600;
            color: #c05656;
        }
        .back-link {
            margin-top: 25px;
            display: inline-block;
            font-weight: 600;
            color: #784c4b;
            text-decoration: underline;
            cursor: pointer;
            user-select: none;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #e97474;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>✏️ 修改模块</h2>

    <?php if ($error): ?>
        <div class="message"><?= htmlspecialchars($error) ?></div>
        <a href="modules.php" class="back-link">模块管理</a>
    <?php elseif ($module): ?>
        <form method="post" novalidate>
            <input type="text" name="module_name" value="<?= htmlspecialchars($module['module_name']) ?>" required maxlength="50" placeholder="请输入模块名称" />
            <button type="submit">保存修改</button>
        </form>
        <a href="modules.php" class="back-link">模块管理</a>
    <?php endif; ?>
</div>
</body>
</html>