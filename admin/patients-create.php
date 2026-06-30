<?php
// hospital/admin/patients-create.php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $errors[] = "جميع الحقول إجبارية.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "صيغة البريد الإلكتروني غير صحيحة.";
    }

    if (empty($errors)) {
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->execute([$email]);
        if ($check_stmt->fetch()) {
            $errors[] = "هذا البريد الإلكتروني مسجل بالفعل.";
        }
    }

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'patient')");
            $insert_stmt->execute([$name, $email, $hashed_password]);
            $success = "تم إضافة المريض بنجاح!";
        } catch (PDOException $e) {
            $errors[] = "حدث خطأ أثناء الحفظ: " . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card main-card p-3 bg-white">
            <h5 class="text-secondary border-bottom pb-2 mb-3">إدارة النظام</h5>
            <ul class="nav flex-column nav-pills">
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="dashboard.php">📊 الرئـيسية</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="doctors-read.php">👨‍⚕️ إدارة الأطباء (CRUD)</a></li>
                <li class="nav-item mb-2"><a class="nav-link active" href="patients-read.php">👥 إدارة المرضى</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="clinics-read.php">🏥 إدارة العيادات</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="appointments-read.php">📅 استعراض المواعيد</a></li>
            </ul>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card main-card p-4 bg-white shadow-sm">
            <h2 class="text-dark mb-1">➕ إضافة مريض جديد للنظام</h2>
            <p class="text-muted mb-4">قم بتعبئة البيانات أدناه لإنشاء حساب جديد للمريض مباشرة.</p>

            <?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
            <?php if (!empty($errors)): ?> <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div> <?php endif; ?>

            <form method="POST" action="patients-create.php" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">الاسم الكامل للمريض:</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">كلمة المرور:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success px-4">💾 حفظ بيانات المريض</button>
                    <a href="patients-read.php" class="btn btn-secondary px-4">⬅️ إلغاء والعودة</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>