<?php
require_once '../config/db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['appointment_id'];
    $notes = $_POST['medical_notes'];
    
    $stmt = $pdo->prepare("UPDATE appointments SET medical_notes = ? WHERE id = ?");
    $stmt->execute([$notes, $id]);
    
    header("Location: dashboard.php");
    exit();
}
?>