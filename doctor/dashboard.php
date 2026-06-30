<?php
// 1. تشغيل الجلسة والاتصال بقاعدة البيانات
session_start();
require_once '../includes/header.php'; // تضمين القالب العلوي
require_once '../config/db.php';

// 2. التحقق من الصلاحيات (يجب أن يكون المستخدم طبيب)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

// 3. جلب مواعيد المرضى المسندة لهذا الطبيب
$doctor_id = $_SESSION['user_id'];
$query = "SELECT a.*, u.name as patient_name 
          FROM appointments a 
          JOIN users u ON a.patient_id = u.id 
          WHERE a.doctor_id = ? 
          ORDER BY a.appointment_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll();
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>لوحة تحكم الطبيب</h2>
        <a href="manage_patients.php" class="btn btn-outline-primary">إدارة ملفات المرضى</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover text-center">
                <thead class="table-light">
                    <tr>
                        <th>اسم المريض</th>
                        <th>تاريخ الموعد</th>
                        <th>الحالة</th>
                        <th>التشخيص / ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($a['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($a['status']); ?></td>
                            <td>
                                <form action="update_medical_notes.php" method="POST">
                                    <input type="hidden" name="appointment_id" value="<?php echo $a['id']; ?>">
                                    <input type="text" name="medical_notes" class="form-control" 
                                           value="<?php echo htmlspecialchars($a['medical_notes'] ?? ''); ?>" 
                                           placeholder="أدخل التشخيص هنا...">
                                    <button type="submit" class="btn btn-success btn-sm mt-1">حفظ</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">لا توجد مواعيد حالياً.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; // تضمين القالب السفلي ?>