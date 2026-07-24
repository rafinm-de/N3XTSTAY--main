<?php
require_once 'config/config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$new_hash = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$new_hash' WHERE email = 'admin@nextstay.com'");
echo "Updated password hash for admin.\n";
?>
