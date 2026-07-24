<?php
$pageTitle = 'Payment';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/Database.php';

if (!Session::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$bookingId = $_GET['booking_id'] ?? 0;
$paymentMethod = $_GET['method'] ?? 'bkash';
$amount = $_GET['amount'] ?? 0;

?>

<div class="container" style="padding: 4rem 0;">
    <div
        style="max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow-lg);">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
            <h2>Complete Your Payment</h2>
            <p style="color: var(--gray-600);">Secure payment for your booking</p>
        </div>

        <form id="paymentForm" onsubmit="processPayment(event)">
            <input type="hidden" id="booking_id" value="<?php echo htmlspecialchars($bookingId); ?>">

            <div
                style="background: var(--gray-50); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center;">
                <div style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 0.5rem;">Payment Method</div>
                <div style="font-size: 1.5rem; font-weight: 600; text-transform: capitalize;">
                    <?php
                    $methodDisplay = $paymentMethod === 'bkash' ? '📱 bKash' : ($paymentMethod === 'nagad' ? '💸 Nagad' : '💳 Card');
                    echo $methodDisplay;
                    ?>
                </div>
            </div>
            <input type="hidden" id="payment_method" value="<?php echo htmlspecialchars($paymentMethod); ?>">

            <div class="form-group">
                <label class="form-label">Enter Account Number</label>
                <input type="text" id="account_number" class="form-input" placeholder="01XXXXXXXXX" pattern="[0-9]{11}"
                    required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;" id="payButton">
                Pay Now
            </button>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div class="modal" id="successModal">
    <div class="modal-content" style="text-align: center;">
        <div style="font-size: 4rem; margin-bottom: 1rem; color: var(--success);">✓</div>
        <h3>Payment Successful!</h3>
        <p style="color: var(--gray-600); margin-bottom: 2rem;">Your booking has been confirmed.</p>
        <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-primary">Return to Home</a>
    </div>
</div>

<script>

    async function processPayment(e) {
        e.preventDefault();

        const bookingId = document.getElementById('booking_id').value;
        const paymentMethod = document.getElementById('payment_method').value;
        const accountNumber = document.getElementById('account_number').value;
        const payButton = document.getElementById('payButton');

        if (!paymentMethod || !accountNumber) {
            alert('Please fill in all fields');
            return;
        }

        payButton.disabled = true;
        payButton.textContent = 'Processing...';

        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/process-payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    booking_id: bookingId,
                    payment_method: paymentMethod,
                    account_number: accountNumber
                })
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('successModal').classList.add('active');
            } else {
                alert(result.message || 'Payment failed');
                payButton.disabled = false;
                payButton.textContent = 'Pay Now';
            }
        } catch (error) {
            alert('An error occurred');
            payButton.disabled = false;
            payButton.textContent = 'Pay Now';
        }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>