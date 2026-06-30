<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

// إذا كان المستخدم مسجلاً دخوله بالفعل، وجهه للوحة التحكم
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['user_role'] == 'doctor' ? 'doctor/dashboard.php' : 'patient/dashboard.php'));
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // جلب بيانات المستخدم من قاعدة البيانات
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // التحقق باستخدام password_verify (ضروري لأننا استخدمنا password_hash في التسجيل)
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        
        // التوجيه الذكي حسب الدور
        if ($user['role'] == 'doctor') {
            header("Location: doctor/dashboard.php");
        } else {
            header("Location: patient/dashboard.php");
        }
        exit();
    } else {
        $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
    }
}
?>

<div class="container mt-5">
    <div class="card shadow-sm col-md-5 mx-auto">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="mb-0">تسجيل الدخول - عيادة الشفاء الرقمي</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">تسجيل الدخول</button>
            </form>
            
            <div class="mt-3 text-center">
                <p>ليس لديك حساب؟ <a href="register.php">سجل كمريض جديد</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>