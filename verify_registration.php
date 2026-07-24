<?php
// Script to verify registration backend
require_once __DIR__ . '/config/config.php';
$url = SITE_URL . '/auth/register.php';
$email = 'test_' . time() . '@example.com';
$password = 'password123';

$data = [
    'register_name' => 'Test User',
    'register_email' => $email,
    'register_phone' => '01700000000',
    'register_password' => $password,
    'register_confirm_password' => $password
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_URL, "http://localhost/WBT/hotel_booking/api/create_booking.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response Code: " . $httpCode . "\n";
echo "Response Body: " . $response . "\n";

// Verify against DB
require_once __DIR__ . '/includes/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();
$result = $conn->query("SELECT * FROM users WHERE email = '$email'");

if ($result->num_rows > 0) {
    echo "SUCCESS: User created in database.\n";
    $user = $result->fetch_assoc();
    echo "User ID: " . $user['id'] . "\n";
} else {
    echo "FAILURE: User not found in database.\n";
}
?>