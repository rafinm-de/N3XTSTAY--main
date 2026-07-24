<?php
$pageTitle = 'Manage Hotels';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_hotel') {
        $name = sanitize($_POST['name']);
        $destination_id = (int) $_POST['destination_id'];
        $address = sanitize($_POST['address']);
        $city = sanitize($_POST['city']);
        $country = sanitize($_POST['country']);
        $description = sanitize($_POST['description']);
        $amenities = sanitize($_POST['amenities']);
        $star_rating = (float) $_POST['star_rating'];

        // Image Upload
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $imagePath = uploadImage($_FILES['image'], 'assets/images/hotels/');
        }

        if (!$imagePath) {
            $imagePath = 'https://via.placeholder.com/400x300?text=Hotel';
        }

        $stmt = $conn->prepare("INSERT INTO hotels (destination_id, name, address, city, country, description, amenities, star_rating, image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("issssssds", $destination_id, $name, $address, $city, $country, $description, $amenities, $star_rating, $imagePath);

        if ($stmt->execute()) {
            Session::setFlash('success', 'Hotel added successfully');
        } else {
            error_log("Error adding hotel: " . $stmt->error);
            Session::setFlash('error', 'Error adding hotel: ' . $conn->error);
        }
        redirect('manage-hotels.php'); // Redirect to self

    } elseif ($action === 'edit_hotel') {
        $id = (int) $_POST['hotel_id'];
        $name = sanitize($_POST['name']);
        $destination_id = (int) $_POST['destination_id'];
        $address = sanitize($_POST['address']);
        $city = sanitize($_POST['city']);
        $country = sanitize($_POST['country']);
        $description = sanitize($_POST['description']);
        $amenities = sanitize($_POST['amenities']);
        $star_rating = (float) $_POST['star_rating'];

        // Handle Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $imagePath = uploadImage($_FILES['image'], 'assets/images/hotels/');
            if ($imagePath) {
                $stmt = $conn->prepare("UPDATE hotels SET destination_id=?, name=?, address=?, city=?, country=?, description=?, amenities=?, star_rating=?, image=? WHERE id=?");
                $stmt->bind_param("issssssdsi", $destination_id, $name, $address, $city, $country, $description, $amenities, $star_rating, $imagePath, $id);
            } else {
                $stmt = $conn->prepare("UPDATE hotels SET destination_id=?, name=?, address=?, city=?, country=?, description=?, amenities=?, star_rating=? WHERE id=?");
                $stmt->bind_param("issssssdi", $destination_id, $name, $address, $city, $country, $description, $amenities, $star_rating, $id);
            }
        } else {
            $stmt = $conn->prepare("UPDATE hotels SET destination_id=?, name=?, address=?, city=?, country=?, description=?, amenities=?, star_rating=? WHERE id=?");
            $stmt->bind_param("issssssdi", $destination_id, $name, $address, $city, $country, $description, $amenities, $star_rating, $id);
        }

        if ($stmt->execute()) {
            Session::setFlash('success', 'Hotel updated successfully');
        } else {
            Session::setFlash('error', 'Error updating hotel: ' . $conn->error);
        }
        redirect('manage-hotels.php');

    } elseif ($action === 'delete_hotel') {
        $id = (int) $_POST['hotel_id'];
        $stmt = $conn->prepare("UPDATE hotels SET is_active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            Session::setFlash('success', 'Hotel deactivated successfully');
        } else {
            error_log("Error deleting hotel: " . $stmt->error);
            Session::setFlash('error', 'Error deleting hotel: ' . $conn->error);
        }
        redirect('manage-hotels.php');
    }
}

// Get all hotels
$hotels = $conn->query("
    SELECT h.*, d.name as destination_name,
           (SELECT COUNT(*) FROM rooms WHERE hotel_id = h.id) as room_count,
           (SELECT COUNT(*) FROM bookings WHERE hotel_id = h.id) as booking_count
    FROM hotels h
    LEFT JOIN destinations d ON h.destination_id = d.id
    WHERE h.is_active = 1
    ORDER BY h.created_at DESC
");
?>

<div class="admin-container">
    <?php include __DIR__ . '/includes/admin-sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1 style="margin: 0;">Manage Hotels</h1>
            <button class="btn btn-primary" onclick="openModal('addHotelModal')">
                + Add New Hotel
            </button>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
            <div style="margin-bottom: 1.5rem;">
                <input type="text" id="searchInput" class="form-input" placeholder="Search hotels..."
                    onkeyup="searchTable('searchInput', 'hotelsTable')" style="max-width: 400px;">
            </div>

            <div class="data-table">
                <table id="hotelsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Hotel Name</th>
                            <th>Destination</th>
                            <th>City</th>
                            <th>Rating</th>
                            <th>Rooms</th>
                            <th>Bookings</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($hotels->num_rows > 0): ?>
                            <?php while ($hotel = $hotels->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $hotel['id']; ?></td>
                                    <td>
                                        <img src="<?php echo !empty($hotel['image']) ? $hotel['image'] : 'https://via.placeholder.com/50'; ?>"
                                            alt="Hotel"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($hotel['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($hotel['destination_name']); ?></td>
                                    <td><?php echo htmlspecialchars($hotel['city']); ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.25rem;">
                                            <span>⭐</span>
                                            <span><?php echo $hotel['star_rating']; ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo $hotel['room_count']; ?></td>
                                    <td><?php echo $hotel['booking_count']; ?></td>
                                    <td>
                                        <?php if ($hotel['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-error">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo SITE_URL; ?>/hotel-details.php?id=<?php echo $hotel['id']; ?>"
                                                class="btn btn-outline btn-sm" target="_blank" title="View">
                                                👁️
                                            </a>
                                            <button class="btn btn-outline btn-sm"
                                                data-hotel='<?php echo htmlspecialchars(json_encode($hotel), ENT_QUOTES, 'UTF-8'); ?>'
                                                onclick="editHotel(this)" title="Edit">
                                                ✏️
                                            </button>
                                            <?php if ($hotel['is_active']): ?>
                                                <button class="btn btn-outline btn-sm" style="color: red; border-color: red;"
                                                    onclick="deleteHotel(<?php echo $hotel['id']; ?>)" title="Delete">
                                                    🗑️
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                                    No hotels found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Hotel Modal -->
<div id="addHotelModal" class="modal">
    <div
        style="background: white; width: 600px; max-width: 90%; margin: 50px auto; padding: 2rem; border-radius: 8px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Add New Hotel</h2>
            <button onclick="closeModal('addHotelModal')"
                style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_hotel">

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Destination</label>
                <select name="destination_id" class="form-input" required>
                    <?php
                    // Fetch destinations again for dropdown
                    $dests = $conn->query("SELECT * FROM destinations ORDER BY name");
                    while ($d = $dests->fetch_assoc()) {
                        echo "<option value='{$d['id']}'>{$d['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-input" rows="3"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Star Rating (1-5)</label>
                <input type="number" name="star_rating" class="form-input" step="0.5" min="1" max="5" value="4">
            </div>

            <div class="form-group">
                <label>Amenities</label>
                <textarea name="amenities" class="form-input" placeholder="Wifi, Pool, Gym..."></textarea>
            </div>

            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image" class="form-input" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Add Hotel</button>
        </form>
    </div>
</div>

<!-- Edit Hotel Modal -->
<div id="editHotelModal" class="modal">
    <div
        style="background: white; width: 600px; max-width: 90%; margin: 50px auto; padding: 2rem; border-radius: 8px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Edit Hotel</h2>
            <button onclick="closeModal('editHotelModal')"
                style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_hotel">
            <input type="hidden" name="hotel_id" id="edit_hotel_id">

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" id="edit_name" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Destination</label>
                <select name="destination_id" id="edit_destination_id" class="form-input" required>
                    <?php
                    $dests->data_seek(0); // Reset pointer
                    while ($d = $dests->fetch_assoc()) {
                        echo "<option value='{$d['id']}'>{$d['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" class="form-input" rows="3"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" id="edit_city" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" id="edit_country" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" id="edit_address" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Star Rating</label>
                <input type="number" name="star_rating" id="edit_star_rating" class="form-input" step="0.5" min="1"
                    max="5">
            </div>

            <div class="form-group">
                <label>Amenities</label>
                <textarea name="amenities" id="edit_amenities" class="form-input"></textarea>
            </div>

            <div class="form-group">
                <label>New Image (Optional)</label>
                <input type="file" name="image" class="form-input" accept="image/*">
                <small>Leave blank to keep current image</small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Update Hotel</button>
        </form>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteHotelForm" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_hotel">
    <input type="hidden" name="hotel_id" id="delete_hotel_id">
</form>

<script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function editHotel(btn) {
        const hotel = JSON.parse(btn.dataset.hotel);
        document.getElementById('edit_hotel_id').value = hotel.id;
        document.getElementById('edit_name').value = hotel.name;
        document.getElementById('edit_destination_id').value = hotel.destination_id;
        document.getElementById('edit_city').value = hotel.city;
        document.getElementById('edit_country').value = hotel.country;
        document.getElementById('edit_address').value = hotel.address;
        document.getElementById('edit_description').value = hotel.description;
        document.getElementById('edit_amenities').value = hotel.amenities;
        document.getElementById('edit_star_rating').value = hotel.star_rating;

        openModal('editHotelModal');
    }

    function deleteHotel(id) {
        if (confirm('Are you sure you want to deactivate this hotel?')) {
            document.getElementById('delete_hotel_id').value = id;
            document.getElementById('deleteHotelForm').submit();
        }
    }

    // Close modal when clicking outside
    // Close modal when clicking outside
    window.onclick = function (event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    }
</script>
</body>

</html>