<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_user') {
        $id = (int) $_POST['user_id'];
        $role = sanitize($_POST['role']);

        // Prevent deleting own admin access
        if ($id == Session::getUserId()) {
            Session::setFlash('error', 'You cannot change your own role');
            redirect('manage-users.php');
        }

        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $id);

        if ($stmt->execute()) {
            Session::setFlash('success', 'User role updated successfully');
        } else {
            Session::setFlash('error', 'Error updating user: ' . $conn->error);
        }
        redirect('manage-users.php');

    } elseif ($action === 'delete_user') {
        $id = (int) $_POST['user_id'];

        if ($id == Session::getUserId()) {
            Session::setFlash('error', 'You cannot delete yourself');
            redirect('manage-users.php');
        }

        // Check for bookings
        $res = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE user_id = $id");
        $row = $res->fetch_assoc();

        if ($row['count'] > 0) {
            Session::setFlash('error', 'Cannot delete user with existing bookings');
            redirect('manage-users.php');
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            Session::setFlash('success', 'User deleted successfully');
        } else {
            Session::setFlash('error', 'Error deleting user: ' . $conn->error);
        }
        redirect('manage-users.php');
    }
}

// Get all users
$users = $conn->query("
    SELECT u.*,
           (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as booking_count
    FROM users u
    ORDER BY u.created_at DESC
");
?>

<div class="admin-container">
    <?php include __DIR__ . '/includes/admin-sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1 style="margin: 0;">Manage Users</h1>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Bookings</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge badge-primary">Admin</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['booking_count']; ?></td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                    <td>
                                        <button class="btn btn-outline btn-sm"
                                            data-user='<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>'
                                            onclick="editUser(this)" title="Edit Role">
                                            Key 🔑
                                        </button>
                                        <?php if ($user['id'] != Session::getUserId()): ?>
                                            <button class="btn btn-outline btn-sm" style="color: red; border-color: red;"
                                                onclick="deleteUser(<?php echo $user['id']; ?>)" title="Delete User">
                                                🗑️
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                                    No users found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Edit User Role</h2>
            <button onclick="closeModal('editUserModal')"
                style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <form action="" method="POST">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="user_id" id="edit_user_id">

            <p><strong>User:</strong> <span id="display_user_name"></span></p>

            <div class="form-group">
                <label>Role</label>
                <select name="role" id="edit_role" class="form-input">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Update Role</button>
        </form>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteUserForm" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_user">
    <input type="hidden" name="user_id" id="delete_user_id">
</form>

<script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function editUser(btn) {
        const user = JSON.parse(btn.dataset.user);
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('display_user_name').textContent = user.name;
        document.getElementById('edit_role').value = user.role;
        openModal('editUserModal');
    }

    function deleteUser(id) {
        if (confirm('Are you sure you want to delete this user? This cannot be undone.')) {
            document.getElementById('delete_user_id').value = id;
            document.getElementById('deleteUserForm').submit();
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