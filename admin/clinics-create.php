<?php
// hospital/admin/clinics-create.php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';
$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $floor = trim($_POST['floor']);

    if (empty($name) || empty($floor)) {
        $errors[] = "جميع الحقول مطلوبة.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO clinics (name, floor) VALUES (:name, :floor)");
            $stmt->execute(['name' => $name, 'floor' => $floor]);
            $success = "تم إضافة العيادة بنجاح!";
        } catch (PDOException $e) {
            $errors[] = "حدث خطأ: " . $e->getMessage();
        }
    }
}
require_once '../includes/header.php';
?>
<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <div class="card p-4 bg-white shadow-sm">
            <h3 class="text-dark mb-4">🏥 إضافة عيادة جديدة</h3>
            <?php if($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
            <?php if(!empty($errors)): ?> <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div> <?php endif; ?>
            <form method="POST">
                <div class="mb-3"><label class="form-label">اسم العيادة:</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">الموقع / الطابق:</label><input type="text" name="floor" class="form-control" required></div>
                <button type="submit" class="btn btn-primary">➕ حفظ العيادة</button>
                <a href="clinics-read.php" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>