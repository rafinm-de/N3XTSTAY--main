<?php
$pageTitle = 'Hotel Details';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$hotelId = $_GET['id'] ?? 0;
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 2;

// Get hotel details
$stmt = $conn->prepare("
    SELECT h.*, d.name as destination_name, d.country, d.type as destination_type,
           (SELECT AVG(rating) FROM reviews WHERE hotel_id = h.id) as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE hotel_id = h.id) as review_count
    FROM hotels h
    LEFT JOIN destinations d ON h.destination_id = d.id
    WHERE h.id = ? AND h.is_active = 1
");
$stmt->bind_param("i", $hotelId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . SITE_URL . '/hotels.php');
    exit;
}

$hotel = $result->fetch_assoc();

// Get available rooms
$roomsQuery = $conn->prepare("
    SELECT * FROM rooms 
    WHERE hotel_id = ? AND is_active = 1 AND available_rooms > 0 
    ORDER BY price_per_night ASC
");
$roomsQuery->bind_param("i", $hotelId);
$roomsQuery->execute();
$rooms = $roomsQuery->get_result();

// Get reviews
$reviewsQuery = $conn->prepare("
    SELECT r.*, u.name as user_name 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.hotel_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$reviewsQuery->bind_param("i", $hotelId);
$reviewsQuery->execute();
$reviews = $reviewsQuery->get_result();
?>

<script>
    document.body.dataset.loggedIn = '<?php echo Session::isLoggedIn() ? 'true' : 'false'; ?>';
</script>

<!-- Hotel Hero -->
<div style="position: relative; height: 400px; overflow: hidden;">
    <img src="<?php echo $hotel['image'] ?: 'https://via.placeholder.com/1200x400?text=' . urlencode($hotel['name']); ?>"
        alt="<?php echo htmlspecialchars($hotel['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
    <div
        style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); padding: 2rem 0;">
        <div class="container">
            <h1 style="color: white; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($hotel['name']); ?></h1>
            <div style="color: rgba(255,255,255,0.9); display: flex; align-items: center; gap: 1rem;">
                <span>📍 <?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['country']); ?></span>
                <?php if ($hotel['avg_rating']): ?>
                    <span>⭐ <?php echo number_format($hotel['avg_rating'], 1); ?> (<?php echo $hotel['review_count']; ?>
                        reviews)</span>
                <?php endif; ?>
                <span
                    class="badge <?php echo $hotel['destination_type'] === 'domestic' ? 'badge-primary' : 'badge-success'; ?>">
                    <?php echo ucfirst($hotel['destination_type']); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<section class="section-sm">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
            <!-- Main Content -->
            <div>
                <!-- Description -->
                <div
                    style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1rem;">About This Property</h2>
                    <p style="color: var(--gray-600); line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($hotel['description'])); ?>
                    </p>
                </div>

                <!-- Amenities -->
                <?php if ($hotel['amenities']): ?>
                    <div
                        style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                        <h2 style="margin-bottom: 1rem;">Amenities</h2>
                        <div
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                            <?php
                            $amenities = explode(',', $hotel['amenities']);
                            foreach ($amenities as $amenity):
                                ?>
                                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray-700);">
                                    ✓ <span><?php echo trim($amenity); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Available Rooms -->
                <div
                    style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1.5rem;">Available Rooms</h2>

                    <?php if ($rooms->num_rows > 0): ?>
                        <?php while ($room = $rooms->fetch_assoc()): ?>
                            <div style="border: 2px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem; transition: var(--transition-base);"
                                onmouseover="this.style.borderColor='var(--primary)'"
                                onmouseout="this.style.borderColor='var(--gray-200)'">
                                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1.5rem;">
                                    <img src="<?php echo $room['image'] ?: 'https://via.placeholder.com/200x150?text=Room'; ?>"
                                        alt="<?php echo htmlspecialchars($room['room_type']); ?>"
                                        style="width: 100%; height: 150px; object-fit: cover; border-radius: 0.5rem;">

                                    <div>
                                        <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($room['room_type']); ?>
                                        </h3>
                                        <p style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 0.75rem;">
                                            <?php echo htmlspecialchars($room['description']); ?>
                                        </p>

                                        <div
                                            style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; font-size: 0.875rem; color: var(--gray-600);">
                                            <span>👥 Max <?php echo $room['max_guests']; ?> guests</span>
                                            <span>🛏️ <?php echo $room['bed_type']; ?></span>
                                            <span>📏 <?php echo $room['room_size']; ?></span>
                                            <span>🏨 <?php echo $room['available_rooms']; ?> rooms available</span>
                                        </div>

                                        <?php if ($room['amenities']): ?>
                                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                                                <?php
                                                $roomAmenities = array_slice(explode(',', $room['amenities']), 0, 4);
                                                foreach ($roomAmenities as $amenity):
                                                    ?>
                                                    <span class="amenity-badge"><?php echo trim($amenity); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                                            <div>
                                                <div class="hotel-price" style="font-size: 1.75rem;">
                                                    <span data-price-bdt="<?php echo $room['price_per_night']; ?>">
                                                        <?php echo formatCurrency($room['price_per_night']); ?>
                                                    </span>
                                                </div>
                                                <div class="hotel-price-label">per night</div>
                                            </div>

                                            <button
                                                onclick="handleBookNow(<?php echo $hotel['id']; ?>, <?php echo $room['id']; ?>)"
                                                class="btn btn-primary btn-lg">
                                                Book Now
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: var(--gray-600); text-align: center; padding: 2rem;">
                            No rooms available at the moment.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Reviews -->
                <?php if ($reviews->num_rows > 0): ?>
                    <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow-md);">
                        <h2 style="margin-bottom: 1.5rem;">Guest Reviews</h2>

                        <?php while ($review = $reviews->fetch_assoc()): ?>
                            <div
                                style="padding: 1.5rem; border-bottom: 1px solid var(--gray-200); last-of-type:border-bottom: none;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                                    <div>
                                        <strong
                                            style="color: var(--gray-900);"><?php echo htmlspecialchars($review['user_name']); ?></strong>
                                        <?php if ($review['is_verified']): ?>
                                            <span class="badge badge-success"
                                                style="font-size: 0.75rem; margin-left: 0.5rem;">Verified</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php echo getStarRating($review['rating']); ?>
                                </div>

                                <?php if ($review['title']): ?>
                                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem;">
                                        <?php echo htmlspecialchars($review['title']); ?></h4>
                                <?php endif; ?>

                                <p style="color: var(--gray-600); margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($review['comment']); ?>
                                </p>

                                <small style="color: var(--gray-500);">
                                    <?php echo timeAgo($review['created_at']); ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div>
                <div
                    style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: var(--shadow-lg); position: sticky; top: 100px;">
                    <h3 style="margin-bottom: 1.5rem;">Check Availability</h3>

                    <form id="checkAvailabilityForm">
                        <div class="form-group">
                            <label class="form-label">Check-in</label>
                            <input type="date" name="check_in" class="form-input" value="<?php echo $checkIn; ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Check-out</label>
                            <input type="date" name="check_out" class="form-input" value="<?php echo $checkOut; ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Guests</label>
                            <input type="number" name="guests" class="form-input" min="1" max="10"
                                value="<?php echo $guests; ?>" required>
                        </div>

                        <button type="button" onclick="updateAvailability()" class="btn btn-outline"
                            style="width: 100%;">
                            Check Availability
                        </button>
                    </form>

                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-200);">
                        <h4 style="margin-bottom: 0.5rem;">Contact Information</h4>
                        <p style="color: var(--gray-600); font-size: 0.875rem;">
                            📍 <?php echo htmlspecialchars($hotel['address']); ?><br>
                            📧 <?php echo ADMIN_EMAIL; ?><br>
                            📞 +880 1234567890
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function handleBookNow(hotelId, roomId) {
        const checkIn = document.querySelector('input[name="check_in"]').value;
        const checkOut = document.querySelector('input[name="check_out"]').value;
        const guests = document.querySelector('input[name="guests"]').value;

        if (!checkIn || !checkOut) {
            alert('Please select check-in and check-out dates');
            return;
        }

        checkAuthAndBook(hotelId, roomId, checkIn, checkOut, guests, 1);
    }

    function updateAvailability() {
        const checkIn = document.querySelector('input[name="check_in"]').value;
        const checkOut = document.querySelector('input[name="check_out"]').value;
        const guests = document.querySelector('input[name="guests"]').value;

        const url = new URL(window.location.href);
        url.searchParams.set('check_in', checkIn);
        url.searchParams.set('check_out', checkOut);
        url.searchParams.set('guests', guests);

        window.location.href = url.toString();
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>