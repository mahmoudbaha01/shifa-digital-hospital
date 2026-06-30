<?php
session_start();
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT a.*, u.name as doctor_name FROM appointments a 
                       JOIN users u ON a.doctor_id = u.id 
                       WHERE a.patient_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$my_appointments = $stmt->fetchAll();
?>

<div class="container mt-5">
    <h2>مواعيدي المحجوزة</h2>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>الطبيب</th>
                <th>التاريخ</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($my_appointments as $a): ?>
            <tr>
                <td><?php echo htmlspecialchars($a['doctor_name']); ?></td>
                <td><?php echo $a['appointment_date']; ?></td>
                <td><?php echo $a['status']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="book_appointment.php" class="btn btn-success">حجز موعد جديد</a>
</div>
<?php require_once '../includes/footer.php'; ?>