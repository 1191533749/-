<?php
require_once '../inc/db.php';
session_start();

// 未登录跳转
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// 获取模块列表
if ($is_admin) {
    $stmt = $pdo->query("SELECT * FROM modules ORDER BY created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
}
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8" />
    <title>模块管理</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            margin: 0; 
            padding: 20px;
            min-height: 100vh;
            background: linear-gradient(135deg, #ffe4e6, #f9d5d3);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            color: #5a3e3d;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .container {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 28px;
            box-shadow: 0 8px 32px 0 rgba(197, 148, 148, 0.25), inset 0 0 40px 5px rgba(245, 180, 180, 0.15);
            padding: 30px 40px;
            width: 100%;
            max-width: 1100px;
            box-sizing: border-box;
        }
        h2 {
            margin: 0 0 32px 0;
            font-weight: 700;
            font-size: 2.2rem;
            text-align: center;
            letter-spacing: 1.2px;
            color: #784c4b;
            text-shadow: 0 1px 3px rgba(245, 180, 180, 0.6);
        }

        .top-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 24px;
        }

        .btn-create,
        .btn-back {
            background: linear-gradient(135deg, #f7cac9, #f5a4a2);
            color: #5a3e3d;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 24px;
            font-size: 14px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(245, 164, 162, 0.3), inset 0 -1px 3px rgba(255, 255, 255, 0.35);
            transition: all 0.3s ease;
        }

        .btn-create:hover,
        .btn-back:hover {
            background: linear-gradient(135deg, #f5a4a2, #d96c6a);
            color: #3a1f1e;
            box-shadow: 0 6px 20px rgba(217, 108, 106, 0.45), inset 0 -1px 3px rgba(255, 255, 255, 0.4);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 16px;
            table-layout: fixed;
        }
        thead tr th {
            background: rgba(247, 202, 201, 0.5);
            padding: 18px 14px;
            color: #784c4b;
            font-weight: 700;
            text-align: center;
            border-radius: 24px 24px 0 0;
            letter-spacing: 0.9px;
            user-select: none;
            box-shadow: inset 0 2px 8px rgba(255, 255, 255, 0.35), 0 3px 10px rgba(197, 148, 148, 0.25);
        }
        tbody tr {
            background: rgba(255, 255, 255, 0.65);
            box-shadow: 0 8px 25px rgba(197, 148, 148, 0.2);
            border-radius: 24px;
            transition: box-shadow 0.3s ease;
            color: #5a3e3d;
        }
        tbody tr:hover {
            box-shadow: 0 14px 40px rgba(217, 108, 106, 0.35);
        }
        tbody tr td {
            padding: 18px 16px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        tbody tr td:first-child {
            text-align: left;
            font-weight: 600;
            padding-left: 32px;
        }
        tbody tr td:last-child {
            padding-right: 32px;
        }
        td.actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
            padding: 16px 0;
        }
        a.btn {
            background: linear-gradient(135deg, #f7cac9, #f5a4a2);
            color: #5a3e3d;
            font-weight: 600;
            padding: 10px 28px;
            border-radius: 36px;
            text-decoration: none;
            box-shadow: 0 8px 30px rgba(245, 164, 162, 0.5), inset 0 -3px 5px rgba(255, 255, 255, 0.35);
            transition: background 0.3s ease, box-shadow 0.3s ease;
            white-space: nowrap;
            user-select: none;
            cursor: pointer;
            display: inline-block;
        }
        a.btn:hover {
            background: linear-gradient(135deg, #f5a4a2, #d96c6a);
            box-shadow: 0 14px 45px rgba(217, 108, 106, 0.75), inset 0 -3px 5px rgba(255, 255, 255, 0.4);
            color: #3a1f1e;
        }
        a.delete-btn {
            background: linear-gradient(135deg, #e97474, #c05656);
            box-shadow: 0 8px 30px rgba(192, 86, 86, 0.5), inset 0 -3px 5px rgba(255, 255, 255, 0.3);
            color: #6b2c2c;
        }
        a.delete-btn:hover {
            background: linear-gradient(135deg, #c05656, #8b3535);
            box-shadow: 0 14px 45px rgba(139, 53, 53, 0.8), inset 0 -3px 5px rgba(255, 255, 255, 0.4);
            color: #3a1f1e;
        }
        @media (max-width: 780px) {
            .container {
                padding: 25px 20px;
            }
            h2 {
                font-size: 1.8rem;
            }
            thead tr th, tbody tr td {
                padding: 14px 10px;
                font-size: 14px;
            }
            tbody tr td:first-child {
                padding-left: 16px;
            }
            tbody tr td:last-child {
                padding-right: 16px;
            }
            td.actions {
                flex-direction: column;
                gap: 10px;
                padding: 12px 0;
            }
            a.btn, .top-actions a {
                width: 100%;
                padding: 12px 0;
                font-size: 16px;
                border-radius: 30px;
                box-shadow: none;
                text-align: center;
            }
            a.btn:hover, .top-actions a:hover {
                box-shadow: none;
            }
        }
        @media (max-width: 400px) {
            h2 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
<h2>模块管理</h2>
<div class="top-actions">
    <a href="add_module.php" class="btn-create">创建模块</a>
    <a href="../dashboard/index.php" class="btn-back">返回后台</a>
</div>

    <?php if (empty($modules)): ?>
        <p style="text-align:center; font-weight: 600; color: #b97777;">暂无模块，请先创建。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>群主昵称</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['module_name']) ?></td>
                        <td><?= $m['created_at'] ?></td>
                        <td class="actions">
                            <a class="btn" href="blacklists.php?module_id=<?= $m['id'] ?>">编辑模块</a>
                            <?php if ($is_admin || $m['user_id'] == $user_id): ?>
                                <a class="btn" href="edit_module.php?id=<?= $m['id'] ?>">群主更名</a>
                                <a class="btn delete-btn" href="delete_module.php?id=<?= $m['id'] ?>" onclick="return confirm('确认删除此模块？将同时删除其下所有黑名单记录');">删除模块</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>