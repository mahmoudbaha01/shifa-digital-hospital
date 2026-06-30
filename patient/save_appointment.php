<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // التأكد من استلام البيانات
    $doctor_id = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
    $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : null;
    $patient_id = $_SESSION['user_id'];

    if ($doctor_id && $appointment_date) {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$patient_id, $doctor_id, $appointment_date]);
        
        // إعادة توجيه إلى لوحة تحكم المريض
        header("Location: dashboard.php");
        exit();
    } else {
        echo "خطأ: بيانات غير مكتملة.";
    }
} else {
    // إذا دخل المستخدم للملف مباشرة
    header("Location: book_appointment.php");
    exit();
}
?>