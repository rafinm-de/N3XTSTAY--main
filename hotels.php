<?php
$pageTitle = 'Hotels';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Get search parameters
$destinationId = $_GET['destination'] ?? '';
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$minRating = $_GET['min_rating'] ?? '';
$type = $_GET['type'] ?? '';

// Build query
$query = "
    SELECT h.*, d.name as destination_name, d.country, d.type as destination_type,
           (SELECT MIN(price_per_night) FROM rooms WHERE hotel_id = h.id AND is_active = 1) as min_price,
           (SELECT AVG(rating) FROM reviews WHERE hotel_id = h.id) as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE hotel_id = h.id) as review_count
    FROM hotels h
    LEFT JOIN destinations d ON h.destination_id = d.id
    WHERE h.is_active = 1
";

$params = [];
$types = "";

if ($destinationId) {
    $query .= " AND h.destination_id = ?";
    $params[] = $destinationId;
    $types .= "i";
}

if ($type) {
    $query .= " AND d.type = ?";
    $params[] = $type;
    $types .= "s";
}

$query .= " ORDER BY h.star_rating DESC, h.id DESC";

// Execute query
if (count($params) > 0) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $hotels = $stmt->get_result();
} else {
    $hotels = $conn->query($query);
}

// Get destinations for filter
$destinations = $conn->query("SELECT * FROM destinations WHERE is_active = 1 ORDER BY name");
?>

<script>
document.body.dataset.loggedIn = '<?php echo Session::isLoggedIn() ? 'true' : 'false'; ?>';
</script>

<div style="background: var(--gradient-primary); padding: 3rem 0; color: white;">
    <div class="container">
        <h1 style="color: white; margin-bottom: 0.5rem;">Find Your Perfect Hotel</h1>
        <p style="color: rgba(255,255,255,0.9);">Search from thousands of hotels worldwide</p>
    </div>
</div>

<section class="section-sm">
    <div class="container">
        <!-- Filter Section -->
        <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: var(--shadow-md); margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">Filters</h3>
            <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Destination</label>
                    <select name="destination" id="destination" class="form-select">
                        <option value="">All Destinations</option>
                        <?php while ($dest = $destinations->fetch_assoc()): ?>
                            <option value="<?php echo $dest['id']; ?>" <?php echo $destinationId == $dest['id'] ? 'selected' : ''; ?>>
                                <?php echo $dest['name'] . ', ' . $dest['country']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="domestic" <?php echo $type === 'domestic' ? 'selected' : ''; ?>>Domestic</option>
                        <option value="international" <?php echo $type === 'international' ? 'selected' : ''; ?>>International</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Min Price</label>
                    <input type="number" name="min_price" id="minPrice" class="form-input" placeholder="Min" value="<?php echo $minPrice; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Max Price</label>
                    <input type="number" name="max_price" id="maxPrice" class="form-input" placeholder="Max" value="<?php echo $maxPrice; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Min Rating</label>
                    <select name="min_rating" id="rating" class="form-select">
                        <option value="">Any Rating</option>
                        <option value="4" <?php echo $minRating == '4' ? 'selected' : ''; ?>>4+ Stars</option>
                        <option value="3" <?php echo $minRating == '3' ? 'selected' : ''; ?>>3+ Stars</option>
                        <option value="2" <?php echo $minRating == '2' ? 'selected' : ''; ?>>2+ Stars</option>
                    </select>
                </div>
                
                <!-- Hidden fields to preserve search params -->
                <?php if ($checkIn): ?><input type="hidden" name="check_in" value="<?php echo $checkIn; ?>"><?php endif; ?>
                <?php if ($checkOut): ?><input type="hidden" name="check_out" value="<?php echo $checkOut; ?>"><?php endif; ?>
                <?php if ($guests): ?><input type="hidden" name="guests" value="<?php echo $guests; ?>"><?php endif; ?>
                
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Apply Filters</button>
                </div>
            </form>
        </div>
        
        <!-- Results Count -->
        <div style="margin-bottom: 1.5rem;">
            <h3 style="color: var(--gray-700);">
                <?php echo $hotels->num_rows; ?> Hotels Found
            </h3>
            <?php if ($checkIn && $checkOut): ?>
                <p style="color: var(--gray-600);">
                    📅 <?php echo formatDate($checkIn); ?> - <?php echo formatDate($checkOut); ?>
                    <?php if ($guests): ?> • <?php echo $guests; ?> Guest(s)<?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        
        <!-- Hotels List -->
        <?php if ($hotels->num_rows > 0): ?>
            <?php while ($hotel = $hotels->fetch_assoc()): 
                // Apply price filter if needed
                $hotelMinPrice = $hotel['min_price'] ?? 0;
                if ($minPrice && $hotelMinPrice < $minPrice) continue;
                if ($maxPrice && $hotelMinPrice > $maxPrice) continue;
                if ($minRating && ($hotel['avg_rating'] ?? 0) < $minRating) continue;
            ?>
                <div class="hotel-card">
                    <img src="<?php echo $hotel['image'] ?: 'https://via.placeholder.com/300x250?text='.urlencode($hotel['name']); ?>" 
                         alt="<?php echo htmlspecialchars($hotel['name']); ?>" 
                         class="hotel-card-image">
                    
                    <div class="hotel-card-content">
                        <div class="hotel-card-header">
                            <h3 class="hotel-card-title"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                            <div class="hotel-location">
                                📍 <?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['country']); ?>
                                <span class="badge <?php echo $hotel['destination_type'] === 'domestic' ? 'badge-primary' : 'badge-success'; ?>" 
                                      style="margin-left: 0.5rem;">
                                    <?php echo ucfirst($hotel['destination_type']); ?>
                                </span>
                            </div>
                            
                            <?php if ($hotel['avg_rating']): ?>
                                <div class="hotel-rating">
                                    <?php echo getStarRating($hotel['avg_rating']); ?>
                                    <span style="color: var(--gray-600); font-size: 0.875rem;">
                                        <?php echo number_format($hotel['avg_rating'], 1); ?>
                                        (<?php echo $hotel['review_count']; ?> reviews)
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <p style="color: var(--gray-600); margin: 1rem 0;">
                            <?php echo htmlspecialchars(substr($hotel['description'], 0, 150)) . '...'; ?>
                        </p>
                        
                        <?php if ($hotel['amenities']): ?>
                            <div class="hotel-amenities">
                                <?php 
                                $amenities = array_slice(explode(',', $hotel['amenities']), 0, 5);
                                foreach ($amenities as $amenity): 
                                ?>
                                    <span class="amenity-badge"><?php echo trim($amenity); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="hotel-card-footer">
                            <div>
                                <div class="hotel-price">
                                    <span data-price-bdt="<?php echo $hotelMinPrice; ?>">
                                        <?php echo formatCurrency($hotelMinPrice); ?>
                                    </span>
                                </div>
                                <div class="hotel-price-label">per night</div>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/hotel-details.php?id=<?php echo $hotel['id']; ?><?php 
                                if ($checkIn) echo '&check_in=' . $checkIn;
                                if ($checkOut) echo '&check_out=' . $checkOut;
                                if ($guests) echo '&guests=' . $guests;
                            ?>" class="btn btn-primary">
                                View Details & Book
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem; background: white; border-radius: 1rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🏨</div>
                <h3 style="color: var(--gray-700); margin-bottom: 0.5rem;">No Hotels Found</h3>
                <p style="color: var(--gray-600);">Try adjusting your filters or search criteria</p>
                <a href="<?php echo SITE_URL; ?>/hotels.php" class="btn btn-primary" style="margin-top: 1rem;">
                    Clear Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
