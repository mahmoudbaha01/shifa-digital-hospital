<?php
// hospital/admin/clinics-update.php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';
$id = intval($_GET['id'] ?? 0);
$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $floor = trim($_POST['floor']);
    try {
        $stmt = $pdo->prepare("UPDATE clinics SET name = :name, floor = :floor WHERE id = :id");
        $stmt->execute(['name' => $name, 'floor' => $floor, 'id' => $id]);
        $success = "تم تحديث بيانات العيادة بنجاح!";
    } catch (PDOException $e) { $errors[] = $e->getMessage(); }
}

$stmt = $pdo->prepare("SELECT * FROM clinics WHERE id = :id");
$stmt->execute(['id' => $id]);
$clinic = $stmt->fetch();
if(!$clinic) { die("العيادة غير موجودة."); }

require_once '../includes/header.php';
?>
<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <div class="card p-4 bg-white shadow-sm">
            <h3 class="text-dark mb-4">✏️ تعديل العيادة</h3>
            <?php if($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
            <form method="POST">
                <div class="mb-3"><label class="form-label">اسم العيادة:</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($clinic['name']); ?>" required></div>
                <div class="mb-3"><label class="form-label">الموقع / الطابق:</label><input type="text" name="floor" class="form-control" value="<?php echo htmlspecialchars($clinic['floor']); ?>" required></div>
                <button type="submit" class="btn btn-warning">💾 تحديث</button>
                <a href="clinics-read.php" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>