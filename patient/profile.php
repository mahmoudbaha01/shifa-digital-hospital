<?php
session_start();
require_once '../config/db.php';
require_once '../includes/header.php';

// التأكد من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// جلب بيانات المريض
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="container mt-5">
    <div class="card shadow-sm col-md-6 mx-auto">
        <div class="card-header bg-primary text-white">
            <h4>بيانات حسابي</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label text-muted">الاسم الكامل:</label>
                <p class="fs-5"><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <hr>
            <div class="mb-3">
                <label class="form-label text-muted">البريد الإلكتروني:</label>
                <p class="fs-5"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <hr>
            <a href="dashboard.php" class="btn btn-secondary">العودة للوحة التحكم</a>
            <a href="../logout.php" class="btn btn-danger">تسجيل الخروج</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>