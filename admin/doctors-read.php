<?php
// hospital/admin/doctors-read.php

// 1. بدء الجلسة وحماية الصفحة (صلاحيات المسؤول فقط)
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. تضمين ملف الاتصال بقاعدة البيانات
require_once '../config/db.php';

$doctors = [];

try {
    /* استعلام متقدم لربط الجداول (INNER JOIN):
       نربط جدول المستخدمين (u) بجدول تفاصيل الأطباء (dp) بجدول العيادات (c)
    */
    $sql = "SELECT 
                u.id AS doctor_id,
                u.name,
                u.email,
                dp.specialization,
                dp.image_url,
                c.name AS clinic_name
            FROM users u
            INNER JOIN doctor_profiles dp ON u.id = dp.user_id
            INNER JOIN clinics c     ON dp.clinic_id = c.id
            WHERE u.role = 'doctor'
            ORDER BY u.id DESC";

    $stmt = $pdo->query($sql);
    $doctors = $stmt->fetchAll();

} catch (PDOException $e) {
    die("حدث خطأ أثناء جلب بيانات الأطباء: " . $e->getMessage());
}

// 3. تضمين الهيدر المشترك
require_once '../includes/header.php';
?>

<div class="row">
    <!-- القائمة الجانبية للمسؤول (تمت إضافة زر إدارة المرضى وتحديث الروابط هنا أيضاً) -->
    <div class="col-md-3 mb-4">
        <div class="card main-card p-3 bg-white">
            <h5 class="text-secondary border-bottom pb-2 mb-3">إدارة النظام</h5>
            <ul class="nav flex-column nav-pills">
                <li class="nav-item mb-2">
                    <a class="nav-link text-dark" href="dashboard.php">📊 الرئـيسية</a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link active" href="doctors-read.php">👨‍⚕️ إدارة الأطباء (CRUD)</a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-dark" href="patients-read.php">👥 إدارة المرضى</a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-dark" href="clinics-read.php">🏥 إدارة العيادات</a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-dark" href="appointments-read.php">📅 استعراض المواعيد</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- المحتوى الرئيسي: جدول استعراض الأطباء -->
    <div class="col-md-9">
        <div class="p-4 bg-white rounded shadow-sm mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-dark">👨‍⚕️ إدارة الطاقم الطبي</h2>
                <p class="text-muted mb-0">يمكنك هنا استعراض الأطباء الحاليين، إضافة أطباء جدد، وتعديل أو حذف بياناتهم.</p>
            </div>
            <a href="doctors-create.php" class="btn btn-primary">➕ إضافة طبيب جديد</a>
        </div>

        <!-- جدول عرض البيانات -->
        <div class="card main-card p-3 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>الصورة</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>التخصص والعيادة</th>
                            <th>العمليات الإدارية</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($doctors)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">لا يوجد أطباء مسجلين في النظام حالياً.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($doctors as $doc): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $img_path = "../uploads/" . $doc['image_url'];
                                        if (!empty($doc['image_url']) && file_exists($img_path)) {
                                            $src = $img_path;
                                        } else {
                                            $src = "../uploads/default-doctor.png";
                                        }
                                        ?>
                                        <img src="<?php echo $src; ?>" alt="Doctor Image" class="rounded-circle border shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($doc['name']); ?></strong></td>
                                    <td><span class="text-muted"><?php echo htmlspecialchars($doc['email']); ?></span></td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?php echo htmlspecialchars($doc['specialization']); ?></span>
                                        <br><small class="text-muted">🏢 <?php echo htmlspecialchars($doc['clinic_name']); ?></small>
                                    </td>
                                    <td>
                                        <a href="doctors-update.php?id=<?php echo $doc['doctor_id']; ?>" class="btn btn-sm btn-outline-warning me-1">✏️ تعديل</a>
                                        <a href="doctors-delete.php?id=<?php echo $doc['doctor_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد تماماً من حذف هذا الطبيب من النظام نهائياً؟');">🗑️ حذف</a>
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
// تضمين الفوتر المشترك
require_once '../includes/footer.php'; 
?>