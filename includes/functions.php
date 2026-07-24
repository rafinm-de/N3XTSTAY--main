<?php
/**
 * Common Utility Functions
 */

// Sanitize input
function sanitize($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone
function isValidPhone($phone)
{
    // Allow any number with 6-15 digits, optional +
    return preg_match('/^\+?[0-9]{6,15}$/', $phone);
}

// Generate booking reference
function generateBookingReference()
{
    return 'BK' . date('Ymd') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
}

// Generate transaction ID
function generateTransactionId()
{
    return 'TXN' . date('YmdHis') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
}

// Format currency
function formatCurrency($amount, $currency = 'BDT')
{
    if ($currency === 'BDT') {
        return '৳' . number_format($amount, 2);
    }
    return $currency . ' ' . number_format($amount, 2);
}

// Format date
function formatDate($date, $format = 'd M Y')
{
    return date($format, strtotime($date));
}

// Calculate nights between dates
function calculateNights($checkIn, $checkOut)
{
    $date1 = new DateTime($checkIn);
    $date2 = new DateTime($checkOut);
    $interval = $date1->diff($date2);
    return $interval->days;
}

// Redirect function
function redirect($url)
{
    header("Location: " . $url);
    exit();
}

// Check if user is logged in
function requireLogin()
{
    if (!Session::isLoggedIn()) {
        redirect(SITE_URL . '/auth/login.php');
    }
}

// Check if user is admin
function requireAdmin()
{
    if (!Session::isAdmin()) {
        redirect(SITE_URL . '/index.php');
    }
}

// Get user initials for avatar
function getUserInitials($name)
{
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return substr($initials, 0, 2);
}

// Upload image
function uploadImage($file, $directory = 'assets/images/uploads/')
{
    $targetDir = __DIR__ . '/../' . $directory;

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $fileName = uniqid() . '.' . $imageFileType;
    $targetFile = $targetDir . $fileName;

    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }

    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return false;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return false;
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $directory . $fileName;
    }

    return false;
}

// Get star rating HTML
function getStarRating($rating, $maxStars = 5)
{
    $html = '<div class="star-rating">';
    for ($i = 1; $i <= $maxStars; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="star filled">★</i>';
        } elseif ($i <= ceil($rating) && $rating - floor($rating) >= 0.5) {
            $html .= '<i class="star half">★</i>';
        } else {
            $html .= '<i class="star">☆</i>';
        }
    }
    $html .= '</div>';
    return $html;
}

// Timeago function
function timeAgo($datetime)
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 2592000) {
        return floor($diff / 86400) . ' days ago';
    } elseif ($diff < 31104000) {
        return floor($diff / 2592000) . ' months ago';
    } else {
        return floor($diff / 31104000) . ' years ago';
    }
}

// Send email notification
function sendEmail($to, $subject, $message)
{
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . SITE_NAME . ' <' . ADMIN_EMAIL . '>' . "\r\n";

    return mail($to, $subject, $message, $headers);
}

// Get booking confirmation email template
function getBookingConfirmationEmail($bookingData)
{
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
            .content { background: #f9f9f9; padding: 30px; }
            .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
            .footer { text-align: center; padding: 20px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . SITE_NAME . '</h1>
                <p>Booking Confirmation</p>
            </div>
            <div class="content">
                <h2>Dear ' . $bookingData['guest_name'] . ',</h2>
                <p>Thank you for your booking! Your reservation has been confirmed.</p>
                
                <div class="booking-details">
                    <h3>Booking Details</h3>
                    <div class="detail-row">
                        <strong>Booking Reference:</strong>
                        <span>' . $bookingData['booking_reference'] . '</span>
                    </div>
                    <div class="detail-row">
                        <strong>Hotel:</strong>
                        <span>' . $bookingData['hotel_name'] . '</span>
                    </div>
                    <div class="detail-row">
                        <strong>Room Type:</strong>
                        <span>' . $bookingData['room_type'] . '</span>
                    </div>
                    <div class="detail-row">
                        <strong>Check-in:</strong>
                        <span>' . formatDate($bookingData['check_in']) . '</span>
                    </div>
                    <div class="detail-row">
                        <strong>Check-out:</strong>
                        <span>' . formatDate($bookingData['check_out']) . '</span>
                    </div>
                    <div class="detail-row">
                        <strong>Guests:</strong>
                        <span>' . $bookingData['num_guests'] . '</span>
                    </div>
                    <div class="detail-row">
                        <strong>Total Amount:</strong>
                        <span><strong>' . formatCurrency($bookingData['total_amount']) . '</strong></span>
                    </div>
                </div>
                
                <p>We look forward to welcoming you!</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';

    return $html;
}
?>