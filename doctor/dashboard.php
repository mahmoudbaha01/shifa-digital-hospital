<?php
// doctor/dashboard.php
session_start();
require_once '../config/db.php';

// حماية الصفحة: يسمح فقط للطبيب أو الإدمن بالدخول
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'doctor' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: ../login.php");
    exit();
}

$user_name = $_SESSION['user_name'];
$role = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الطبيب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { height: 100vh; background: #2c3e50; color: white; position: fixed; width: 250px; }
        .sidebar a { color: white; text-decoration: none; padding: 15px; display: block; transition: 0.3s; }
        .sidebar a:hover { background: #34495e; }
        .sidebar .active { background: #3498db; }
        .content { margin-right: 250px; padding: 20px; }
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="text-center py-4">
            <h4>مستشفى الأمل</h4>
            <small class="badge bg-info"><?php echo ($role === 'admin' ? 'إدمن' : 'طبيب'); ?></small>
        </div>
        <hr>
        <a href="dashboard.php" class="active"><i class="fas fa-home"></i> الرئيسية</a>
        <a href="manage_patients.php"><i class="fas fa-user-injured"></i> إدارة المرضى</a>
        <a href="manage_appointments.php"><i class="fas fa-calendar-check"></i> إدارة المواعيد</a>
        
        <?php if ($role === 'admin'): ?>
            <a href="../admin/manage_clinics.php"><i class="fas fa-hospital"></i> إدارة العيادات (إدمن فقط)</a>
        <?php endif; ?>

        <hr>
        <a href="../logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>أهلاً بك  <?php echo $user_name; ?></h2>
                <span class="text-muted"><?php echo date('Y-m-d'); ?></span>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card card-custom bg-white p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary text-white p-3 rounded-circle me-3">
                                <i class="fas fa-calendar-day fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">مواعيد اليوم</h5>
                                <h3 class="fw-bold">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card card-custom bg-white p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success text-white p-3 rounded-circle me-3">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">إجمالي المرضى</h5>
                                <h3 class="fw-bold">1</h3>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
        </div>
    </div>

</body>
</html>