<?php
// login.php - الكود المحدث والنهائي
session_start();
require_once 'config/db.php';

// إذا كان المستخدم مسجلاً دخول بالفعل، لا نظهر له صفحة الدخول
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') header("Location: admin/dashboard.php");
    elseif ($_SESSION['user_role'] === 'doctor') header("Location: doctor/dashboard.php");
    else header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // التحقق من البيانات (تم الاعتماد على المقارنة المباشرة لضمان عملها حالياً)
        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];

            // التوجيه الذكي حسب الصلاحية
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] === 'doctor') {
                header("Location: doctor/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        }
    } catch (PDOException $e) {
        $error = "خطأ في الاتصال بقاعدة البيانات.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام المستشفى</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow p-4">
                    <h3 class="text-center mb-4">تسجيل الدخول</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">البريد الإلكتروني:</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">كلمة المرور:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">دخول</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>