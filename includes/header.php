<?php
// التأكد من أن الجلسة مفعلة لاستخدام $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشروع عيادة الشفاء الرقمي</title>
    <!-- استخدام Bootstrap للواجهات -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/hospital/index.php">عيادة الشفاء الرقمي</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- روابط المستخدم المسجل -->
                    <?php if ($_SESSION['user_role'] == 'patient'): ?>
                        <li class="nav-item"><a class="nav-link" href="/hospital/patient/dashboard.php">لوحة تحكم المريض</a></li>
                        <li class="nav-item"><a class="nav-link" href="/hospital/patient/profile.php">حسابي</a></li>
                    <?php elseif ($_SESSION['user_role'] == 'doctor'): ?>
                        <li class="nav-item"><a class="nav-link" href="/hospital/doctor/dashboard.php">لوحة تحكم الطبيب</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link btn btn-danger btn-sm text-white px-3" href="/hospital/logout.php">تسجيل الخروج</a></li>
                <?php else: ?>
                    <!-- روابط الزوار -->
                    <li class="nav-item"><a class="nav-link" href="/hospital/login.php">دخول</a></li>
                    <li class="nav-item"><a class="nav-link" href="/hospital/register.php">تسجيل مريض جديد</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="mt-4">
    <!-- بداية محتوى الصفحات -->