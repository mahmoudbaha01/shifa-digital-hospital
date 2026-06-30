<?php
// hospital/admin/appointments-create.php

// 1. بدء الجلسة وحماية الصفحة (صلاحيات المسؤول فقط)
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. تضمين ملف الاتصال بقاعدة البيانات
require_once '../config/db.php';

$errors = [];
$success = '';

// 3. جلب قائمة المرضى وقائمة الأطباء لتعليقهم في قوائم الاختيار (Select Dropdowns)
try {
    // جلب المرضى
    $patients_stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'patient' ORDER BY name ASC");
    $patients = $patients_stmt->fetchAll();

    // جلب الأطباء
    $doctors_stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'doctor' ORDER BY name ASC");
    $doctors = $doctors_stmt->fetchAll();
} catch (PDOException $e) {
    die("حدث خطأ أثناء جلب القوائم: " . $e->getMessage());
}

// 4. معالجة إرسال النموذج وحفظ الموعد (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = intval($_POST['patient_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $app_date = $_POST['appointment_date'];
    $app_time = $_POST['appointment_time'];

    // التحقق من المدخلات
    if (empty($patient_id) || empty($doctor_id) || empty($app_date) || empty($app_time)) {
        $errors[] = "جميع الحقول إجبارية لتسجيل الحجز الطبي.";
    }

    // إدخال الموعد الجديد بحالة افتراضية 'pending' (قيد الانتظار)
    if (empty($errors)) {
        try {
            $insert_stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, 'pending')");
            $insert_stmt->execute([$patient_id, $doctor_id, $app_date, $app_time]);
            
            $success = "تم حجز وتجديد الموعد بنجاح داخل النظام الجراحي!";
        } catch (PDOException $e) {
            $errors[] = "فشل تسجيل الموعد: " . $e->getMessage();
        }
    }
}

// 5. تضمين الهيدر المشترك
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card main-card p-3 bg-white">
            <h5 class="text-secondary border-bottom pb-2 mb-3">إدارة النظام</h5>
            <ul class="nav flex-column nav-pills">
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="dashboard.php">📊 الرئـيسية</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="doctors-read.php">👨‍⚕️ إدارة الأطباء (CRUD)</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="patients-read.php">👥 إدارة المرضى</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="clinics-read.php">🏥 إدارة العيادات</a></li>
                <li class="nav-item mb-2"><a class="nav-link active" href="appointments-read.php">📅 استعراض المواعيد</a></li>
            </ul>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card main-card p-4 bg-white shadow-sm">
            <h2 class="text-dark mb-1">➕ إضافة وحجز موعد جديد</h2>
            <p class="text-muted mb-4">بصفتك المسؤول، يمكنك جدولة حجز طبي جديد وتعيين المريض للطبيب مباشرة.</p>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>

            <form method="POST" action="appointments-create.php">
                <div class="mb-3">
                    <label class="form-label">اختر المريض:</label>
                    <select name="patient_id" class="form-select" required>
                        <option value="">-- حدد اسم المريض المعني --</option>
                        <?php foreach ($patients as $pat): ?>
                            <option value="<?php echo $pat['id']; ?>"><?php echo htmlspecialchars($pat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">اختر الطبيب المعالج:</label>
                    <select name="doctor_id" class="form-select" required>
                        <option value="">-- حدد الطبيب المتابع --</option>
                        <?php foreach ($doctors as $doc): ?>
                            <option value="<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">تاريخ الزيارة:</label>
                        <input type="date" name="appointment_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">الوقت المحجوز:</label>
                        <input type="time" name="appointment_time" class="form-control" required>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning text-dark px-4 font-weight-bold">💾 جدولة وحفظ الموعد</button>
                    <a href="appointments-read.php" class="btn btn-secondary px-4">⬅️ إلغاء وعودة</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>