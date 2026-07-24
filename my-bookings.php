<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/Database.php';

if (!Session::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();
$userId = Session::get('user_id');

$bookings = $conn->prepare("
    SELECT b.*, h.name as hotel_name, h.image as hotel_image, r.room_type, 
           p.payment_method, p.transaction_id
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    JOIN rooms r ON b.room_id = r.id
    LEFT JOIN payments p ON b.id = p.booking_id AND p.status = 'completed'
    WHERE b.user_id = ?
    GROUP BY b.id
    ORDER BY b.created_at DESC
");
$bookings->bind_param("i", $userId);
$bookings->execute();
$result = $bookings->get_result();
?>

<div class="container" style="padding: 4rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>My Bookings</h1>
        <a href="<?php echo SITE_URL; ?>/hotels.php" class="btn btn-primary">Book New Hotel</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="grid grid-2">
            <?php while ($booking = $result->fetch_assoc()): ?>
                <div class="card" style="display: flex; flex-direction: row; gap: 0;">
                    <img src="<?php echo $booking['hotel_image']; ?>" style="width: 200px; height: 100%; object-fit: cover;">
                    <div style="padding: 1.5rem; flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <h3 style="margin: 0;">
                                <?php echo htmlspecialchars($booking['hotel_name']); ?>
                            </h3>
                            <span class="badge <?php
                            echo $booking['status'] === 'confirmed' ? 'badge-success' :
                                ($booking['status'] === 'pending' ? 'badge-warning' :
                                    ($booking['status'] === 'cancelled' ? 'badge-error' : 'badge-info'));
                            ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                        <p style="color: var(--gray-600); margin-bottom: 1rem;">
                            <strong>Room:</strong>
                            <?php echo htmlspecialchars($booking['room_type']); ?><br>
                            <strong>Dates:</strong>
                            <?php echo formatDate($booking['check_in']); ?> -
                            <?php echo formatDate($booking['check_out']); ?><br>
                            <strong>Amount:</strong>
                            <?php echo formatCurrency($booking['total_amount']); ?>
                        </p>

                        <div
                            style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--gray-100);">
                            <small style="color: var(--gray-500);">Ref:
                                <?php echo $booking['booking_reference']; ?>
                            </small>
                            <?php if (($booking['status'] === 'pending' || $booking['status'] === 'confirmed') && strtotime($booking['created_at']) > time() - 86400): ?>
                                <button class="btn btn-outline btn-sm" style="color: red; border-color: red;"
                                    onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                    Cancel
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 4rem; background: white; border-radius: 1rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">📅</div>
            <h3>No Bookings Found</h3>
            <p>You haven't made any bookings yet.</p>
        </div>
    <?php endif; ?>
</div>



<?php require_once __DIR__ . '/includes/footer.php'; ?>