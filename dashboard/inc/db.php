<?php
$host = 'localhost';
$dbname = '替换成数据库密码';
$user = '替换成数据库用户名';
$pass = '替换成数据库密码';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}