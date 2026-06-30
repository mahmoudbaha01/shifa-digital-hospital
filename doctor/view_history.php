<?php
session_start();
require_once '../includes/header.php';
require_once '../config/db.php';

// التأكد من الصلاحيات
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

// التأكد من وجود معرف المريض في الرابط
if (!isset($_GET['patient_id'])) {
    die("خطأ: لم يتم تحديد المريض.");
}

$patient_id = $_GET['patient_id'];

// جلب بيانات المريض
$stmt_patient = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt_patient->execute([$patient_id]);
$patient = $stmt_patient->fetch();

// جلب التاريخ الطبي (جميع المواعيد السابقة لهذا المريض)
$stmt_history = $pdo->prepare("SELECT * FROM appointments WHERE patient_id = ? ORDER BY appointment_date DESC");
$stmt_history->execute([$patient_id]);
$history = $stmt_history->fetchAll();
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>السجل الطبي للمريض: <?php echo htmlspecialchars($patient['name'] ?? 'غير معروف'); ?></h2>
        <a href="manage_patients.php" class="btn btn-secondary">العودة لقائمة المرضى</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>التاريخ</th>
                        <th>التشخيص / الملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($history) > 0): ?>
                        <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($h['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($h['medical_notes'] ?? 'لا يوجد تشخيص مسجل'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">لا يوجد تاريخ طبي مسجل لهذا المريض.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>