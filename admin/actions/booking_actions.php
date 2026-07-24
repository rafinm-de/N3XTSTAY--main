<?php
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/config.php';

Session::start();

// Only admins can use this
requireAdmin();

$action   = $_GET['action']   ?? '';
$id       = intval($_GET['id'] ?? 0);
$redirect = $_GET['redirect'] ?? 'manage-bookings';

if (!$id) {
    Session::setFlash('error', 'Invalid booking ID.');
    header('Location: ' . SITE_URL . '/admin/' . $redirect . '.php');
    exit;
}

$db   = Database::getInstance();
$conn = $db->getConnection();

if ($action === 'update_status') {
    $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
    $status  = $_GET['status'] ?? '';

    if (!in_array($status, $allowed)) {
        Session::setFlash('error', 'Invalid status value.');
        header('Location: ' . SITE_URL . '/admin/' . $redirect . '.php');
        exit;
    }

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        Session::setFlash('success', 'Booking #' . $id . ' status updated to ' . ucfirst($status) . '.');
    } else {
        Session::setFlash('error', 'Failed to update booking status: ' . $conn->error);
    }

} elseif ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        Session::setFlash('success', 'Booking #' . $id . ' deleted successfully.');
    } else {
        Session::setFlash('error', 'Failed to delete booking: ' . $conn->error);
    }

} else {
    Session::setFlash('error', 'Unknown action.');
}

header('Location: ' . SITE_URL . '/admin/' . $redirect . '.php');
exit;
?>
