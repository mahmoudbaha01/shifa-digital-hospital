<?php
// hospital/admin/patients-read.php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';
$patients = [];

try {
    $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE role = 'patient' ORDER BY id DESC");
    $stmt->execute();
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    die("حدث خطأ أثناء جلب سجلات المرضى: " . $e->getMessage());
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
        <div class="p-4 bg-white rounded shadow-sm mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-dark">👥 إدارة سجلات المرضى</h2>
                <p class="text-muted mb-0">لوحة التحكم الكاملة لمتابعة الحسابات المسجلة للمرضى وتعديل بياناتهم أو حذفهم.</p>
            </div>
            <a href="patients-create.php" class="btn btn-success">➕ إضافة مريض جديد</a>
        </div>

        <div class="card main-card p-3 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>رقم المريض</th>
                            <th>الاسم الكامل</th>
                            <th>البريد الإلكتروني</th>
                            <th>تاريخ التسجيل</th>
                            <th>العمليات الإدارية</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">لا يوجد مرضى مسجلين في النظام حالياً.</td></tr>
                        <?php else: ?>
                            <?php foreach ($patients as $patient): ?>
                                <tr>
                                    <td><span class="badge bg-dark">#<?php echo $patient['id']; ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($patient['name']); ?></strong></td>
                                    <td>📩 <?php echo htmlspecialchars($patient['email']); ?></td>
                                    <td>📅 <?php echo date('Y-m-d', strtotime($patient['created_at'])); ?></td>
                                    <td>
                                        <a href="patients-update.php?id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-outline-warning me-1">✏️ تعديل</a>
                                        <a href="patients-delete.php?id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف حساب المريض؟');">🗑️ حذف</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>