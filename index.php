<?php
// hospital/index.php

// 1. بدء الجلسة لتحديث حالة الأزرار العلوية تلقائياً
session_start();

// 2. تضمين ملف الاتصال بقاعدة البيانات لجلب العيادات المتاحة في المستشفى
require_once 'config/db.php';

$clinics = [];
try {
    // جلب بيانات العيادات وأماكنها (تطبيق استخدام الجدول الخامس في الواجهة)
    $stmt = $pdo->query("SELECT * FROM clinics ORDER BY id ASC");
    $clinics = $stmt->fetchAll();
} catch (PDOException $e) {
    // في حال عدم الاتصال بالقاعدة يعرض كرت فارغ بدون تعطيل الصفحة بالكامل
    $error_msg = "فشل جلب العيادات حالياً.";
}

// 3. تضمين الهيدر المشترك (المسار مباشر هنا لأننا في المجلد الرئيسي)
require_once 'includes/header.php';
?>

<div class="p-5 mb-5 bg-white rounded-3 shadow-sm border-start border-primary border-5">
    <div class="container-fluid py-3">
        <h1 class="display-5 fw-bold text-dark">مرحباً بكم في مستشفى الشفاء الرقمي</h1>
        <p class="col-md-9 fs-5 text-muted mt-3">
            نظامنا الإلكتروني المتكامل يتيح لكم حجز المواعيد الطبية، متابعة السجلات الصحية، والتواصل مع نخبة من أفضل الأطباء والاستشاريين بكل سهولة وأمان تـام.
        </p>
        
        <div class="d-flex gap-2 mt-4">
            <?php if (isset($_SESSION['user_role'])): ?>
                <a href="/hospital/logout.php" class="btn btn-danger btn-lg px-4">تسجيل الخروج</a>
                <?php if ($_SESSION['user_role'] == 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn btn-primary btn-lg px-4">لوحة تحكم المسؤول</a>
                <?php elseif ($_SESSION['user_role'] == 'doctor'): ?>
                    <a href="doctor/dashboard.php" class="btn btn-primary btn-lg px-4">لوحة تحكم الطبيب</a>
                <?php else: ?>
                    <a href="patient/dashboard.php" class="btn btn-primary btn-lg px-4">الانتقال لحسابي وحجوزاتي</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary btn-lg px-4">🏥 أنشئ حساب مريض الآن</a>
                <a href="login.php" class="btn btn-outline-secondary btn-lg px-4">تسجيل الدخول</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-12 text-center mb-4">
        <h3 class="fw-bold text-primary">عياداتنا وأقسامنا الطبية</h3>
        <p class="text-muted">نضم نخبة من العيادات المجهزة بأحدث التقنيات لخدمتكم</p>
    </div>

    <?php if (!empty($clinics)): ?>
        <?php foreach ($clinics as $clinic): ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100 main-card border-top border-info border-3 bg-white p-3">
                    <div class="card-body">
                        <h5 class="card-title text-dark fw-bold">🏢 <?php echo htmlspecialchars($clinic['name']); ?></h5>
                        <p class="card-text text-muted mt-2">موقع العيادة داخل المستشفى:</p>
                        <span class="badge bg-light text-dark p-2 fs-6 border w-100 text-start">📍 <?php echo htmlspecialchars($clinic['floor']); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center text-muted">
            <p>سيتم تحديث قائمة العيادات قريباً من قبل إدارة المستشفى.</p>
        </div>
    <?php endif; ?>
</div>

<div class="row text-center mt-5">
    <div class="col-md-4 mb-4">
        <div class="p-3 bg-light rounded shadow-sm">
            <div class="fs-1 mb-2">🔒 أمان وحصوصية</div>
            <h5>حماية البيانات</h5>
            <p class="text-muted small">تشفير كلمات المرور وحماية سجلاتك الطبية وفق أعلى المعايير الأمنية.</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="p-3 bg-light rounded shadow-sm">
            <div class="fs-1 mb-2">⚡ سرعة الحجز</div>
            <h5>مواعيد فورية</h5>
            <p class="text-muted small">اختر طبيبك وتوقيتك المفضل وقدم طلب الحجز في أقل من دقيقة واحدة.</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="p-3 bg-light rounded shadow-sm">
            <div class="fs-1 mb-2">📊 لوحات تحكم</div>
            <h5>توزيع الصلاحيات</h5>
            <p class="text-muted small">لوحات تحكم منفصلة للمشرفين، الأطباء، والمرضى لإدارة العمل بسلاسة.</p>
        </div>
    </div>
</div>

<?php 
// تضمين الفوتر المشترك لغلق وسوم الصفحة بالكامل
require_once 'includes/footer.php'; 
?>