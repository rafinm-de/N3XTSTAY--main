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
$name = sanitize($_POST['register_name'] ?? '');
$email = sanitize($_POST['register_email'] ?? '');
$phone = sanitize($_POST['register_phone'] ?? '');
$password = $_POST['register_password'] ?? '';
$confirmPassword = $_POST['register_confirm_password'] ?? '';

// Validate input
if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
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

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    // Hash password using utility function
    $hashedPassword = hashPassword($password);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')");
    $stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);

    if ($stmt->execute()) {
        $userId = $conn->insert_id;

        // Set session data
        Session::set('user_id', $userId);
        Session::set('user_name', $name);
        Session::set('user_email', $email);
        Session::set('user_phone', $phone);
        Session::set('user_role', 'user');

        Session::setFlash('success', 'Account created successfully! Welcome, ' . $name . '!');

        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'user'
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>