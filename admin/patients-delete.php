<?php
// hospital/admin/patients-delete.php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_role = 'patient'");
        $stmt->execute([$_GET['id']]);
    } catch (PDOException $e) {
        die("حدث خطأ أثناء حذف ملف المريض: " . $e->getMessage());
    }
}

header("Location: patients-read.php");
exit();