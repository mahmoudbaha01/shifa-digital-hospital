<?php
// hospital/admin/doctors-create.php

// 1. بدء الجلسة وحماية الصفحة (صلاحيات المسؤول)
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. تضمين ملف الاتصال بقاعدة البيانات
require_once '../config/db.php';

$errors = [];
$success_msg = '';

// جلب العيادات المتاحة (الجدول الخامس) لعرضها في القائمة المنسدلة
try {
    $clinics_stmt = $pdo->query("SELECT * FROM clinics");
    $clinics = $clinics_stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "حدث خطأ أثناء جلب العيادات: " . $e->getMessage();
}

// 3. معالجة البيانات عند إرسال الفورم (اضغط على زر إضافة)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $specialization = trim($_POST['specialization']);
    $clinic_id = $_POST['clinic_id'];
    
    // أولاً: التحقق من صحة المدخلات النصية (Validation)
    if (empty($name) || empty($email) || empty($password) || empty($specialization) || empty($clinic_id)) {
        $errors[] = "جميع الحقول النصية واختيار العيادة مطلوبة.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "صيغة البريد الإلكتروني غير صحيحة.";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "يجب أن تكون كلمة مرور الطبيب 6 خانات على الأقل.";
    }

    // ثانياً: معالجة رفع الصورة والتحقق منها (File Upload Validation)
    $image_name = 'default-doctor.png'; // الاسم الافتراضي في حال لم يرفع صورة
    
    if (isset($_FILES['doctor_image']) && $_FILES['doctor_image']['error'] == 0) {
        $file = $_FILES['doctor_image'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // التحقق من الامتداد (للأمان)
        if (!in_repeat($file_ext, $allowed_extensions) && !in_array($file_ext, $allowed_extensions)) {
            $errors[] = "امتداد الصورة غير مسموح به. يرجى رفع (JPG, PNG, JPEG).";
        }
        
        // التحقق من الحجم (أقل من 2 ميجابايت مثلاً)
        if ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "حجم الصورة كبير جداً، الحد الأقصى 2 ميجابايت.";
        }
        
        // إذا لم تكن هناك أخطاء، نجهز اسماً فريداً للصورة لتجنب تكرار الأسماء
        if (empty($errors)) {
            $image_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            $upload_destination = '../uploads/' . $image_name;
            
            // نقل الصورة من المجلد المؤقت إلى مجلد uploads في المشروع
            if (!move_uploaded_file($file['tmp_name'], $upload_destination)) {
                $errors[] = "فشل في حفظ الصورة المرفوعة على السيرفر.";
                $image_name = 'default-doctor.png'; // العودة للافتراضي في حال الفشل
            }
        }
    }

    // ثالثاً: إدخال البيانات في قاعدة البيانات بأمان (Transaction)
    if (empty($errors)) {
        try {
            // نتحقق أولاً من أن الإيميل غير مكرر
            $check_email = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $check_email->execute(['email' => $email]);
            
            if ($check_email->fetch()) {
                $errors[] = "البريد الإلكتروني هذا مستخدم بالفعل لطبيب آخر أو مستخدم آخر.";
            } else {
                // نستخدم Transaction لأننا سنقوم بالإدخال في جدولين متتاليين (users ثم doctor_profiles)
                $pdo->beginTransaction();
                
                // 1. تشفير الباسورد وإدخاله في جدول المستخدمين بصفة doctor
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_user = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'doctor')");
                $stmt_user->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashed_password
                ]);
                
                // جلب الـ ID الخاص بالطبيب الذي أُنشئ للتو لربطه بالجدول الثاني
                $new_doctor_id = $pdo->lastInsertId();
                
                // 2. إدخال تفاصيل الطبيب (العيادة، التخصص، الصورة) في جدول doctor_profiles
                $stmt_profile = $pdo->prepare("INSERT INTO doctor_profiles (user_id, clinic_id, specialization, image_url) VALUES (:user_id, :clinic_id, :specialization, :image_url)");
                $stmt_profile->execute([
                    'user_id' => $new_doctor_id,
                    'clinic_id' => $clinic_id,
                    'specialization' => $specialization,
                    'image_url' => $image_name
                ]);
                
                // تأكيد العملية بالكامل بنجاح
                $pdo->commit();
                
                $success_msg = "تم إضافة الطبيب بنجاح وتخصيصه للعيادة المطلوبة!";
                
                // تفريغ الحقول بعد النجاح
                $name = $email = $specialization = '';
            }
            
        } catch (PDOException $e) {
            // إلغاء العمليات في حال حدوث خطأ أثناء التنفيذ لحماية سلامة البيانات
            $pdo->rollBack();
            $errors[] = "حدث خطأ في النظام أثناء الإضافة: " . $e->getMessage();
        }
    }
}

// تضمين الهيدر المشترك
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
                <h3 class="text-primary mb-0">➕ إضافة طبيب جديد للطاقم</h3>
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
                <div class="alert alert-success">
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <form action="doctors-create.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">اسم الطبيب بالكامل:</label>
                        <input type="text" name="name" class="form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">البريد الإلكتروني المهني:</label>
                        <input type="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">كلمة المرور الافتراضية للطبيب:</label>
                        <input type="password" name="password" class="form-control" placeholder="6 خانات على الأقل" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">التخصص الدقيق:</label>
                        <input type="text" name="specialization" class="form-control" placeholder="مثال: جراحة قلب، طب أطفال" value="<?php echo isset($specialization) ? htmlspecialchars($specialization) : ''; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">العيادة التابعة (الجدول الخامس):</label>
                        <select name="clinic_id" class="form-select" required>
                            <option value="">-- اختر العيادة ومكانها --</option>
                            <?php foreach ($clinics as $clinic): ?>
                                <option value="<?php echo $clinic['id']; ?>">
                                    <?php echo htmlspecialchars($clinic['name']) . " (" . htmlspecialchars($clinic['floor']) . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">صورة الطبيب الشخصية:</label>
                        <input type="file" name="doctor_image" class="form-control" accept="image/*">
                        <div class="form-text">صيغ مدعومة: JPG, PNG. الحد الأقصى 2MB.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success px-4 py-2 mt-2">💾 حفظ وبيانات الطبيب</button>
            </form>
        </div>
    </div>
</div>

<?php 
require_once '../includes/footer.php'; 
?>