<?php
session_start();
require_once '../includes/header.php';
require_once '../config/db.php';

// التأكد من الصلاحيات
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

// جلب قائمة المرضى المسجلين لدى هذا الطبيب
// نستخدم DISTINCT لمنع تكرار اسم المريض إذا كان لديه أكثر من موعد
$query = "SELECT DISTINCT u.id, u.name, u.email 
          FROM users u 
          JOIN appointments a ON u.id = a.patient_id 
          WHERE a.doctor_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$patients = $stmt->fetchAll();
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>إدارة ملفات المرضى</h2>
        <a href="dashboard.php" class="btn btn-secondary">العودة للوحة التحكم</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (count($patients) > 0): ?>
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>اسم المريض</th>
                            <th>البريد الإلكتروني</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo htmlspecialchars($p['email']); ?></td>
                            <td>
                                <!-- نربط بصفحة عرض التاريخ الطبي التي سنقوم بإنشائها -->
                                <a href="view_history.php?patient_id=<?php echo $p['id']; ?>" 
                                   class="btn btn-primary btn-sm">عرض السجل الطبي</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">لا يوجد مرضى مسجلون في مواعيدك حالياً.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>