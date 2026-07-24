<?php
// Database Configuration - support both Railway and custom env vars
define('DB_HOST', getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'rafinkhan');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'hotel_booking');

// Application Configuration
define('SITE_URL', getenv('SITE_URL') ?: 'http://127.0.0.1:8080');
define('SITE_NAME', getenv('SITE_NAME') ?: 'N3XTSTAY');
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@hotelbooking.com');

// Payment Gateway Configuration
// bKash Configuration
define('BKASH_APP_KEY', 'your_bkash_app_key');
define('BKASH_APP_SECRET', 'your_bkash_app_secret');
define('BKASH_USERNAME', 'your_bkash_username');
define('BKASH_PASSWORD', 'your_bkash_password');
define('BKASH_BASE_URL', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta');

// Nagad Configuration
define('NAGAD_MERCHANT_ID', 'your_nagad_merchant_id');
define('NAGAD_MERCHANT_NUMBER', 'your_nagad_merchant_number');
define('NAGAD_PUBLIC_KEY', 'your_nagad_public_key');
define('NAGAD_PRIVATE_KEY', 'your_nagad_private_key');
define('NAGAD_BASE_URL', 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs');

// Card Payment Configuration (SSLCommerz)
define('SSLC_STORE_ID', 'your_sslcommerz_store_id');
define('SSLC_STORE_PASSWORD', 'your_sslcommerz_store_password');
define('SSLC_TESTMODE', true);

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
}

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Error Reporting
$env = getenv('APP_ENV') ?: 'development';
if ($env === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
