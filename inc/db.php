<?php
// 数据库配置
$host = 'localhost';
$dbname = 'hmd_apj____';
$username = 'hmd_apj____';
$password = 'PkdTTkAsQmfeBhyf';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
?>