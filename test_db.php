<?php
require_once 'config/db.php'; // تأكد أن المسار صحيح

try {
    $stmt = $pdo->query("SELECT email, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>المستخدمون الموجودون حالياً في قاعدة البيانات المتصل بها:</h3>";
    echo "<table border='1'><tr><th>Email</th><th>Password (Hash)</th></tr>";
    foreach ($users as $user) {
        echo "<tr><td>" . $user['email'] . "</td><td>" . $user['password'] . "</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>