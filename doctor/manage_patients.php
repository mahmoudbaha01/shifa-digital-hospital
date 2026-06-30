<?php
session_start();
// التأكد من المسار الصحيح للملف
require_once '../config/db.php';

// حماية الصفحة
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

// 1. معالجة العمليات (إضافة، تحديث، حذف)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // إضافة موعد جديد
    if (isset($_POST['add_appointment'])) {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, appointment_date, status, medical_notes) VALUES (?, ?, 'pending', ?)");
        $stmt->execute([$_POST['patient_id'], $_POST['date'], $_POST['notes']]);
        header("Location: manage_appointments.php");
        exit();
    }

    // تحديث الحالة
    if (isset($_POST['update_status'])) {
        $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?")->execute([$_POST['status'], $_POST['appointment_id']]);
        header("Location: manage_appointments.php");
        exit();
    }

    // تحديث الملاحظات
    if (isset($_POST['update_notes'])) {
        $pdo->prepare("UPDATE appointments SET medical_notes = ? WHERE id = ?")->execute([$_POST['notes'], $_POST['appointment_id']]);
        header("Location: manage_appointments.php");
        exit();
    }
}

// حذف الموعد
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM appointments WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_appointments.php");
    exit();
}

// جلب البيانات
$appointments = $pdo->query("SELECT a.*, u.name as patient_name FROM appointments a JOIN users u ON a.patient_id = u.id")->fetchAll();
$patients = $pdo->query("SELECT id, name FROM users WHERE role = 'patient'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>.sidebar { height: 100vh; background: #2c3e50; color: white; position: fixed; width: 250px; } .content { margin-right: 250px; padding: 20px; }</style>
</head>
<body>
    <div class="sidebar">
        <h4 class="text-center py-3">لوحة الطبيب</h4>
        <a href="dashboard.php" class="text-white p-3 d-block text-decoration-none">الرئيسية</a>
        <a href="manage_appointments.php" class="bg-primary text-white p-3 d-block text-decoration-none">إدارة المواعيد</a>
    </div>

    <div class="content">
        <div class="card p-4">
            <div class="d-flex justify-content-between mb-4">
                <h3>إدارة المواعيد والملاحظات</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addApptModal"><i class="fas fa-plus"></i> حجز جديد</button>
            </div>
            <table class="table table-hover align-middle">
                <thead class="table-dark"><tr><th>المريض</th><th>التاريخ</th><th>الحالة</th><th>الملاحظات</th><th>إجراء</th></tr></thead>
                <tbody>
                    <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($a['appointment_date'])); ?></td>
                        <td>
                            <form method="POST"><input type="hidden" name="appointment_id" value="<?php echo $a['id']; ?>">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="pending" <?php if($a['status']=='pending') echo 'selected'; ?>>قيد الانتظار</option>
                                    <option value="confirmed" <?php if($a['status']=='confirmed') echo 'selected'; ?>>تم الكشف</option>
                                </select><input type="hidden" name="update_status" value="1"></form>
                        </td>
                        <td>
                            <form method="POST"><input type="hidden" name="appointment_id" value="<?php echo $a['id']; ?>">
                                <textarea name="notes" class="form-control" onblur="this.form.submit()"><?php echo htmlspecialchars($a['medical_notes']); ?></textarea>
                                <input type="hidden" name="update_notes" value="1"></form>
                        </td>
                        <td><a href="?delete=<?php echo $a['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('حذف؟')"><i class="fas fa-trash"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addApptModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">حجز موعد جديد</h5></div>
                <div class="modal-body">
                    <select name="patient_id" class="form-control mb-2" required>
                        <?php foreach ($patients as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="datetime-local" name="date" class="form-control mb-2" required>
                    <textarea name="notes" class="form-control" placeholder="ملاحظات أولية..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_appointment" class="btn btn-primary">حفظ الموعد</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>