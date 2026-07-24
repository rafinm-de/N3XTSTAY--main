<?php
$pageTitle = 'Booking Confirmed';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/Database.php';

if (!Session::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$bookingId = $_GET['id'] ?? 0;

if (!$bookingId) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();
$userId = Session::get('user_id');

// Get booking details
$stmt = $conn->prepare("
    SELECT b.*, h.name as hotel_name, h.image as hotel_image, r.room_type, p.payment_method, p.transaction_id
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    JOIN rooms r ON b.room_id = r.id
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}
?>

<div class="container" style="padding: 4rem 0;">
    <div style="max-width: 600px; margin: 0 auto; text-align: center;">
        <!-- Success Icon -->
        <div
            style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto 2rem; display: flex; align-items: center; justify-content: center; animation: scaleIn 0.5s ease;">
            <svg width="60" height="60" fill="white" viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
            </svg>
        </div>

        <h1 style="margin-bottom: 1rem; font-size: 2rem;">Booking Confirmed!</h1>
        <p style="color: var(--gray-600); font-size: 1.125rem; margin-bottom: 2rem;">
            Your booking has been successfully confirmed. We've sent a confirmation email with all the details.
        </p>

        <!-- Booking Details Card -->
        <div
            style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow-md); text-align: left; margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                <img src="<?php echo $booking['hotel_image']; ?>"
                    style="width: 120px; height: 120px; object-fit: cover; border-radius: 0.5rem;">
                <div>
                    <h3 style="margin: 0 0 0.5rem 0;">
                        <?php echo htmlspecialchars($booking['hotel_name']); ?>
                    </h3>
                    <p style="color: var(--gray-600); margin: 0; font-size: 0.875rem;">
                        <?php echo htmlspecialchars($booking['room_type']); ?>
                    </p>
                </div>
            </div>

            <div style="border-top: 1px solid var(--gray-200); padding-top: 1rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                    <div>
                        <small style="color: var(--gray-500); display: block; margin-bottom: 0.25rem;">Booking
                            Reference</small>
                        <strong style="color: var(--primary);">
                            <?php echo $booking['booking_reference']; ?>
                        </strong>
                    </div>
                    <div>
                        <small style="color: var(--gray-500); display: block; margin-bottom: 0.25rem;">Status</small>
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                    <div>
                        <small style="color: var(--gray-500); display: block; margin-bottom: 0.25rem;">Check-in</small>
                        <strong>
                            <?php echo formatDate($booking['check_in']); ?>
                        </strong>
                    </div>
                    <div>
                        <small style="color: var(--gray-500); display: block; margin-bottom: 0.25rem;">Check-out</small>
                        <strong>
                            <?php echo formatDate($booking['check_out']); ?>
                        </strong>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--gray-200); padding-top: 0.75rem; margin-top: 0.75rem;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--gray-600);">Total Amount</span>
                        <strong style="font-size: 1.25rem; color: var(--primary);">
                            <?php echo formatCurrency($booking['total_amount']); ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-primary btn-lg">
                Go to Homepage
            </a>
            <a href="<?php echo SITE_URL; ?>/profile.php" class="btn btn-outline btn-lg">
                View My Profile
            </a>
        </div>

        <!-- Cancellation Policy Notice -->
        <div style="margin-top: 2rem; padding: 1rem; background: #fef3c7; border-radius: 0.5rem; text-align: left;">
            <strong style="display: block; margin-bottom: 0.5rem;">📌 Cancellation Policy</strong>
            <p style="font-size: 0.875rem; margin: 0; color: var(--gray-700);">
                You can cancel this booking within 24 hours from now without any charges.
                After 24 hours, cancellation will not be available.
            </p>
        </div>
    </div>
</div>

<style>
    @keyframes scaleIn {
        from {
            transform: scale(0);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>