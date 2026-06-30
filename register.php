<?php
// hospital/register.php

// بدء الجلسة
session_start();

// تضمين ملف الاتصال بقاعدة البيانات
require_once 'config/db.php';

// مصفوفة لتخزين رسائل الأخطاء ومغير لرسالة النجاح
$errors = [];
$success_msg = '';

// التحقق من إرسال النموذج (Form Submission)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. استقبال المدخلات وتطهيرها من المسافات الزائدة (Sanitization)
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // 2. عمليات التحقق من صحة المدخلات (Validation)
    if (empty($name)) {
        $errors[] = "الاسم الكامل مطلوب.";
    }

    if (empty($email)) {
        $errors[] = "البريد الإلكتروني مطلوب.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "صيغة البريد الإلكتروني غير صحيحة.";
    }

    if (empty($password)) {
        $errors[] = "كلمة المرور مطلوبة.";
    } elseif (strlen($password) < 6) {
        $errors[] = "يجب ألا تقل كلمة المرور عن 6 خانات.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "كلمتا المرور غير متطابقتين.";
    }

    // 3. إذا لم تكن هناك أخطاء، نتحقق من عدم تكرار البريد الإلكتروني في القاعدة
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            
            if ($stmt->fetch()) {
                $errors[] = "هذا البريد الإلكتروني مسجل بالفعل في النظام.";
            } else {
                // 4. تشفير كلمة المرور وتخزين البيانات بأمان (Security)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // إدخال المستخدم الجديد كـ "patient" (مريض / مستخدم عادي)
                $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'patient')");
                $insert_stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashed_password
                ]);

                $success_msg = "تم إنشاء الحساب بنجاح! يمكنك الآن <a href='login.php' class='alert-link'>تسجيل الدخول</a>.";
                
                // تفريغ الحقول بعد النجاح
                $name = $email = '';
            }
        } catch (PDOException $e) {
            $errors[] = "حدث خطأ غير متوقع في النظام: " . $e->getMessage();
        }
    }
}

// تضمين الهيدر المشترك (يحتوي على بداية تصميم الـ Bootstrap والـ Navbar)
require_once 'includes/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-md-6">
        <div class="card main-card p-4">
            <h3 class="text-center text-primary mb-4">إنشاء حساب مريض جديد</h3>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label">الاسم الكامل:</label>
                    <input type="text" name="name" class="form-dash-control form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" placeholder="أدخل اسمك الثلاثي" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني:</label>
                    <input type="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="name@example.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">كلمة المرور:</label>
                    <input type="password" name="password" class="form-control" placeholder="اختر كلمة مرور قوية" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">تأكيد كلمة المرور:</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="أعد كتابة كلمة المرور" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fs-5">إنشاء الحساب</button>
            </form>

            <div class="text-center mt-3">
                <p class="text-muted">لديك حساب بالفعل؟ <a href="login.php" class="text-decoration-none">سجل دخولك من هنا</a></p>
            </div>
        </div>
    </div>
</div>

<?php 
// تضمين الفوتر المشترك لغلق وسوم الـ HTML واستدعاء الـ JS
require_once 'includes/footer.php'; 
?>