<?php
require_once '../inc/auth.php';
require_once '../inc/db.php';

$isAdmin = $_SESSION['is_admin'] ?? false;

$stmt = $pdo->query("SELECT id, username, is_admin FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8" />
    <title>用户管理</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            margin: 0; padding: 0;
            background: linear-gradient(135deg, #d299c2, #fef9d7);
            font-family: "Segoe UI", "Microsoft YaHei", sans-serif;
            min-height: 100vh;
            display: flex; justify-content: center; align-items: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            width: 95%;
            max-width: 800px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
        }
        h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.3);
            color: #fff;
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        th {
            background: rgba(255,255,255,0.4);
        }
        tr:hover {
            background: rgba(255,255,255,0.1);
        }
        button.delete-btn {
            background: rgba(255,69,69,0.8);
            border: none;
            padding: 6px 14px;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button.delete-btn:hover {
            background: rgba(255,0,0,0.9);
        }
        a.back {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #fff;
            background: rgba(255,255,255,0.3);
            padding: 10px 20px;
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        a.back:hover {
            background: rgba(255,255,255,0.6);
            color: #000;
        }

        /* 液态弹窗样式 */
        #confirm-popup {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        #confirm-popup .popup-content {
            background: rgba(255,255,255,0.35);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            padding: 24px 30px;
            border-radius: 20px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
            max-width: 320px;
            color: #333;
            text-align: center;
            font-size: 16px;
        }
        #confirm-popup button {
            margin: 15px 12px 0 12px;
            padding: 10px 28px;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: background 0.3s ease;
        }
        #confirm-popup button.confirm {
            background-color: #e91e63;
            color: white;
        }
        #confirm-popup button.confirm:hover {
            background-color: #d81b60;
        }
        #confirm-popup button.cancel {
            background-color: #ccc;
            color: #333;
        }
        #confirm-popup button.cancel:hover {
            background-color: #aaa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>👤 用户管理</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>权限</th>
                    <?php if ($isAdmin): ?>
                    <th>操作</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= $u['is_admin'] ? '管理员' : '普通用户' ?></td>
                    <?php if ($isAdmin): ?>
                    <td>
                        <?php if (!$u['is_admin']): ?>
                        <button class="delete-btn" onclick="showConfirm(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')">删除</button>
                        <?php else: ?>
                        <span style="color:#ccc;">不可删除</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="text-align:center;">
            <a href="index.php" class="back">返回后台</a>
        </div>
    </div>

    <!-- 液态确认弹窗 -->
    <div id="confirm-popup">
        <div class="popup-content">
            <p id="confirm-text">确认删除该用户吗？此操作将删除用户所有相关数据！</p>
            <button class="confirm" id="confirm-btn">确认删除</button>
            <button class="cancel" onclick="hideConfirm()">取消</button>
        </div>
    </div>

    <script>
        let deleteUserId = 0;
        function showConfirm(userId, username) {
            deleteUserId = userId;
            document.getElementById('confirm-text').textContent = `确认删除用户 "${username}" 吗？此操作将删除用户所有相关数据！`;
            document.getElementById('confirm-popup').style.display = 'flex';
        }
        function hideConfirm() {
            deleteUserId = 0;
            document.getElementById('confirm-popup').style.display = 'none';
        }
        document.getElementById('confirm-btn').addEventListener('click', function(){
            if(deleteUserId > 0){
                window.location.href = `delete_user.php?id=${deleteUserId}`;
            }
        });
    </script>
</body>
</html>