<?php
// hospital/admin/appointments-read.php

// 1. بدء الجلسة وحماية الصفحة (صلاحيات المسؤول فقط)
session_start();

// التأكد من أن المستخدم مسجل دخوله وصلاحيته "admin"
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. تضمين ملف الاتصال بقاعدة البيانات
require_once '../config/db.php';
$appointments = [];

try {
    // استعلام لربط المواعيد بجداول المستخدمين لمعرفة اسم المريض واسم الطبيب المعالج
    $sql = "SELECT 
                a.id AS app_id,
                a.appointment_date,
                a.appointment_time,
                a.status,
                p.name AS patient_name,
                d.name AS doctor_name
            FROM appointments a
            INNER JOIN users p ON a.patient_id = p.id
            INNER JOIN users d ON a.doctor_id = d.id
            ORDER BY a.appointment_date DESC";
            
    $stmt = $pdo->query($sql);
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    die("حدث خطأ أثناء جلب المواعيد: " . $e->getMessage());
}

// 3. تضمين الهيدر المشترك للتصميم
require_once '../includes/header.php';
?>

<div class="row">
    <!-- القائمة الجانبية للمسؤول -->
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

    <!-- المحتوى الرئيسي: استعراض وإدارة المواعيد بالكامل -->
    <div class="col-md-9">
        <div class="p-4 bg-white rounded shadow-sm mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-dark mb-1">📅 سجل المواعيد والحجوزات العام</h2>
                <p class="text-muted mb-0">صلاحيات كاملة للمسؤول لمتابعة حالة الحجوزات، إضافتها، تعديلها، أو حذفها من النظام.</p>
            </div>
            <!-- زر إضافة موعد جديد الممنوح للمسؤول (C in CRUD) -->
            <a href="appointments-create.php" class="btn btn-warning text-dark font-weight-bold shadow-sm">➕ إضافة موعد جديد</a>
        </div>

        <!-- جدول عرض المواعيد والحجوزات -->
        <div class="card main-card p-3 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>اسم المريض</th>
                            <th>الطبيب المعالج</th>
                            <th>التاريخ</th>
                            <th>الوقت</th>
                            <th>الحالة</th>
                            <th>العمليات الإدارية</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">لا توجد حجوزات في النظام حالياً.</td></tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $app): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($app['patient_name']); ?></strong></td>
                                    <td>👨‍⚕️ <?php echo htmlspecialchars($app['doctor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($app['appointment_date']); ?></td>
                                    <td><?php echo date('h:i A', strtotime($app['appointment_time'])); ?></td>
                                    <td>
                                        <?php 
                                        // استخدام الصياغة التقليدية لـ if لتجنب أخطاء ومشاكل التداخل في الوسوم
                                        if ($app['status'] == 'pending') {
                                            echo '<span class="badge bg-warning text-dark">⏳ قيد الانتظار</span>';
                                        } elseif ($app['status'] == 'completed') {
                                            echo '<span class="badge bg-success">✅ تمت الزيارة</span>';
                                        } else {
                                            echo '<span class="badge bg-danger">❌ ملغي</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <!-- أزرار التحكم الكامل للمسؤول في المواعيد (تحديث وحذف) -->
                                        <a href="appointments-update.php?id=<?php echo $app['app_id']; ?>" class="btn btn-sm btn-outline-warning me-1">✏️ إدارة الحالات</a>
                                        <a href="appointments-delete.php?id=<?php echo $app['app_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد تماماً من حذف هذا الموعد من السجلات نهائياً؟');">🗑️ حذف الموعد</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
// تضمين الفوتر المشترك لغلق وسوم الصفحة
require_once '../includes/footer.php'; 
?>