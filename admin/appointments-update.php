<?php
// hospital/admin/appointments-update.php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: appointments-read.php");
    exit();
}

$app_id = $_GET['id'];
$error = '';
$success = '';

// جلب بيانات الموعد الحالي مع أسماء المريض والطبيب
try {
    $stmt = $pdo->prepare("SELECT a.*, p.name AS patient_name, d.name AS doctor_name 
                           FROM appointments a
                           INNER JOIN users p ON a.patient_id = p.id
                           INNER JOIN users d ON a.doctor_id = d.id
                           WHERE a.id = ?");
    $stmt->execute([$app_id]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        die("الموعد غير موجود في النظام.");
    }
} catch (PDOException $e) {
    die("خطأ في جلب البيانات: " . $e->getMessage());
}

// معالجة تحديث الحالة عند إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];
    
    if (in_array($new_status, ['pending', 'completed', 'cancelled'])) {
        try {
            $update_stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $update_stmt->execute([$new_status, $app_id]);
            $success = "تم تحديث حالة الموعد بنجاح!";
            // تحديث البيانات المعروضة في الصفحة
            $appointment['status'] = $new_status;
        } catch (PDOException $e) {
            $error = "حدث خطأ أثناء التحديث: " . $e->getMessage();
        }
    } else {
        $error = "حالة الموعد غير صالحة.";
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card p-4 bg-white shadow-sm rounded">
            <h3 class="text-dark mb-3">✏️ تعديل حالة الحجز</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item"><strong>المريض:</strong> <?php echo htmlspecialchars($appointment['patient_name']); ?></li>
                <li class="list-group-item"><strong>الطبيب المعالج:</strong> <?php echo htmlspecialchars($appointment['doctor_name']); ?></li>
                <li class="list-group-item"><strong>التاريخ والوقت:</strong> <?php echo $appointment['appointment_date'] . ' | ' . date('h:i A', strtotime($appointment['appointment_time'])); ?></li>
            </ul>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">اختر الحالة الجديدة للموعد:</label>
                    <select name="status" class="form-select">
                        <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'selected' : ''; ?>>⏳ قيد الانتظار</option>
                        <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>✅ تمت الزيارة (مكتمل)</option>
                        <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>❌ ملغي</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-2">حفظ التغييرات</button>
                <a href="appointments-read.php" class="btn btn-light w-100">⬅️ العودة لسجل المواعيد</a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>