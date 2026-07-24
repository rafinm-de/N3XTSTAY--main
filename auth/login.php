<?php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/password_utils.php';
require_once __DIR__ . '/../config/config.php';

Session::start();

// Set JSON header for AJAX requests
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$email = sanitize($_POST['login_email'] ?? '');
$password = $_POST['login_password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit;
}

if (!isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Prepare statement
    $stmt = $conn->prepare("SELECT id, name, email, phone, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    $user = $result->fetch_assoc();

    // Verify password using utility function
    if (!verifyPassword($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    // Set session data
    Session::set('user_id', $user['id']);
    Session::set('user_name', $user['name']);
    Session::set('user_email', $user['email']);
    Session::set('user_phone', $user['phone']);
    Session::set('user_role', $user['role']);

    Session::setFlash('success', 'Welcome back, ' . $user['name'] . '!');

    // Determine redirect URL based on role
    $redirectUrl = SITE_URL . '/index.php';
    if ($user['role'] === 'admin') {
        $redirectUrl = SITE_URL . '/admin/dashboard.php';
    }

    $logMsg = date('Y-m-d H:i:s') . " - Login success: " . $email . " | Role: " . $user['role'] . " | Redirect: " . $redirectUrl . "\n";

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect_url' => $redirectUrl,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>