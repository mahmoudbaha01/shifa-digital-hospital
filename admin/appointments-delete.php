<?php
// hospital/admin/appointments-delete.php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$_GET['id']]);
    } catch (PDOException $e) {
        die("حدث خطأ أثناء حذف الموعد: " . $e->getMessage());
    }
}

header("Location: appointments-read.php");
exit();