<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'patient'; // المريض يسجل دائماً بهذه الصلاحية

    // التأكد من عدم تكرار البريد الإلكتروني
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        $error = "هذا البريد الإلكتروني مسجل مسبقاً!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
        header("Location: login.php?msg=success");
        exit();
    }
}
?>

<div class="container mt-5">
    <div class="card shadow-sm col-md-6 mx-auto">
        <div class="card-header bg-primary text-white text-center">
            <h4>تسجيل مريض جديد - مشروع عيادة الشفاء الرقمي</h4>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">الاسم الكامل</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">إنشاء حساب</button>
                <div class="mt-3 text-center">
                    <a href="login.php">لديك حساب بالفعل؟ سجل دخولك</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>