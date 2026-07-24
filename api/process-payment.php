<?php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/functions.php';

Session::start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$bookingId = intval($input['booking_id'] ?? 0);
$paymentMethod = sanitize($input['payment_method'] ?? '');
$accountNumber = sanitize($input['account_number'] ?? '');
$userId = Session::getUserId();

if (!$bookingId || !$paymentMethod || !$accountNumber) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verify booking belongs to user
    $stmt = $conn->prepare("SELECT id, total_amount FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $bookingId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }

    $booking = $result->fetch_assoc();
    $amount = $booking['total_amount'];

    // Start transaction
    $conn->begin_transaction();

    // Update booking status
    $updateStmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
    $updateStmt->bind_param("i", $bookingId);
    $updateStmt->execute();

    // Record payment
    // Check if payments table exists and has correct columns, assuming it does based on my-bookings.php query
    // "LEFT JOIN payments p ON b.id = p.booking_id"
    // We'll insert a dummy transaction ID
    $trxId = strtoupper(uniqid('TRX'));

    $paymentStmt = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, 'completed')");
    $paymentStmt->bind_param("idss", $bookingId, $amount, $paymentMethod, $trxId);
    $paymentStmt->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment successful',
        'transaction_id' => $trxId
    ]);

} catch (Exception $e) {
    if (isset($conn))
        $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Payment failed: ' . $e->getMessage()]);
}
?>