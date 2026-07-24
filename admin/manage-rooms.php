<?php
$pageTitle = 'Manage Rooms';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_room') {
        $hotel_id = (int) $_POST['hotel_id'];
        $room_type = sanitize($_POST['room_type']);
        $description = sanitize($_POST['description']);
        $price = (float) $_POST['price_per_night'];
        $max_guests = (int) $_POST['max_guests'];
        $total_rooms = (int) $_POST['total_rooms'];

        // available_rooms initially equals total_rooms
        $available_rooms = $total_rooms;

        $stmt = $conn->prepare("INSERT INTO rooms (hotel_id, room_type, description, price_per_night, max_guests, total_rooms, available_rooms, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("issdiii", $hotel_id, $room_type, $description, $price, $max_guests, $total_rooms, $available_rooms);

        if ($stmt->execute()) {
            Session::setFlash('success', 'Room added successfully');
        } else {
            Session::setFlash('error', 'Error adding room: ' . $conn->error);
        }
        redirect('manage-rooms.php');

    } elseif ($action === 'edit_room') {
        $id = (int) $_POST['room_id'];
        $room_type = sanitize($_POST['room_type']);
        $description = sanitize($_POST['description']);
        $price = (float) $_POST['price_per_night'];
        $max_guests = (int) $_POST['max_guests'];
        $total_rooms = (int) $_POST['total_rooms'];

        $stmt = $conn->prepare("UPDATE rooms SET room_type=?, description=?, price_per_night=?, max_guests=?, total_rooms=? WHERE id=?");
        $stmt->bind_param("ssdiii", $room_type, $description, $price, $max_guests, $total_rooms, $id);

        if ($stmt->execute()) {
            Session::setFlash('success', 'Room updated successfully');
        } else {
            Session::setFlash('error', 'Error updating room: ' . $conn->error);
        }
        redirect('manage-rooms.php');

    } elseif ($action === 'delete_room') {
        $id = (int) $_POST['room_id'];
        $stmt = $conn->prepare("UPDATE rooms SET is_active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            Session::setFlash('success', 'Room deactivated successfully');
        } else {
            Session::setFlash('error', 'Error deleting room: ' . $conn->error);
        }
        redirect('manage-rooms.php');
    }
}

// Get all rooms
$rooms = $conn->query("
    SELECT r.*, h.name as hotel_name
    FROM rooms r
    JOIN hotels h ON r.hotel_id = h.id
    WHERE r.is_active = 1
    ORDER BY h.name, r.room_type
");
?>

<div class="admin-container">
    <?php include __DIR__ . '/includes/admin-sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1 style="margin: 0;">Manage Rooms</h1>
            <button class="btn btn-primary" onclick="openModal('addRoomModal')">
                + Add New Room
            </button>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hotel</th>
                            <th>Room Type</th>
                            <th>Price/Night</th>
                            <th>Max Guests</th>
                            <th>Total Rooms</th>
                            <th>Available</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rooms->num_rows > 0): ?>
                            <?php while ($room = $rooms->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $room['id']; ?></td>
                                    <td><?php echo htmlspecialchars($room['hotel_name']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($room['room_type']); ?></strong></td>
                                    <td style="font-weight: 600;"><?php echo formatCurrency($room['price_per_night']); ?></td>
                                    <td><?php echo $room['max_guests']; ?></td>
                                    <td><?php echo $room['total_rooms']; ?></td>
                                    <td><?php echo $room['available_rooms']; ?></td>
                                    <td>
                                        <?php if ($room['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-error">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-outline btn-sm"
                                                data-room='<?php echo htmlspecialchars(json_encode($room), ENT_QUOTES, 'UTF-8'); ?>'
                                                onclick="editRoom(this)" title="Edit">
                                                ✏️
                                            </button>
                                            <?php if ($room['is_active']): ?>
                                                <button class="btn btn-outline btn-sm" style="color: red; border-color: red;"
                                                    onclick="deleteRoom(<?php echo $room['id']; ?>)" title="Delete">
                                                    🗑️
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                                    No rooms found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Room Modal -->
<div id="addRoomModal" class="modal">
    <div style="background: white; width: 500px; max-width: 90%; margin: 50px auto; padding: 2rem; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Add New Room</h2>
            <button onclick="closeModal('addRoomModal')"
                style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <form action="" method="POST">
            <input type="hidden" name="action" value="add_room">

            <div class="form-group">
                <label>Select Hotel</label>
                <select name="hotel_id" class="form-input" required>
                    <?php
                    $all_hotels = $conn->query("SELECT id, name FROM hotels ORDER BY name");
                    while ($h = $all_hotels->fetch_assoc()) {
                        echo "<option value='{$h['id']}'>{$h['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Room Type</label>
                <input type="text" name="room_type" class="form-input" placeholder="e.g. Deluxe Suite" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-input" rows="2"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Price Per Night</label>
                    <input type="number" name="price_per_night" class="form-input" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Max Guests</label>
                    <input type="number" name="max_guests" class="form-input" min="1" value="2" required>
                </div>
            </div>

            <div class="form-group">
                <label>Total Rooms</label>
                <input type="number" name="total_rooms" class="form-input" min="1" value="5" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Add Room</button>
        </form>
    </div>
</div>

<!-- Edit Room Modal -->
<div id="editRoomModal" class="modal">
    <div style="background: white; width: 500px; max-width: 90%; margin: 50px auto; padding: 2rem; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Edit Room</h2>
            <button onclick="closeModal('editRoomModal')"
                style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <form action="" method="POST">
            <input type="hidden" name="action" value="edit_room">
            <input type="hidden" name="room_id" id="edit_room_id">

            <div class="form-group">
                <label>Room Type</label>
                <input type="text" name="room_type" id="edit_room_type" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" class="form-input" rows="2"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Price Per Night</label>
                    <input type="number" name="price_per_night" id="edit_price" class="form-input" min="0" step="0.01"
                        required>
                </div>
                <div class="form-group">
                    <label>Max Guests</label>
                    <input type="number" name="max_guests" id="edit_guests" class="form-input" min="1" required>
                </div>
            </div>

            <div class="form-group">
                <label>Total Rooms</label>
                <input type="number" name="total_rooms" id="edit_total" class="form-input" min="1" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Update Room</button>
        </form>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteRoomForm" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_room">
    <input type="hidden" name="room_id" id="delete_room_id">
</form>

<script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function editRoom(btn) {
        const room = JSON.parse(btn.dataset.room);
        document.getElementById('edit_room_id').value = room.id;
        document.getElementById('edit_room_type').value = room.room_type;
        document.getElementById('edit_description').value = room.description;
        document.getElementById('edit_price').value = room.price_per_night;
        document.getElementById('edit_guests').value = room.max_guests;
        document.getElementById('edit_total').value = room.total_rooms;

        openModal('editRoomModal');
    }

    function deleteRoom(id) {
        if (confirm('Are you sure you want to delete this room?')) {
            document.getElementById('delete_room_id').value = id;
            document.getElementById('deleteRoomForm').submit();
        }
    }

    window.onclick = function (event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    }
</script>
</body>

</html>