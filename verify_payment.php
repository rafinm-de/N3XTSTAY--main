<?php
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Session.php';
require_once __DIR__ . '/config/config.php';

// disable auth check for testing api directly if needed, but we can simulate session
// Actually, it's hard to simulate session in a standalone script accessed via curl from inside
// We will modify the script to manually set session for the duration of the test if possible,
// or we will just use the functions directly without HTTP calls for this test script.

header('Content-Type: text/plain');

$db = Database::getInstance();
$conn = $db->getConnection();

// 1. Create a Test User
$email = 'paytest_' . time() . '@example.com';
$password = 'password123';
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES ('Pay Test', ?, '01700000000', ?, 'user')");
$stmt->bind_param("ss", $email, $hashed);
if (!$stmt->execute())
    die("Failed to create user");
$userId = $conn->insert_id;
echo "User created: ID $userId\n";

// 2. Create a Booking
$hotelId = 1;
$roomId = 1;
$totalAmount = 5000;
$stmt = $conn->prepare("INSERT INTO bookings (user_id, hotel_id, room_id, check_in, check_out, total_amount, status) VALUES (?, ?, ?, '2026-02-01', '2026-02-02', ?, 'pending')");
$stmt->bind_param("iiid", $userId, $hotelId, $roomId, $totalAmount);
if (!$stmt->execute())
    die("Failed to create booking");
$bookingId = $conn->insert_id;
echo "Booking created: ID $bookingId (pending)\n";

// 3. Test Payment API Logic (Directly calling code to avoid session/auth issues via HTTP)
// But we need to verify the API file itself. 
// We will mock the Session::getUserId() by temporarily setting $_SESSION
$_SESSION['user_id'] = $userId;
$_SESSION['user_role'] = 'user';

// Mock request to process-payment
$paymentData = [
    'booking_id' => $bookingId,
    'payment_method' => 'bkash',
    'account_number' => '01711111111'
];

// We can't include api/process-payment.php directly because it outputs JSON and exits.
// So we will replicate the logic or use CURL with a cookie jar?
// Let's use CURL with cookie jar to persist session.

// Login first to get cookie
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, SITE_URL . '/auth/login.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['login_email' => $email, 'login_password' => $password]));
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$loginResp = curl_exec($ch);
curl_close($ch);
// echo "Login Resp: $loginResp\n";

// Now call Payment API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/WBT/hotel_booking/api/process-payment.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$payResp = curl_exec($ch);
curl_close($ch);
echo "Payment API Response: $payResp\n";

// Check DB
$res = $conn->query("SELECT status FROM bookings WHERE id = $bookingId");
$row = $res->fetch_assoc();
echo "Booking Status in DB: " . $row['status'] . "\n";

if ($row['status'] === 'confirmed') {
    echo "PAYMENT VERIFICATION: PASSED\n";
} else {
    echo "PAYMENT VERIFICATION: FAILED\n";
}

// 4. Test Cancellation API
// For cancellation, status must be pending or confirmed. It's confirmed now.
$cancelData = ['booking_id' => $bookingId];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/WBT/hotel_booking/api/cancel-booking.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cancelData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$cancelResp = curl_exec($ch);
curl_close($ch);
echo "Cancel API Response: $cancelResp\n";

// Check DB
$res = $conn->query("SELECT status FROM bookings WHERE id = $bookingId");
$row = $res->fetch_assoc();
echo "Booking Status in DB: " . $row['status'] . "\n";

if ($row['status'] === 'cancelled') {
    echo "CANCELLATION VERIFICATION: PASSED\n";
} else {
    echo "CANCELLATION VERIFICATION: FAILED\n";
}

// Cleanup
unlink('cookie.txt');
?>