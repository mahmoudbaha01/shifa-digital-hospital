<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') { header("Location: ../login.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // المريض يحجز موعداً لنفسه، لذا نأخذ الـ ID من الجلسة
    $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, appointment_date, status, medical_notes) VALUES (?, ?, 'pending', ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['date'], 'طلب حجز جديد']);
    $success = "تم إرسال طلب الحجز بنجاح!";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card p-4">
            <h3>طلب حجز موعد جديد</h3>
            <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
            <form method="POST">
                <label>اختر تاريخ ووقت الموعد:</label>
                <input type="datetime-local" name="date" class="form-control mb-3" required>
                <button type="submit" class="btn btn-primary">إرسال الطلب</button>
            </form>
            <a href="dashboard.php" class="btn btn-secondary mt-2">العودة للوحة التحكم</a>
        </div>
    </div>
</body>
</html>