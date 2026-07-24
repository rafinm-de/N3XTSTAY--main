<?php
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$name = 'Admin User';
$email = 'admin@n3xtstay.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$phone = '01700000000';
$role = 'admin';

// Check if admin exists
$stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Admin user already exists.";
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $phone, $role);
    if ($stmt->execute()) {
        echo "Admin user created successfully. Email: $email, Password: admin123";
    } else {
        echo "Error creating admin user: " . $conn->error;
    }
}
?>