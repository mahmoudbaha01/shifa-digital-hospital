<?php
// hospital/admin/doctors-delete.php

// 1. بدء الجلسة وحماية الصفحة (صلاحيات المسؤول فقط)
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. تضمين ملف الاتصال بقاعدة البيانات
require_once '../config/db.php';

// 3. التحقق من إرسال الـ ID الخاص بالطبيب عبر الرابط
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $doctor_id = $_GET['id'];

    try {
        /*
          ملاحظة برمجية هامة جداً للمناقشة:
          بما أننا قمنا بإنشاء الجداول في قاعدة البيانات واستخدمنا الخاصية:
          FOREIGN KEY (...) REFERENCES users(id) ON DELETE CASCADE
          فإننا بمجرد حذف المستخدم من جدول users، سيقوم السيرفر تلقائياً 
          بحذف الملف الشخصي التابع له من جدول doctor_profiles دون الحاجة لكتابة استعلام ثانٍ!
        */
        
        // جلب اسم الصورة القديمة من القاعدة لحذفها من السيرفر نهائياً وتوفير المساحة
        $img_stmt = $pdo->prepare("SELECT image_url FROM doctor_profiles WHERE user_id = :user_id");
        $img_stmt->execute(['user_id' => $doctor_id]);
        $doctor_profile = $img_stmt->fetch();
        
        // إذا كان للطبيب صورة خاصة وليست الافتراضية، نقوم بحذفها من مجلد uploads
        if ($doctor_profile && $doctor_profile['image_url'] !== 'default-doctor.png') {
            $image_path = '../uploads/' . $doctor_profile['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path); // دالة حذف الملفات من السيرفر في PHP
            }
        }

        // الاستعلام الآمن لحذف الطبيب من جدول المستخدمين الرئيسي
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'doctor'");
        $stmt->execute(['id' => $doctor_id]);

        // 4. إعادة التوجيه لصفحة العرض بعد نجاح عملية الحذف
        header("Location: doctors-read.php?msg=deleted");
        exit();

    } catch (PDOException $e) {
        die("حدث خطأ أثناء محاولة حذف الطبيب: " . $e->getMessage());
    }
} else {
    // في حال محاولة دخول الصفحة بدون توفير ID صالحة
    header("Location: doctors-read.php");
    exit();
}
?>