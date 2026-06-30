<?php
// hospital/admin/clinics-read.php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';
$clinics = [];

try {
    $stmt = $pdo->query("SELECT * FROM clinics ORDER BY id ASC");
    $clinics = $stmt->fetchAll();
} catch (PDOException $e) {
    die("حدث خطأ أثناء جلب العيادات: " . $e->getMessage());
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
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="patients-read.php">👥 إدارة المرضى</a></li>
                <li class="nav-item mb-2"><a class="nav-link active" href="clinics-read.php">🏥 إدارة العيادات</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="appointments-read.php">📅 استعراض المواعيد</a></li>
            </ul>
        </div>
    </div>

    <div class="col-md-9">
        <div class="p-4 bg-white rounded shadow-sm mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-dark">🏥 أقسام وعيادات المستشفى</h2>
                <p class="text-muted mb-0">لوحة التحكم الكاملة لإضافة، تعديل، وحذف عيادات وأقسام المستشفى.</p>
            </div>
            <a href="clinics-create.php" class="btn btn-primary">➕ إضافة عيادة جديدة</a>
        </div>

        <div class="card main-card p-3 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>رقم العيادة</th>
                            <th>اسم العيادة الطبية</th>
                            <th>الموقع / الطابق</th>
                            <th>العمليات الإدارية الكاملة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clinics as $clinic): ?>
                            <tr>
                                <td><span class="badge bg-secondary">#<?php echo $clinic['id']; ?></span></td>
                                <td><strong><?php echo htmlspecialchars($clinic['name']); ?></strong></td>
                                <td>📍 <?php echo htmlspecialchars($clinic['floor']); ?></td>
                                <td>
                                    <a href="clinics-update.php?id=<?php echo $clinic['id']; ?>" class="btn btn-sm btn-outline-warning me-1">✏️ تعديل</a>
                                    <a href="clinics-delete.php?id=<?php echo $clinic['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذه العيادة نهائياً؟');">🗑️ حذف</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>