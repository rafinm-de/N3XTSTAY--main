<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-section">
                <h4>🏨
                    <?php echo SITE_NAME; ?>
                </h4>
                <p style="color: var(--gray-300);">
                    Your trusted partner for booking hotels in Bangladesh and worldwide.
                    Easy payments with bKash, Nagad, and international cards.
                </p>
            </div>

            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/index.php" class="footer-link">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/hotels.php" class="footer-link">Hotels</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/hotels.php?type=domestic" class="footer-link">Domestic
                            Destinations</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/hotels.php?type=international"
                            class="footer-link">International Destinations</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Support</h4>
                <ul class="footer-links">
                    <li><a href="#" class="footer-link">Help Center</a></li>
                    <li><a href="#" class="footer-link">Contact Us</a></li>
                    <li><a href="#" class="footer-link">Privacy Policy</a></li>
                    <li><a href="#" class="footer-link">Terms & Conditions</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Payment Methods</h4>
                <p style="color: var(--gray-300); margin-bottom: 10px;">We accept:</p>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <span class="badge badge-primary">bKash</span>
                    <span class="badge badge-success">Nagad</span>
                    <span class="badge badge-info">Visa</span>
                    <span class="badge badge-warning">Mastercard</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy;
                <?php echo date('Y'); ?>
                <?php echo SITE_NAME; ?>. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<!-- Authentication Modal -->
<?php include __DIR__ . '/auth-modal.php'; ?>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>

</html>