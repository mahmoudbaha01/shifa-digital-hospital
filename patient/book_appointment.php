<?php
session_start();
require_once '../includes/header.php';
require_once '../config/db.php';

// التأكد من أن المستخدم مريض
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

// معالجة البيانات داخل نفس الصفحة
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $patient_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$patient_id, $doctor_id, $appointment_date]);
    
    // إعادة توجيه بعد الحفظ
    header("Location: dashboard.php");
    exit();
}

// جلب قائمة الأطباء
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'doctor'");
$doctors = $stmt->fetchAll();
?>

<div class="container mt-5">
    <div class="card shadow-sm col-md-8 mx-auto">
        <div class="card-header bg-primary text-white">
            <h4>حجز موعد جديد</h4>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">اختر الطبيب</label>
                    <select name="doctor_id" class="form-select" required>
                        <?php foreach ($doctors as $d): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">تاريخ الموعد</label>
                    <input type="datetime-local" name="appointment_date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">تأكيد الحجز</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>