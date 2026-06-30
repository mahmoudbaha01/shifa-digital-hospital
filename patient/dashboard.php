<?php
session_start();

$db_path = '../config/db.php';
if (!file_exists($db_path)) {
    die("خطأ: ملف الاتصال بقاعدة البيانات غير موجود.");
}
require_once $db_path;

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT a.appointment_date, a.medical_notes, u.name as doctor_name 
                        FROM appointments a 
                        LEFT JOIN users u ON a.doctor_id = u.id 
                        WHERE a.patient_id = ? 
                        ORDER BY a.appointment_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();

// جلب قائمة الأطباء للمودال
$doctors = $pdo->query("SELECT id, name FROM users WHERE role = 'doctor'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>لوحة المريض</title>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="m-0">مواعيدي الطبية</h3>
                <div>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bookModal">إضافة موعد جديد</button>
                    <a href="../logout.php" class="btn btn-danger me-2">تسجيل الخروج</a>
                </div>
            </div>

            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr><th>التاريخ</th><th>الطبيب</th><th>حالة المريض</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars($a['doctor_name'] ?: 'غير محدد'); ?></td>
                        <td>
                            <?php if (!empty($a['medical_notes'])): ?>
                                <div class="alert alert-info p-2 m-0"><?php echo nl2br(htmlspecialchars($a['medical_notes'])); ?></div>
                            <?php else: ?>
                                <span class="text-muted">لا يوجد تقرير طبي بعد</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="bookModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="add_appointment.php" method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">طلب حجز موعد جديد</h5></div>
                <div class="modal-body">
                    <label>اختر الطبيب:</label>
                    <select name="doctor_id" class="form-control mb-2" required>
                        <?php foreach ($doctors as $d): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo $d['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>التاريخ والوقت:</label>
                    <input type="datetime-local" name="date" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">حفظ الموعد</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>     