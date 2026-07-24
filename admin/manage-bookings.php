<?php
$pageTitle = 'Manage Bookings';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle Actions
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'update_status') {
    $bookingId = (int) ($_REQUEST['id'] ?? 0);
    $status = $_REQUEST['status'] ?? '';

    $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];

    if ($bookingId > 0 && in_array($status, $validStatuses)) {
        // 1. Get Booking details first (needed for email)
        $stmt = $conn->prepare("
            SELECT b.*, u.email as user_email, u.name as user_name, h.name as hotel_name, r.room_type 
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN hotels h ON b.hotel_id = h.id
            JOIN rooms r ON b.room_id = r.id
            WHERE b.id = ?
        ");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();

        if ($booking) {
            // 2. Update Status
            $updateStmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $updateStmt->bind_param("si", $status, $bookingId);

            if ($updateStmt->execute()) {
                Session::setFlash('success', 'Booking status updated successfully');

                // 3. Send Email if Cancelled
                if ($status === 'cancelled') {
                    $to = $booking['user_email'];
                    $subject = "Booking Cancelled - " . SITE_NAME;
                    $message = "
                    <h2>Booking Cancelled</h2>
                    <p>Dear " . htmlspecialchars($booking['user_name']) . ",</p>
                    <p>Your booking (Ref: <strong>" . htmlspecialchars($booking['booking_reference']) . "</strong>) at <strong>" . htmlspecialchars($booking['hotel_name']) . "</strong> has been cancelled by the administrator.</p>
                    <p>If you have any questions, please contact our support.</p>
                    <br>
                    <p>Regards,<br>" . SITE_NAME . "</p>";

                    if (@sendEmail($to, $subject, $message)) {
                        // Email sent
                    } else {
                        error_log("Failed to send cancellation email to " . $to);
                    }
                }
            } else {
                Session::setFlash('error', 'Failed to update booking status');
            }
        } else {
            Session::setFlash('error', 'Booking not found');
        }
    } else {
        Session::setFlash('error', 'Invalid booking ID or status');
    }
    redirect('manage-bookings.php');
}

// Get all bookings
$bookings = $conn->query("
    SELECT b.*, h.name as hotel_name, u.name as user_name, u.email as user_email,
           r.room_type, p.payment_method
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    LEFT JOIN payments p ON b.id = p.booking_id
    ORDER BY b.created_at DESC
");
?>

<div class="admin-container">
    <?php include __DIR__ . '/includes/admin-sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1 style="margin: 0;">Manage Bookings</h1>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
            <div style="margin-bottom: 1.5rem;">
                <input type="text" id="searchInput" class="form-input" placeholder="Search bookings..."
                    onkeyup="searchTable('searchInput', 'bookingsTable')" style="max-width: 400px;">
            </div>

            <div class="data-table">
                <table id="bookingsTable">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Guest</th>
                            <th>Hotel</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th style="white-space: nowrap;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings->num_rows > 0): ?>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-family: monospace; font-weight: 600;">
                                        <?php echo $booking['booking_reference']; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <?php echo htmlspecialchars($booking['user_name']); ?>
                                        </div>
                                        <small style="color: var(--gray-500);">
                                            <?php echo htmlspecialchars($booking['user_email']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['hotel_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo formatDate($booking['check_in']); ?>
                                    </td>
                                    <td>
                                        <?php echo formatDate($booking['check_out']); ?>
                                    </td>
                                    <td style="font-weight: 600;">
                                        <?php echo formatCurrency($booking['total_amount']); ?>
                                    </td>
                                    <td>
                                        <?php if ($booking['payment_method']): ?>
                                            <span class="badge badge-success">
                                                <?php echo ucfirst($booking['payment_method']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <select
                                            onchange="if(confirm('Update booking status?')) window.location.href='manage-bookings.php?action=update_status&id=<?php echo $booking['id']; ?>&status='+this.value"
                                            class="form-select" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">
                                            <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <div style="display: flex; flex-direction: column; gap: 0.25rem; align-items: stretch;">
                                            <button class="btn btn-outline btn-sm"
                                                onclick="viewDetails(<?php echo $booking['id']; ?>)" title="View Details" style="width: 100%;">
                                                👁️
                                            </button>
                                            <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                                <button class="btn btn-outline btn-sm"
                                                    style="color: red; border-color: red; width: 100%;"
                                                    onclick="if(confirm('Cancel this booking?')) window.location.href='manage-bookings.php?action=update_status&id=<?php echo $booking['id']; ?>&status=cancelled'"
                                                    title="Cancel Booking">
                                                    ✕
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                                    No bookings found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
<script>
    function viewDetails(id) {
        alert('Booking details view - ID: ' + id);
    }
</script>
</body>

</html>