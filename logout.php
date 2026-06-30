<?php
// hospital/logout.php
session_start();

// إزالة جميع متغيرات الجلسة
$_SESSION = array();

// تدمير الكوكيز الخاصة بالـ Session من المتصفح لزيادة الأمان
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// تدمير الجلسة بالكامل في السيرفر
session_destroy();

// توجيه المستخدم لصفحة تسجيل الدخول مع رسالة تأكيد غير مباشرة
header("Location: login.php");
exit();
?>