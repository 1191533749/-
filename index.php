<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/inc/db.php';

$search = trim($_GET['search'] ?? '');
$results = [];

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT 
            b.*, 
            m.module_name, 
            u.username,
            m.user_id
        FROM blacklists b
        JOIN modules m ON b.module_id = m.id
        JOIN users u ON m.user_id = u.id
        WHERE b.wx_id LIKE ?
        ORDER BY b.blacklist_time DESC
    ");
    $stmt->execute(['%' . $search . '%']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 动态拼接图片和视频路径
    foreach ($rows as &$row) {
        $user_id = $row['user_id'];
        $module_id = $row['module_id'];
        $blacklist_id = $row['id'];

        $basePath = "uploads/user_{$user_id}/module_{$module_id}/blacklist_{$blacklist_id}/";

        // 取第一个图片路径
        $imagePath = '';
        $imageDir = __DIR__ . '/' . $basePath;
        if (is_dir($imageDir)) {
            $images = glob($imageDir . "image*.{jpg,jpeg,png}", GLOB_BRACE);
            if ($images && count($images) > 0) {
                $imagePath = $basePath . basename($images[0]);
            }
        }

        // 视频路径检测
        $videoFile = $basePath . "video.mp4";
        $videoFullPath = __DIR__ . '/' . $videoFile;
        $videoPath = file_exists($videoFullPath) ? $videoFile : '';

        $row['image_path'] = $imagePath;
        $row['video_path'] = $videoPath;
    }
    unset($row);

    $results = $rows;
}

$stmt = $pdo->query("
    SELECT m.id, m.module_name, u.username 
    FROM modules m
    JOIN users u ON m.user_id = u.id
    ORDER BY m.created_at DESC
");
$modules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>微群黑户上报平台</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", "Microsoft Yahei", sans-serif;
            background: linear-gradient(135deg, #ffd4ec 0%, #f2f2f2 100%);
            color: #333;
        }
        header {
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.4);
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        header h1 {
            font-size: 18px;
            margin: 0;
            color: #e91e63;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        nav a {
            margin-left: 15px;
            text-decoration: none;
            color: #555;
            font-weight: bold;
        }
        nav a:hover {
            color: #e91e63;
        }
        .container {
            max-width: 960px;
            margin: 30px auto;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        form {
            text-align: center;
            margin-bottom: 25px;
        }
        input[type="text"] {
            width: 80%;
            max-width: 400px;
            padding: 8px 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            padding: 8px 16px;
            background-color: #e91e63;
            border: none;
            color: #fff;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 8px;
        }
        button:hover {
            background-color: #d81b60;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        table th, table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f0f0;
            text-align: center;
        }
        table th {
            background-color: #f8f8f8;
            color: #e91e63;
        }
        .view-btn {
            color: #e91e63;
            cursor: pointer;
            text-decoration: underline;
        }
        .popup {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .popup-content {
            background: rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            padding: 20px;
            border-radius: 20px;
            max-width: 90%;
            max-height: 85%;
            overflow-y: auto;
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
            font-size: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #333;
        }
        .popup-close {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 10px;
        }
        .popup-close button {
            background: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #e91e63;
        }
        @media (max-width: 600px) {
            header h1 {
                font-size: 16px;
            }
            input[type="text"] {
                width: 90%;
            }
            table th, table td {
                font-size: 13px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
<header>
    <h1>微群黑户上报平台</h1>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard/index.php">进入后台</a>
        <?php else: ?>
            <a href="login.php">登录</a>
            <a href="register.php">注册</a>
            <a href="dashboard/login.php">管理员登录</a>
        <?php endif; ?>
    </nav>
</header>
<div class="container">
    <form method="get" action="">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="输入微信原始ID">
        <button type="submit">搜索</button>
    </form>

    <?php if ($search !== ''): ?>
        <h3 style="text-align:center; font-size: 16px;">搜索结果（微信原始ID：<?= htmlspecialchars($search) ?>）</h3>
        <?php if (empty($results)): ?>
            <p style="text-align:center;">未找到匹配的黑户记录。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>黑户微信原始ID</th>
                        <th>黑户昵称</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['wx_id']) ?></td>
                            <td><?= htmlspecialchars($row['nickname']) ?></td>
                            <td><span class="view-btn" onclick="showDetails(`<?= htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE)) ?>`)">查看详情</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <h2 style="margin-top: 40px; text-align:center;">所有板块</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>群主昵称</th>
                <th>所属群聊</th>
                <th>查看</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($modules)): ?>
                <tr><td colspan="4">暂无板块</td></tr>
            <?php else: ?>
                <?php foreach ($modules as $mod): ?>
                    <tr>
                        <td><?= $mod['id'] ?></td>
                        <td><?= htmlspecialchars($mod['module_name']) ?></td>
                        <td><?= htmlspecialchars($mod['username']) ?></td>
                        <td><a href="dashboard/blacklists.php?module_id=<?= $mod['id'] ?>">查看所有黑户</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="popup" class="popup" style="display: none;">
    <div class="popup-content">
        <div class="popup-close">
            <button onclick="hideDetails()">×</button>
        </div>
        <div id="popup-body"></div>
    </div>
</div>

<script>
function showDetails(dataJson) {
    const data = JSON.parse(dataJson);
    const body = document.getElementById("popup-body");
    body.innerHTML = `
        <p><strong>黑户原始ID：</strong> ${data.wx_id}</p>
        <p><strong>黑户昵称：</strong> ${data.nickname}</p>
        <p><strong>上报群主：</strong> ${data.module_name}</p>
        <p><strong>限制群聊：</strong> ${data.group_name}</p>
        <p><strong>上报时间：</strong> ${data.blacklist_time ? data.blacklist_time.substr(0, 10) : ''}</p>
        <p><strong>上报原因：</strong><br>${data.reason.replace(/\n/g, "<br>")}</p>
        <p><strong>图片证据：</strong> ${data.image_path ? `<a href="${data.image_path}" target="_blank">查看</a>` : '—'}</p>
        <p><strong>视频证据：</strong> ${data.video_path ? `<a href="${data.video_path}" target="_blank">播放</a>` : '—'}</p>

        <p style="text-align:center; margin-top: 20px;">
            <a href="warrant.php?blacklist_id=${data.id}" target="_blank"
               style="display:inline-block; padding:10px 22px; background-color:#dc3545; color:#fff;
                      border-radius:8px; font-weight:bold; text-decoration:none;">
                生成通缉令
            </a>
        </p>
    `;
    document.getElementById("popup").style.display = "flex";
}
function hideDetails() {
    document.getElementById("popup").style.display = "none";
}
</script>

<footer style="text-align: center; padding: 20px 10px; font-size: 14px; color: #888;">
    开发者：肾萎书记 ｜ 公众号：剑指网络 ｜ 
    <a href="https://apj.我爱你/" target="_blank" style="color:#e91e63; text-decoration: none;">官网：爱破解</a>
</footer>

</body>
</html>