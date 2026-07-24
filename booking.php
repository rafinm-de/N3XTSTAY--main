<?php
$pageTitle = 'Confirm Booking';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/Database.php';

if (!Session::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();

$hotelId = $_GET['hotel_id'] ?? 0;
$roomId = $_GET['room_id'] ?? 0;
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 1;

if (!$hotelId || !$roomId || !$checkIn || !$checkOut) {
    echo "<div class='container' style='padding: 4rem;'><div class='alert alert-error'>Invalid booking parameters.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch details
$stmt = $conn->prepare("SELECT h.name as hotel_name, h.image as hotel_image, h.address, h.city, h.country FROM hotels h WHERE h.id = ?");
$stmt->bind_param("i", $hotelId);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT room_type, price_per_night, image FROM rooms WHERE id = ?");
$stmt->bind_param("i", $roomId);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

// Calculate totals
$start = new DateTime($checkIn);
$end = new DateTime($checkOut);
$days = $end->diff($start)->days;
$totalPrice = $room['price_per_night'] * $days;
$tax = $totalPrice * 0.10; // 10% tax
$grandTotal = $totalPrice + $tax;

?>

<div class="container" style="padding: 4rem 0;">
    <h1 style="margin-bottom: 2rem;">Confirm Your Booking</h1>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Booking Form -->
        <div>
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow-md);">
                <h3>Guest Information</h3>
                <form id="bookingForm" onsubmit="processBooking(event)">
                    <input type="hidden" name="hotel_id" value="<?php echo $hotelId; ?>">
                    <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">
                    <input type="hidden" name="check_in" value="<?php echo $checkIn; ?>">
                    <input type="hidden" name="check_out" value="<?php echo $checkOut; ?>">
                    <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                    <input type="hidden" name="total_amount" value="<?php echo $grandTotal; ?>">
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                            <label class="payment-option"
                                style="border: 2px solid var(--gray-300); padding: 1rem; border-radius: 0.75rem; cursor: pointer; text-align: center; transition: all 0.3s;">
                                <input type="radio" name="payment_method" value="card" checked style="display: none;">
                                <img src="<?php echo SITE_URL; ?>/assets/images/card.png"
                                    style="width: 60px; height: 60px; margin: 0 auto 0.5rem; border-radius: 0.5rem;"
                                    alt="Credit Card">
                                <div style="font-weight: 600; font-size: 0.875rem;">Credit Card</div>
                            </label>
                            <label class="payment-option"
                                style="border: 2px solid var(--gray-300); padding: 1rem; border-radius: 0.75rem; cursor: pointer; text-align: center; transition: all 0.3s;">
                                <input type="radio" name="payment_method" value="bkash" style="display: none;">
                                <img src="<?php echo SITE_URL; ?>/assets/images/bkash.png"
                                    style="width: 60px; height: 60px; margin: 0 auto 0.5rem; border-radius: 0.5rem;"
                                    alt="bKash">
                                <div style="font-weight: 600; font-size: 0.875rem;">bKash</div>
                            </label>
                            <label class="payment-option"
                                style="border: 2px solid var(--gray-300); padding: 1rem; border-radius: 0.75rem; cursor: pointer; text-align: center; transition: all 0.3s;">
                                <input type="radio" name="payment_method" value="nagad" style="display: none;">
                                <img src="<?php echo SITE_URL; ?>/assets/images/nagad.png"
                                    style="width: 60px; height: 60px; margin: 0 auto 0.5rem; border-radius: 0.5rem;"
                                    alt="Nagad">
                                <div style="font-weight: 600; font-size: 0.875rem;">Nagad</div>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-input" value="<?php echo Session::getUserName(); ?>" readonly>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1rem;">
                        Proceed to Payment
                    </button>
                </form>
                
                <style>
                    .payment-option:hover {
                        border-color: var(--primary) !important;
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
                    }
                    .payment-option input:checked + img {
                        opacity: 1;
                    }
                    .payment-option input:not(:checked) + img {
                        opacity: 0.6;
                    }
                    .payment-option:has(input:checked) {
                        border-color: var(--primary) !important;
                        background: rgba(102, 126, 234, 0.05);
                    }
                </style>
            </div>
        </div>

        <!-- Summary -->
        <div>
            <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: var(--shadow-md);">
                <img src="<?php echo $hotel['hotel_image']; ?>"
                    style="width: 100%; height: 200px; object-fit: cover; border-radius: 0.5rem; margin-bottom: 1rem;">
                <h4>
                    <?php echo htmlspecialchars($hotel['hotel_name']); ?>
                </h4>
                <p style="color: var(--gray-600); font-size: 0.875rem;">
                    <?php echo htmlspecialchars($hotel['address']); ?><br>
                    <?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['country']); ?>
                </p>
                <hr style="border: 0; border-top: 1px solid var(--gray-200); margin: 1rem 0;">

                <div style="margin-bottom: 0.5rem;">
                    <strong>Room:</strong>
                    <?php echo htmlspecialchars($room['room_type']); ?>
                </div>
                <div style="margin-bottom: 0.5rem;">
                    <strong>Dates:</strong>
                    <?php echo formatDate($checkIn); ?> to
                    <?php echo formatDate($checkOut); ?>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Duration:</strong>
                    <?php echo $days; ?> Night(s),
                    <?php echo $guests; ?> Guest(s)
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Price per night</span>
                    <span>
                        <?php echo formatCurrency($room['price_per_night']); ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Wait ($days nights)</span>
                    <span>
                        <?php echo formatCurrency($totalPrice); ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                    <span>Tax (10%)</span>
                    <span>
                        <?php echo formatCurrency($tax); ?>
                    </span>
                </div>
                <hr style="border: 0; border-top: 1px solid var(--gray-200); margin: 1rem 0;">
                <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.25rem;">
                    <span>Total</span>
                    <span>
                        <?php echo formatCurrency($grandTotal); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function processBooking(e) {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Processing...';
        btn.disabled = true;

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        data.action = 'create_booking'; // Assuming an action handler exists, or we create one

        // For now, let's simulate or call a hypothetical endpoint. 
        // Since I deleted root files, I need to ensure 'process_booking.php' or similar exists in actions?
        // Actually, let's look at existing actions. 'booking_actions.php' is in admin/actions but maybe we need a public one.
        // For this recovery, I'll send it to a new api endpoint or valid location.
        // Let's create 'actions/create_booking.php' or similar if missing.
        // Wait, I'll just post to myself or a new file.

        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/create_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // Redirect to payment page with selected payment method
                const amount = document.querySelector('input[name="total_amount"]').value;
                const method = document.querySelector('input[name="payment_method"]:checked').value;
                window.location.href = `<?php echo SITE_URL; ?>/payment.php?booking_id=${result.booking_id}&amount=${amount}&method=${method}`;
                // Don't re-enable button - we're redirecting
            } else {
                alert(result.message || 'Booking failed');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>