<?php
// hospital/includes/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // التأكد من بدء الجلسة في جميع الصفحات
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة المستشفى الذكي</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; color: #0d6efd !important; }
        .main-card { border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 10px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand" href="/hospital/index.php">🏥 مستشفى الشفاء</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/hospital/index.php">الرئيسية</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_role'])): ?>
                    <span class="me-3 text-secondary">مرحباً، <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                    
                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <a href="/hospital/admin/dashboard.php" class="btn btn-sm btn-outline-primary me-2">لوحة التحكم</a>
                    <?php elseif ($_SESSION['user_role'] == 'doctor'): ?>
                        <a href="/hospital/doctor/dashboard.php" class="btn btn-sm btn-outline-primary me-2">لوحة الطبيب</a>
                    <?php else: ?>
                        <a href="/hospital/patient/dashboard.php" class="btn btn-sm btn-outline-primary me-2">حسابي</a>
                    <?php endif; ?>
                    
                    <a href="/hospital/logout.php" class="btn btn-sm btn-danger">تسجيل الخروج</a>
                <?php else: ?>
                    <a href="/hospital/login.php" class="btn btn-sm btn-outline-primary me-2">تسجيل الدخول</a>
                    <a href="/hospital/register.php" class="btn btn-sm btn-primary">إنشاء حساب</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container">