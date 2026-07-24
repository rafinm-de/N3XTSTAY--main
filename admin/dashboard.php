<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Get statistics
$stats = [];

// Total hotels
$result = $conn->query("SELECT COUNT(*) as count FROM hotels WHERE is_active = 1");
$stats['hotels'] = $result->fetch_assoc()['count'];

// Total bookings
$result = $conn->query("SELECT COUNT(*) as count FROM bookings");
$stats['bookings'] = $result->fetch_assoc()['count'];

// Pending bookings
$result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
$stats['pending'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE status IN ('confirmed', 'completed')");
$stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Recent bookings
$recentBookings = $conn->query("
    SELECT b.*, h.name as hotel_name, u.name as user_name
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC
    LIMIT 10
");
?>

<div class="admin-container">
    <?php include __DIR__ . '/includes/admin-sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <div>
                <h1 style="margin: 0;">Dashboard</h1>
                <p style="color: var(--gray-600); margin: 0.25rem 0 0;">Welcome back,
                    <?php echo Session::getUserName(); ?>!
                </p>
            </div>
            <div class="btn btn-primary">
                <a href="<?php echo SITE_URL; ?>/index.php" style="color: white;">View Website</a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-icon" style="background: rgba(102, 126, 234, 0.1); color: var(--primary);">
                    🏨
                </div>
                <div class="stat-card-title">Total Hotels</div>
                <div class="stat-card-value">
                    <?php echo $stats['hotels']; ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                    📅
                </div>
                <div class="stat-card-title">Total Bookings</div>
                <div class="stat-card-value">
                    <?php echo $stats['bookings']; ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                    ⏳
                </div>
                <div class="stat-card-title">Pending Bookings</div>
                <div class="stat-card-value">
                    <?php echo $stats['pending']; ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background: rgba(59, 130, 246, 0.1); color: var(--info);">
                    💰
                </div>
                <div class="stat-card-title">Total Revenue</div>
                <div class="stat-card-value" style="font-size: 1.5rem;">
                    <?php echo formatCurrency($stats['revenue']); ?>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
            <h2 style="margin-bottom: 1.5rem;">Recent Bookings</h2>

            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Guest</th>
                            <th>Hotel</th>
                            <th>Check-in</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentBookings->num_rows > 0): ?>
                            <?php while ($booking = $recentBookings->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-family: monospace; font-weight: 600;">
                                        <?php echo $booking['booking_reference']; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['user_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['hotel_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo formatDate($booking['check_in']); ?>
                                    </td>
                                    <td style="font-weight: 600;">
                                        <?php echo formatCurrency($booking['total_amount']); ?>
                                    </td>
                                    <td>
                                        <select
                                            onchange="if(confirm('Update booking status?')) window.location.href='actions/booking_actions.php?action=update_status&id=<?php echo $booking['id']; ?>&status='+this.value + '&redirect=dashboard'"
                                            class="form-select" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                            <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="manage-bookings.php?action=edit&id=<?php echo $booking['id']; ?>"
                                                class="btn btn-outline btn-sm btn-icon" title="Edit">
                                                ✏️
                                            </a>
                                            <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                                <button class="btn btn-outline btn-sm btn-icon" style="color: red; border-color: red;"
                                                    onclick="if(confirm('Cancel this booking?')) window.location.href='actions/booking_actions.php?action=update_status&id=<?php echo $booking['id']; ?>&status=cancelled&redirect=dashboard'"
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
                                <td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray-500);">
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
</body>

</html>