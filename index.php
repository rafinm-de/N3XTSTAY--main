<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Get destinations for search
$destinations = $conn->query("SELECT * FROM destinations WHERE is_active = 1 ORDER BY name");

// Get featured hotels
$featuredHotels = $conn->query("
    SELECT h.*, d.name as destination_name, d.country,
           (SELECT MIN(price_per_night) FROM rooms WHERE hotel_id = h.id AND is_active = 1) as min_price,
           (SELECT AVG(rating) FROM reviews WHERE hotel_id = h.id) as avg_rating
    FROM hotels h
    LEFT JOIN destinations d ON h.destination_id = d.id
    WHERE h.is_active = 1
    ORDER BY RAND()
    LIMIT 6
");
?>

<script>
    // Set logged in status for JavaScript
    document.body.dataset.loggedIn = '<?php echo Session::isLoggedIn() ? 'true' : 'false'; ?>';
</script>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1 class="hero-title">Discover Your Perfect Stay</h1>
        <p class="hero-subtitle">Book hotels in Bangladesh and worldwide with easy payments</p>

        <!-- Search Form -->
        <form class="search-form" onsubmit="searchHotels(event)">
            <div class="form-group">
                <label class="form-label">Destination</label>
                <select name="destination" class="form-select" required>
                    <option value="">Select destination</option>
                    <?php while ($dest = $destinations->fetch_assoc()): ?>
                        <option value="<?php echo $dest['id']; ?>">
                            <?php echo $dest['name'] . ', ' . $dest['country']; ?>
                            <?php echo $dest['type'] === 'international' ? '✈️' : '🏠'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Check-in</label>
                <input type="date" name="check_in" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Check-out</label>
                <input type="date" name="check_out" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Guests</label>
                <input type="number" name="guests" class="form-input" min="1" max="10" value="2" required>
            </div>

            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    🔍 Search Hotels
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Featured Hotels Section -->
<section class="section">
    <div class="container">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2>Featured Hotels</h2>
            <p style="color: var(--gray-600); font-size: 1.125rem;">Discover our handpicked selection of amazing stays
            </p>
        </div>

        <div class="grid grid-3">
            <?php if ($featuredHotels->num_rows > 0): ?>
                <?php while ($hotel = $featuredHotels->fetch_assoc()): ?>
                    <div class="card">
                        <img src="<?php echo $hotel['image'] ?: 'https://via.placeholder.com/400x250?text=Hotel+Image'; ?>"
                            alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="card-image">
                        <div class="card-body">
                            <h3 class="card-title">
                                <?php echo htmlspecialchars($hotel['name']); ?>
                            </h3>
                            <div class="hotel-location">
                                📍
                                <?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['country']); ?>
                            </div>

                            <?php if ($hotel['avg_rating']): ?>
                                <div class="hotel-rating">
                                    <?php echo getStarRating($hotel['avg_rating']); ?>
                                    <span style="color: var(--gray-600); font-size: 0.875rem;">
                                        (
                                        <?php echo number_format($hotel['avg_rating'], 1); ?>)
                                    </span>
                                </div>
                            <?php endif; ?>

                            <p class="card-text">
                                <?php echo htmlspecialchars(substr($hotel['description'], 0, 100)) . '...'; ?>
                            </p>
                        </div>
                        <div class="card-footer">
                            <div>
                                <div class="hotel-price">
                                    <?php echo formatCurrency($hotel['min_price'] ?? 0); ?>
                                </div>
                                <div class="hotel-price-label">per night</div>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/hotel-details.php?id=<?php echo $hotel['id']; ?>"
                                class="btn btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <p style="color: var(--gray-500); font-size: 1.125rem;">No hotels available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="<?php echo SITE_URL; ?>/hotels.php" class="btn btn-outline btn-lg">
                View All Hotels
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section" style="background: var(--white);">
    <div class="container">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2>Why Choose Us</h2>
            <p style="color: var(--gray-600); font-size: 1.125rem;">Making hotel booking simple and convenient</p>
        </div>

        <div class="grid grid-4">
            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                <h4>Multiple Payment Options</h4>
                <p style="color: var(--gray-600);">Pay with bKash, Nagad, or international cards</p>
            </div>

            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🌍</div>
                <h4>Global Destinations</h4>
                <p style="color: var(--gray-600);">Book hotels in Bangladesh and worldwide</p>
            </div>

            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⚡</div>
                <h4>Instant Confirmation</h4>
                <p style="color: var(--gray-600);">Get instant booking confirmation via email</p>
            </div>

            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔒</div>
                <h4>Secure Booking</h4>
                <p style="color: var(--gray-600);">Your data is safe with us</p>
            </div>
        </div>
    </div>
</section>

<!-- Popular Destinations -->
<?php
$destinations->data_seek(0); // Reset pointer
?>
<section class="section">
    <div class="container">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2>Popular Destinations</h2>
            <p style="color: var(--gray-600); font-size: 1.125rem;">Explore amazing places to visit</p>
        </div>

        <div class="grid grid-3">
            <?php
            $count = 0;
            while ($dest = $destinations->fetch_assoc()):
                if ($count >= 6)
                    break;
                $count++;
                ?>
                <a href="<?php echo SITE_URL; ?>/hotels.php?destination=<?php echo $dest['id']; ?>" class="card"
                    style="cursor: pointer;">
                    <img src="<?php echo $dest['image'] ?: 'https://via.placeholder.com/400x250?text=' . urlencode($dest['name']); ?>"
                        alt="<?php echo htmlspecialchars($dest['name']); ?>" class="card-image">
                    <div class="card-body">
                        <h3 class="card-title">
                            <?php echo htmlspecialchars($dest['name']); ?>
                        </h3>
                        <p class="card-text">
                            <?php echo htmlspecialchars($dest['country']); ?>
                        </p>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>