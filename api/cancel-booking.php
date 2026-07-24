<?php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

Session::start();

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to cancel booking']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get booking ID from JSON or POST
$input = json_decode(file_get_contents('php://input'), true);
$bookingId = intval($input['booking_id'] ?? $_POST['booking_id'] ?? 0);

if (!$bookingId) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $userId = Session::getUserId();

    // Get booking details
    $stmt = $conn->prepare("
        SELECT id, user_id, status, created_at 
        FROM bookings 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }

    $booking = $result->fetch_assoc();

    // Verify booking belongs to user
    if ($booking['user_id'] !== $userId) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    // Check if booking can be cancelled (status must be pending or confirmed)
    if (!in_array($booking['status'], ['pending', 'confirmed'])) {
        echo json_encode(['success' => false, 'message' => 'This booking cannot be cancelled']);
        exit;
    }

    // Check if booking was created within 24 hours
    $createdTime = strtotime($booking['created_at']);
    $currentTime = time();
    $hoursSinceCreation = ($currentTime - $createdTime) / 3600;

    if ($hoursSinceCreation > 24) {
        echo json_encode([
            'success' => false,
            'message' => 'Cancellation period expired. You can only cancel bookings within 24 hours of creation.'
        ]);
        exit;
    }

    // Update booking status to cancelled
    $updateStmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $updateStmt->bind_param("i", $bookingId);

    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Booking cancelled successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to cancel booking. Please try again.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while cancelling the booking'
    ]);
}
?>