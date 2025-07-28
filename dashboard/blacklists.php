<?php
require_once 'inc/db.php';
require_once 'inc/auth.php';
session_start();

if (!isset($_GET['module_id']) || !is_numeric($_GET['module_id'])) {
    die("模块ID无效");
}

$module_id = (int)$_GET['module_id'];

$stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
$stmt->execute([$module_id]);
$module = $stmt->fetch();

if (!$module) {
    die("模块不存在");
}

$user_id = $_SESSION['user_id'] ?? 0;
$isAdmin = ($_SESSION['is_admin'] ?? 0) == 1;
$isOwner = ($module['user_id'] ?? 0) == $user_id;
$isEditable = $isAdmin || $isOwner;

$stmt = $pdo->prepare("SELECT * FROM blacklists WHERE module_id = ? ORDER BY created_at DESC");
$stmt->execute([$module_id]);
$blacklists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 定义函数，获取图片和视频文件（只显示第一张图片作为缩略）
function getMediaPaths($user_id, $module_id, $blacklist_id) {
    $relativeDir = "uploads/user_{$user_id}/module_{$module_id}/blacklist_{$blacklist_id}/";
    $absoluteDir = __DIR__ . "/../" . $relativeDir;

    $result = [
        'images' => [],
        'video' => '',
    ];

    if (is_dir($absoluteDir)) {
        // 取图片（最多5张）
        $images = glob($absoluteDir . "image*.{jpg,jpeg,png}", GLOB_BRACE);
        if ($images) {
            foreach ($images as $img) {
                $result['images'][] = $relativeDir . basename($img);
            }
        }

        // 取视频
        $videoFile = $absoluteDir . "video.mp4";
        if (file_exists($videoFile)) {
            $result['video'] = $relativeDir . "video.mp4";
        }
    }

    return $result;
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($module['module_name']) ?> - 的黑户记录</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    html, body {
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #ffdde1, #ee9ca7);
        font-family: "Segoe UI", "Microsoft YaHei", sans-serif;
        color: #fff;
        min-height: 100vh;
    }
    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        background: rgba(255,255,255,0.1);
        border-radius: 16px;
        backdrop-filter: blur(12px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
    }
    .actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
        justify-content: center;
    }
    .actions a {
        background: rgba(255,255,255,0.25);
        color: #fff;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 8px;
        transition: 0.3s;
    }
    .actions a:hover {
        background: rgba(255,255,255,0.5);
        color: #000;
    }

    .blacklist-table {
        display: none;
    }

    .card-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .card {
        background: rgba(255,255,255,0.15);
        padding: 16px;
        border-radius: 12px;
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .card div {
        margin: 6px 0;
    }

    .card div span {
        font-weight: bold;
        margin-right: 6px;
        display: inline-block;
        min-width: 88px;
    }

    .card .actions {
        justify-content: flex-end;
        margin-top: 10px;
    }

    .media-thumb {
        max-height: 80px;
        border-radius: 6px;
        box-shadow: 0 0 6px rgba(0,0,0,0.3);
    }

    @media(min-width: 768px) {
        .blacklist-table {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .blacklist-table th,
        .blacklist-table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            word-break: break-word;
        }
        .blacklist-table th {
            background-color: rgba(255,255,255,0.2);
        }
        .card-list {
            display: none;
        }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2><?= htmlspecialchars($module['module_name']) ?> - 的黑户记录</h2>
    <div class="actions">
        <a href="modules.php">模块列表</a>
        <?php if ($isEditable): ?>
            <a href="add_blacklist.php?module_id=<?= $module_id ?>">添加黑户</a>
        <?php endif; ?>
    </div>

    <?php if (empty($blacklists)): ?>
        <p style="text-align:center;">暂无黑户记录</p>
    <?php else: ?>

    <!-- 桌面端表格显示 -->
    <table class="blacklist-table">
      <thead>
        <tr>
          <th>微信号</th>
          <th>昵称</th>
          <th>拉黑时间</th>
          <th>群名称</th>
          <th>原因</th>
          <th>图片</th>
          <th>视频</th>
          <th>创建时间</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($blacklists as $b):
            $media = getMediaPaths($module['user_id'], $module_id, $b['id']);
        ?>
        <tr>
          <td><?= htmlspecialchars($b['wx_id']) ?></td>
          <td><?= htmlspecialchars($b['nickname']) ?></td>
          <td><?= $b['blacklist_time'] ?></td>
          <td><?= htmlspecialchars($b['group_name']) ?></td>
          <td><?= nl2br(htmlspecialchars($b['reason'])) ?></td>
          <td>
            <?php if (!empty($media['images'])): ?>
                <a href="/<?= htmlspecialchars($media['images'][0]) ?>" target="_blank">
                    <img src="/<?= htmlspecialchars($media['images'][0]) ?>" alt="图片" class="media-thumb" />
                </a>
                <?php if (count($media['images']) > 1): ?>
                    <br/><small>共<?= count($media['images']) ?>张</small>
                <?php endif; ?>
            <?php else: ?>
                无
            <?php endif; ?>
          </td>
          <td>
            <?php if ($media['video']): ?>
                <a href="/<?= htmlspecialchars($media['video']) ?>" target="_blank">查看视频</a>
            <?php else: ?>
                无
            <?php endif; ?>
          </td>
          <td><?= $b['created_at'] ?></td>
          <td>
            <?php if ($isEditable): ?>
              <a href="edit_blacklist.php?id=<?= $b['id'] ?>">编辑</a> |
              <a href="delete_blacklist.php?id=<?= $b['id'] ?>" onclick="return confirm('确认删除这条记录？');">删除</a>
            <?php else: ?>无权限<?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- 手机端卡片显示 -->
    <div class="card-list">
      <?php foreach ($blacklists as $b):
        $media = getMediaPaths($module['user_id'], $module_id, $b['id']);
      ?>
      <div class="card">
        <div><span>微信号：</span><?= htmlspecialchars($b['wx_id']) ?></div>
        <div><span>昵称：</span><?= htmlspecialchars($b['nickname']) ?></div>
        <div><span>拉黑时间：</span><?= $b['blacklist_time'] ?></div>
        <div><span>群名称：</span><?= htmlspecialchars($b['group_name']) ?></div>
        <div><span>原因：</span><?= nl2br(htmlspecialchars($b['reason'])) ?></div>
        <div><span>图片：</span>
            <?php if (!empty($media['images'])): ?>
                <a href="/<?= htmlspecialchars($media['images'][0]) ?>" target="_blank" style="color:#fff;">
                    <img src="/<?= htmlspecialchars($media['images'][0]) ?>" alt="图片" class="media-thumb" />
                </a>
                <?php if (count($media['images']) > 1): ?>
                    <br/><small>共<?= count($media['images']) ?>张</small>
                <?php endif; ?>
            <?php else: ?>
                无
            <?php endif; ?>
        </div>
        <div><span>视频：</span>
            <?php if ($media['video']): ?>
                <a href="/<?= htmlspecialchars($media['video']) ?>" target="_blank" style="color:#fff;">查看视频</a>
            <?php else: ?>
                无
            <?php endif; ?>
        </div>
        <div><span>创建时间：</span><?= $b['created_at'] ?></div>
        <?php if ($isEditable): ?>
        <div class="actions">
            <a href="edit_blacklist.php?id=<?= $b['id'] ?>">编辑</a>
            <a href="delete_blacklist.php?id=<?= $b['id'] ?>" onclick="return confirm('确认删除？');">删除</a>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <?php endif; ?>
  </div>
</body>
</html>