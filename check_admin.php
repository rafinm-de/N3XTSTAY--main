<?php
require_once 'config/config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $conn->query("SELECT email, password, role FROM users");
while($row = $res->fetch_assoc()) echo json_encode($row) . "\n";
echo "Verify passed: " . (password_verify('admin123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') ? 'YES' : 'NO') . "\n";
?>
