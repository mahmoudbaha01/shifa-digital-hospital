<?php
$host = 'localhost';
$db   = 'hospital';
$user = 'root';
$pass = ''; // ضع كلمة مرور قاعدة بياناتك هنا إذا وجدت

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>