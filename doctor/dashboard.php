<?php
session_start();
require_once '../config/db.php';
require_once '../includes/header.php';

// التأكد من أن المستخدم طبيب
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$stmt = $pdo->query("SELECT a.*, u.name as patient_name FROM appointments a 
                     JOIN users u ON a.patient_id = u.id");
$appointments = $stmt->fetchAll();
?>

<div class="container mt-5">
    <h2>لوحة تحكم الطبيب - مشروع عيادة الشفاء الرقمي</h2>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>المريض</th>
                <th>التاريخ</th>
                <th>الحالة</th>
                <th>الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $a): ?>
            <tr>
                <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                <td><?php echo $a['appointment_date']; ?></td>
                <td>
                    <span class="badge bg-<?php echo ($a['status'] == 'completed') ? 'success' : 'warning'; ?>">
                        <?php echo $a['status']; ?>
                    </span>
                </td>
                <td>
                    <form action="update_appointment_status.php" method="POST" class="d-flex">
                        <input type="hidden" name="appointment_id" value="<?php echo $a['id']; ?>">
                        <select name="status" class="form-select form-select-sm me-2">
                            <option value="pending" <?php if($a['status'] == 'pending') echo 'selected'; ?>>قيد الانتظار</option>
                            <option value="confirmed" <?php if($a['status'] == 'confirmed') echo 'selected'; ?>>مؤكد</option>
                            <option value="completed" <?php if($a['status'] == 'completed') echo 'selected'; ?>>مكتمل</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">تحديث</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>