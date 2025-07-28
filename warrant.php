<?php
session_start();
require_once __DIR__ . '/inc/db.php';

$blacklist_id = (int)($_GET['blacklist_id'] ?? 0);
if (!$blacklist_id) {
    die("缺少黑户ID");
}

$stmt = $pdo->prepare("
    SELECT b.*, m.module_name, u.username 
    FROM blacklists b
    JOIN modules m ON b.module_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE b.id = ?
");
$stmt->execute([$blacklist_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) {
    die("未找到对应黑户");
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8" />
    <title>生成通缉令 - <?= htmlspecialchars($data['wx_id']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            margin:0; padding:0;
            background: #f4e1c1;
            background-size: cover;
            font-family: "KaiTi", "STKaiti", "楷体", serif;
            user-select: none;
            -webkit-user-select:none;
        }
        .container {
            max-width: 400px;
            margin: 20px auto;
            background: rgba(255, 248, 220, 0.85);
            padding: 15px 20px 30px;
            box-shadow: 0 0 20px #a86c1f;
            border: 5px solid #9c2a2a;
            border-radius: 12px;
            position: relative;
        }
        h1.title {
            font-family: "DFPCCaiFang", "华文行楷", cursive, "楷体";
            font-size: 48px;
            color: #c22;
            text-align: center;
            text-shadow: 2px 2px 6px #800000aa;
            margin: 15px 0 40px;
        }
        .avatar-area {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
        }
        canvas#avatarCanvas {
            border: 6px solid #922;
            border-radius: 10px;
            box-shadow: 0 0 10px #70000088 inset;
            background: #fff;
        }
        .upload-label {
            display: inline-block;
            margin: 10px auto 25px;
            padding: 8px 20px;
            background: #b02929;
            color: #fff;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .upload-label:hover {
            background: #8a2222;
        }
        .info-area {
            font-size: 16px;
            line-height: 1.6;
            color: #442200;
            padding: 0 10px;
            letter-spacing: 1.3px;
            border-top: 2px solid #a94a4a;
            border-bottom: 2px solid #a94a4a;
            margin-bottom: 20px;
            min-height: 140px;
        }
        .info-area p {
            margin: 6px 0;
        }
        .seal {
            position: absolute;
            top: 95px;
            left: 50%;
            width: 120px;
            height: 120px;
            margin-left: -60px;
            pointer-events: none;
            opacity: 0.75;
            transform: rotate(-15deg);
            filter: drop-shadow(2px 2px 2px rgba(139,0,0,0.7));
            display: none;
        }
        .btn-group {
            text-align: center;
        }
        button {
            background-color: #b72a2a;
            border: none;
            color: #fff;
            font-weight: bold;
            padding: 10px 26px;
            margin: 0 8px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 0 6px #a33;
            transition: background-color 0.25s ease;
        }
        button:hover {
            background-color: #7f1f1f;
        }
        @media(max-width: 440px) {
            .container {
                max-width: 90vw;
                padding: 15px 15px 25px;
            }
            h1.title {
                font-size: 36px;
                margin-bottom: 30px;
            }
            canvas#avatarCanvas {
                width: 100% !important;
                height: auto !important;
                border-width: 4px;
            }
            .seal {
                width: 80px;
                height: 80px;
                top: 75px;
                margin-left: -40px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="title">通缉令</h1>

    <div class="avatar-area">
        <canvas id="avatarCanvas" width="150" height="180"></canvas>
        <img class="seal" src="/TJL/seal.png" alt="印章" onload="this.style.display='block'" />
    </div>

    <label class="upload-label" for="avatarUpload">上传头像（仅用于生成；不会保存在云端）</label>
    <input type="file" accept="image/*" id="avatarUpload" style="display:none" />

    <div class="info-area" id="infoArea">
        <p><strong>黑户ID：</strong><?= htmlspecialchars($data['wx_id']) ?></p>
        <p><strong>黑户昵称：</strong><?= htmlspecialchars($data['nickname']) ?></p>
        <p><strong>上报群主：</strong><?= htmlspecialchars($data['module_name']) ?></p>
        <p><strong>限制群聊：</strong><?= htmlspecialchars($data['group_name']) ?></p>
        <p><strong>上报时间：</strong><?= $data['blacklist_time'] ? htmlspecialchars(substr($data['blacklist_time'],0,10)) : '' ?></p>
        <p><strong>上报原因：</strong><br><?= nl2br(htmlspecialchars($data['reason'])) ?></p>
    </div>

    <div class="btn-group">
        <button id="generateBtn">生成通缉令</button>
        <button onclick="window.location.href='/index.php'">返回主页</button>
        <p style="color:#9c2a2a; margin-top:10px; margin-bottom:0; font-size:14px;">*电脑端生成的通缉令将附带背景资源</p>
    </div>
</div>

<script>
(() => {
    const canvas = document.getElementById('avatarCanvas');
    const ctx = canvas.getContext('2d');
    let avatarImg = null;
    let bgImage = null;
    let sealImage = null;

    // 加载背景图（失败忽略）
    const loadBgImage = () => {
        return new Promise(resolve => {
            const img = new Image();
            img.src = '/TJL/bg.jpg';
            img.onload = () => { bgImage = img; resolve(); };
            img.onerror = () => resolve();
        });
    };

    // 加载印章图（失败忽略）
    const loadSealImage = () => {
        return new Promise(resolve => {
            const img = new Image();
            img.src = '/TJL/seal.png';
            img.onload = () => { sealImage = img; resolve(); };
            img.onerror = () => resolve();
        });
    };

    // 绘制默认头像框
    function drawBase() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#f4e1c1';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.shadowColor = 'rgba(0,0,0,0.3)';
        ctx.shadowBlur = 6;
        ctx.strokeStyle = '#922';
        ctx.lineWidth = 6;
        ctx.strokeRect(3, 3, canvas.width-6, canvas.height-6);
        ctx.shadowBlur = 0;
        ctx.fillStyle = '#a55';
        ctx.font = '16px KaiTi, STKaiti, 楷体';
        ctx.textAlign = 'center';
        ctx.fillText('请上传头像', canvas.width/2, canvas.height/2);
    }

    // 绘制上传的头像
    function drawAvatar(img) {
        drawBase();
        if (!img) return;

        let cw = canvas.width - 24;
        let ch = canvas.height - 24;
        let sx = 0, sy = 0, sw = img.width, sh = img.height;
        if (img.width > img.height) {
            sx = (img.width - img.height) / 2;
            sw = img.height;
        } else if (img.height > img.width) {
            sy = (img.height - img.width) / 2;
            sh = img.width;
        }
        ctx.drawImage(img, sx, sy, sw, sh, 12, 12, cw, ch);
        
        // 头像高光
        const grad = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
        grad.addColorStop(0, 'rgba(255,255,255,0.3)');
        grad.addColorStop(0.8, 'rgba(255,255,255,0)');
        ctx.fillStyle = grad;
        ctx.fillRect(12, 12, cw, ch);
    }

    // 上传头像事件
    document.getElementById('avatarUpload').addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;

        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            avatarImg = img;
            drawAvatar(img);
            URL.revokeObjectURL(url);
        };
        img.onerror = () => alert('头像加载失败，请换一张图片');
        img.src = url;
    });

    // 初始化
    drawBase();

    // 生成按钮点击事件
    document.getElementById('generateBtn').addEventListener('click', async () => {
        await Promise.all([loadBgImage(), loadSealImage()]);

        // 创建输出画布
        const w = 400;
        const h = 500;
        const canvasOut = document.createElement('canvas');
        canvasOut.width = w;
        canvasOut.height = h;
        const ctxOut = canvasOut.getContext('2d');

        // 绘制背景
        if (bgImage) {
            ctxOut.drawImage(bgImage, 0, 0, w, h);
        } else {
            ctxOut.fillStyle = '#f4e1c1';
            ctxOut.fillRect(0, 0, w, h);
        }

        // 绘制标题
        ctxOut.font = 'bold 48px 华文行楷, STKaiti, 楷体';
        ctxOut.fillStyle = '#b00000';
        ctxOut.textAlign = 'center';
        ctxOut.shadowColor = '#600000aa';
        ctxOut.shadowBlur = 4;
        ctxOut.fillText('通缉令', w/2, 70);
        ctxOut.shadowBlur = 0;

        // 绘制头像（进一步缩小尺寸）
        const avatarX = (w - 150) / 2;
        const avatarY = 100;
        if (avatarImg) {
            let side = Math.min(avatarImg.width, avatarImg.height);
            let sx = (avatarImg.width - side) / 2;
            let sy = (avatarImg.height - side) / 2;
            ctxOut.drawImage(avatarImg, sx, sy, side, side, avatarX, avatarY, 150, 180);
        } else {
            ctxOut.fillStyle = '#bbb';
            ctxOut.fillRect(avatarX, avatarY, 150, 180);
            ctxOut.fillStyle = '#666';
            ctxOut.font = '14px 楷体';
            ctxOut.textAlign = 'center';
            ctxOut.fillText('无头像', w/2, avatarY + 90);
        }

        // 绘制头像边框
        ctxOut.lineWidth = 6;
        ctxOut.strokeStyle = '#800000';
        ctxOut.shadowColor = 'rgba(0,0,0,0.25)';
        ctxOut.shadowBlur = 8;
        ctxOut.strokeRect(avatarX, avatarY, 150, 180);
        ctxOut.shadowBlur = 0;

        // 绘制印章（适配小头像）
        if (sealImage) {
            const sealSize = 80;
            const sealX = avatarX + 110;
            const sealY = avatarY + 20;
            ctxOut.save();
            ctxOut.translate(sealX + sealSize/2, sealY + sealSize/2);
            ctxOut.rotate(-15 * Math.PI / 180);
            ctxOut.drawImage(sealImage, -sealSize/2, -sealSize/2, sealSize, sealSize);
            ctxOut.restore();
        }

        // 信息文字
        const lines = [
            '黑户ID：<?= addslashes($data["wx_id"]) ?>',
            '黑户昵称：<?= addslashes($data["nickname"]) ?>',
            '上报群主：<?= addslashes($data["module_name"]) ?>',
            '限制群聊：<?= addslashes($data["group_name"]) ?>',
            '上报时间：<?= $data["blacklist_time"] ? addslashes(substr($data["blacklist_time"],0,10)) : "" ?>',
            '上报原因：',
            <?php 
            $reasonLines = $data['reason'] ? explode("\n", $data['reason']) : [];
            $output = [];
            foreach ($reasonLines as $line) {
                $output[] = "'" . addslashes(trim($line)) . "'";
            }
            echo implode(',', $output);
            ?>
        ];

        // 绘制文字（进一步上移）
        ctxOut.fillStyle = '#5a2e0a';
        ctxOut.font = '16px 楷体';
        ctxOut.textAlign = 'left';
        let startX = 40;
        let startY = 320;
        let lineHeight = 26;
        lines.forEach((line, i) => {
            ctxOut.fillText(line, startX, startY + i * lineHeight);
        });

        // 显示生成的图片
        let imgShow = document.getElementById('resultImg');
        if (!imgShow) {
            imgShow = document.createElement('img');
            imgShow.id = 'resultImg';
            imgShow.style.marginTop = '20px';
            imgShow.style.width = '100%';
            imgShow.style.border = '4px solid #b22';
            imgShow.style.borderRadius = '8px';
            document.querySelector('.container').appendChild(imgShow);
        }
        imgShow.src = canvasOut.toDataURL('image/png');

        // 下载按钮
        let downloadBtn = document.getElementById('downloadBtn');
        if (!downloadBtn) {
            downloadBtn = document.createElement('button');
            downloadBtn.id = 'downloadBtn';
            downloadBtn.textContent = '下载通缉令';
            downloadBtn.style.marginTop = '15px';
            downloadBtn.onclick = () => {
                const a = document.createElement('a');
                a.href = canvasOut.toDataURL('image/png');
                a.download = '通缉令_<?= addslashes($data["wx_id"]) ?>.png';
                a.click();
            };
            document.querySelector('.btn-group').appendChild(downloadBtn);
        }
    });
})();
</script>
</body>
</html>