<?php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/password_utils.php';
require_once __DIR__ . '/../config/config.php';

Session::start();

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to update profile']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate input
if (empty($name) || empty($email) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

if (!isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (!isValidPhone($phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $userId = Session::getUserId();

    // Get current user data
    $stmt = $conn->prepare("SELECT email, password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $currentUser = $stmt->get_result()->fetch_assoc();

    // Check if email changed and if it's already taken by another user
    if ($email !== $currentUser['email']) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $userId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already in use by another account']);
            exit;
        }
    }

    // Handle password change if requested
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        // Verify all password fields are filled
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all password fields to change password']);
            exit;
        }

        // Verify current password
        if (!verifyPassword($currentPassword, $currentUser['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }

        // Validate new password
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
            exit;
        }

        // Update with new password
        $hashedPassword = hashPassword($newPassword);
        $updateStmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
        $updateStmt->bind_param("ssssi", $name, $email, $phone, $hashedPassword, $userId);
    } else {
        // Update without changing password
        $updateStmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $updateStmt->bind_param("sssi", $name, $email, $phone, $userId);
    }

    if ($updateStmt->execute()) {
        // Update session data
        Session::set('user_name', $name);
        Session::set('user_email', $email);
        Session::set('user_phone', $phone);

        Session::setFlash('success', 'Profile updated successfully!');

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile. Please try again.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>