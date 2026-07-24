<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/Database.php';

if (!Session::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();
$userId = Session::get('user_id');

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div class="container" style="padding: 4rem 0;">
    <h1>My Profile</h1>

    <!-- My Bookings Section -->
    <div
        style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow-md); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="margin: 0 0 0.5rem 0;">My Bookings</h2>
            <p style="margin: 0; color: var(--gray-500);">View and manage your hotel reservations</p>
        </div>
        <a href="my-bookings.php" class="btn btn-primary">
            View My Bookings
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; max-width: 1200px;">
        <!-- Profile Information -->
        <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow-md);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0;">Profile Information</h2>
                <button id="toggleEditBtn" class="btn btn-outline btn-sm" onclick="toggleEdit()">
                    <span id="editBtnText">Edit Profile</span>
                </button>
            </div>

            <form id="profileForm">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-input"
                        value="<?php echo htmlspecialchars($user['name']); ?>" readonly required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-input"
                        value="<?php echo htmlspecialchars($user['email']); ?>" readonly required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" id="phone" class="form-input"
                        value="<?php echo htmlspecialchars($user['phone']); ?>" readonly required>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-input" value="<?php echo ucfirst($user['role']); ?>" readonly
                        disabled>
                </div>

                <div id="saveSection"
                    style="display: none; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-200);">
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                        <button type="button" class="btn btn-outline" onclick="cancelEdit()"
                            style="flex: 1;">Cancel</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow-md);">
            <h2 style="margin: 0 0 1.5rem 0;">Change Password</h2>

            <form id="passwordForm">
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-input"
                        placeholder="Enter current password">
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-input" placeholder="Min. 6 characters"
                        minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-input"
                        placeholder="Re-enter new password" minlength="6">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Password</button>
            </form>


        </div>
    </div>
</div>

<script>
    let isEditing = false;

    function toggleEdit() {
        isEditing = !isEditing;
        const fields = ['name', 'email', 'phone'];
        const editBtn = document.getElementById('editBtnText');
        const saveSection = document.getElementById('saveSection');

        fields.forEach(field => {
            document.getElementById(field).readOnly = !isEditing;
        });

        if (isEditing) {
            editBtn.textContent = 'Cancel Edit';
            saveSection.style.display = 'block';
        } else {
            editBtn.textContent = 'Edit Profile';
            saveSection.style.display = 'none';
            // Reset form
            document.getElementById('profileForm').reset();
            location.reload();
        }
    }

    function cancelEdit() {
        toggleEdit();
    }

    // Handle profile form submission
    document.getElementById('profileForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);

        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/update-profile.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert('An error occurred. Please try again.');
        }
    });

    // Handle password form submission
    document.getElementById('passwordForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('confirm_password');

        if (newPassword !== confirmPassword) {
            alert('New passwords do not match');
            return;
        }

        // Add profile data to formData
        formData.append('name', document.getElementById('name').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('phone', document.getElementById('phone').value);

        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/update-profile.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                e.target.reset();
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert('An error occurred. Please try again.');
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>