<?php
// hospital/admin/dashboard.php

session_start();

// التحقق من أن المستخدم مسجل دخوله وأن صلاحيته "admin" حصراً
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // طرد المستخدم وتوجيهه لصفحة الدخول في حال التلاعب بالروابط
    header("Location: ../login.php");
    exit();
}

// 2. تضمين ملف الاتصال بقاعدة البيانات لجلب الإحصائيات
require_once '../config/db.php';

try {
    // جلب إجمالي عدد العيادات (الجدول الخامس)
    $stmt_clinics = $pdo->query("SELECT COUNT(*) FROM clinics");
    $total_clinics = $stmt_clinics->fetchColumn();

    // جلب إجمالي عدد الأطباء (من جدول المستخدمين حيث الدور يساوي doctor)
    $stmt_doctors = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'");
    $total_doctors = $stmt_doctors->fetchColumn();

    // جلب إجمالي عدد المرضى (من جدول المستخدمين حيث الدور يساوي patient)
    $stmt_patients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'");
    $total_patients = $stmt_patients->fetchColumn();

    // جلب إجمالي عدد المواعيد المحجوزة في النظام
    $stmt_appointments = $pdo->query("SELECT COUNT(*) FROM appointments");
    $total_appointments = $stmt_appointments->fetchColumn();

} catch (PDOException $e) {
    die("حدث خطأ أثناء جلب إحصائيات اللوحة: " . $e->getMessage());
}

// 3. تضمين الهيدر المشترك
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card main-card p-3 bg-white">
            <h5 class="text-secondary border-bottom pb-2 mb-3">إدارة النظام</h5>
            <ul class="nav flex-column nav-pills">
                <li class="nav-item mb-2">
                    <a class="nav-link active" href="dashboard.php">📊 الرئـيسية</a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-dark" href="doctors-read.php">👨‍⚕️ إدارة الأطباء (CRUD)</a>
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

    <!-- المحتوى الرئيسي للوحة -->
    <div class="col-md-9">
        <div class="p-4 bg-white rounded shadow-sm mb-4">
            <h2 class="text-dark">لوحة تحكم المسؤول العام</h2>
            <p class="text-muted">مرحباً بك في نظام الإشراف والمتابعة الطبية. يمكنك من هنا متابعة كافة مؤشرات وإحصائيات النظام الحية.</p>
        </div>

        <div class="row">
            <!-- بطاقة العيادات -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-primary main-card p-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase small">إجمالي العيادات</h6>
                            <h2 class="font-weight-bold mb-0"><?php echo $total_clinics; ?></h2>
                        </div>
                        <div class="fs-1">🏥</div>
                    </div>
                </div>
            </div>

            <!-- بطاقة الأطباء -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-success main-card p-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase small">الأطباء المسجلين</h6>
                            <h2 class="font-weight-bold mb-0"><?php echo $total_doctors; ?></h2>
                        </div>
                        <div class="fs-1">👨‍⚕️</div>
                    </div>
                </div>
            </div>

            <!-- بطاقة المرضى الجديدة -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-info main-card p-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase small text-dark">المرضى المسجلين</h6>
                            <h2 class="font-weight-bold text-dark mb-0"><?php echo $total_patients; ?></h2>
                        </div>
                        <div class="fs-1 text-dark">👥</div>
                    </div>
                </div>
            </div>

            <!-- بطاقة الحجوزات -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-warning main-card p-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase small text-dark">إجمالي الحجوزات</h6>
                            <h2 class="font-weight-bold text-dark mb-0"><?php echo $total_appointments; ?></h2>
                        </div>
                        <div class="fs-1 text-dark">📅</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// تضمين الفوتر المشترك لغلق وسوم الصفحة
require_once '../includes/footer.php'; 
?>