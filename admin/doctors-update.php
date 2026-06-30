<?php
// hospital/admin/doctors-update.php

// 1. بدء الجلسة وحماية الصفحة
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. تضمين ملف الاتصال بقاعدة البيانات
require_once '../config/db.php';

$errors = [];
$success_msg = '';

// التحقق من وجود ID الطبيب في الرابط
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: doctors-read.php");
    exit();
}

$doctor_id = $_GET['id'];

try {
    // جلب العيادات المتاحة للقائمة المنسدلة
    $clinics_stmt = $pdo->query("SELECT * FROM clinics");
    $clinics = $clinics_stmt->fetchAll();

    // جلب البيانات الحالية للطبيب المستهدف ليتم عرضها في الحقول
    $stmt = $pdo->prepare("SELECT u.name, u.email, dp.specialization, dp.clinic_id, dp.image_url 
                           FROM users u 
                           INNER JOIN doctor_profiles dp ON u.id = dp.user_id 
                           WHERE u.id = :id AND u.role = 'doctor'");
    $stmt->execute(['id' => $doctor_id]);
    $doctor = $stmt->fetch();

    if (!$doctor) {
        header("Location: doctors-read.php");
        exit();
    }
} catch (PDOException $e) {
    $errors[] = "حدث خطأ في النظام: " . $e->getMessage();
}

// 3. معالجة البيانات عند الضغط على زر التحديث (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $specialization = trim($_POST['specialization']);
    $clinic_id = $_POST['clinic_id'];
    $password = trim($_POST['password']); // اختياري عند التعديل

    // التحقق من المدخلات (Validation)
    if (empty($name) || empty($email) || empty::($specialization) || empty($clinic_id)) {
        $errors[] = "جميع الحقول عدا كلمة المرور مطلوبة.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "صيغة البريد الإلكتروني غير صحيحة.";
    }

    // معالجة رفع الصورة الجديدة (إذا اختار المسؤول تغيير الصورة)
    $image_name = $doctor['image_url']; // الاحتفاظ بالصورة القديمة كخيار افتراضي
    
    if (isset($_FILES['doctor_image']) && $_FILES['doctor_image']['error'] == 0) {
        $file = $_FILES['doctor_image'];
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_extensions)) {
            $errors[] = "امتداد الصورة غير مسموح به.";
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "حجم الصورة يجب أن يكون أقل من 2 ميجابايت.";
        }

        if (empty($errors)) {
            // إنشاء اسم فريد للصورة الجديدة
            $image_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            $upload_destination = '../uploads/' . $image_name;

            if (move_uploaded_file($file['tmp_name'], $upload_destination)) {
                // حذف الصورة القديمة من السيرفر لتوفير المساحة (إذا لم تكن الافتراضية)
                if ($doctor['image_url'] !== 'default-doctor.png') {
                    $old_image_path = '../uploads/' . $doctor['image_url'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
            } else {
                $errors[] = "فشل في رفع الصورة الجديدة.";
                $image_name = $doctor['image_url'];
            }
        }
    }

    // تحديث البيانات في قاعدة البيانات (Transaction)
    if (empty($errors)) {
        try {
            // التحقق من أن الإيميل الجديد لا يخص مستخدماً آخر
            $check_email = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $check_email->execute(['email' => $email, 'id' => $doctor_id]);
            
            if ($check_email->fetch()) {
                $errors[] = "هذا البريد الإلكتروني مستخدم بالفعل من قبل شخص آخر.";
            } else {
                $pdo->beginTransaction();

                // 1. تحديث جدول المستخدمين (users)
                if (!empty($password)) {
                    // إذا كتب المسؤول باسورداً جديداً، نقوم بتشفيره وتحديثه
                    if (strlen($password) < 6) {
                        throw new Exception("كلمة المرور الجديدة يجب أن تكون 6 خانات على الأقل.");
                    }
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update_user_sql = "UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id";
                    $user_params = ['name' => $name, 'email' => $email, 'password' => $hashed_password, 'id' => $doctor_id];
                } else {
                    // إذا ترك حقل الباسورد فارغاً، نحدث البيانات بدون تغيير الباسورد القديم
                    $update_user_sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
                    $user_params = ['name' => $name, 'email' => $email, 'id' => $doctor_id];
                }
                
                $stmt_user = $pdo->prepare($update_user_sql);
                $stmt_user->execute($user_params);

                // 2. تحديث جدول تفاصيل الأطباء (doctor_profiles)
                $stmt_profile = $pdo->prepare("UPDATE doctor_profiles SET clinic_id = :clinic_id, specialization = :specialization, image_url = :image_url WHERE user_id = :user_id");
                $stmt_profile->execute([
                    'clinic_id' => $clinic_id,
                    'specialization' => $specialization,
                    'image_url' => $image_name,
                    'user_id' => $doctor_id
                ]);

                $pdo->commit();
                $success_msg = "تم تحديث بيانات الطبيب بنجاح! <a href='doctors-read.php' class='alert-link'>العودة للقائمة</a>";
                
                // تحديث مصفوفة العرض المحتفظ بها لتعكس التعديلات الجديدة في الحقول فوراً
                $doctor['name'] = $name;
                $doctor['email'] = $email;
                $doctor['specialization'] = $specialization;
                $doctor['clinic_id'] = $clinic_id;
                $doctor['image_url'] = $image_name;

            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "حدث خطأ أثناء التحديث: " . $e->getMessage();
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
                <li class="nav-item mb-2"><a class="nav-link active" href="doctors-read.php">👨‍⚕️ إدارة الأطباء (CRUD)</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="#clinics">🏥 إدارة العيادات</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-dark" href="#appointments">📅 استعراض المواعيد</a></li>
            </ul>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card main-card p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-warning mb-0">✏️ تعديل بيانات الطبيب</h3>
                <a href="doctors-read.php" class="btn btn-sm btn-outline-secondary">رجوع للقائمة ←</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <form action="doctors-update.php?id=<?php echo $doctor_id; ?>" method="POST" enctype="multipart/form-data">
                <div class="row mb-3 align-items-center">
                    <div class="col-md-2 text-center">
                        <img src="../uploads/<?php echo htmlspecialchars($doctor['image_url']); ?>" alt="صورة حالية" class="rounded-circle border img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                    </div>
                    <div class="col-md-10">
                        <label class="form-label">تغيير الصورة الشخصية:</label>
                        <input type="file" name="doctor_image" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">الاسم بالكامل:</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">البريد الإلكتروني:</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">التخصص:</label>
                        <input type="text" name="specialization" class="form-control" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">العيادة التابعة (الجدول الخامس):</label>
                        <select name="clinic_id" class="form-select" required>
                            <?php foreach ($clinics as $clinic): ?>
                                <option value="<?php echo $clinic['id']; ?>" <?php echo ($clinic['id'] == $doctor['clinic_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($clinic['name']) . " (" . htmlspecialchars($clinic['floor']) . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">كلمة المرور الجديدة (اتركها فارغة إذا لم تكن تريد تغييرها):</label>
                    <input type="password" name="password" class="form-control" placeholder="******">
                </div>

                <button type="submit" class="btn btn-warning px-4 py-2 text-dark font-weight-bold">💾 تحديث البيانات</button>
            </form>
        </div>
    </div>
</div>

<?php 
require_once '../includes/footer.php'; 
?>