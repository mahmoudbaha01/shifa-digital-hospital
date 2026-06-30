<?php
// hospital/admin/clinics-delete.php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';
$id = intval($_GET['id'] ?? 0);
try {
    $stmt = $pdo->prepare("DELETE FROM clinics WHERE id = :id");
    $stmt->execute(['id' => $id]);
} catch (PDOException $e) { }
header("Location: clinics-read.php");
exit();