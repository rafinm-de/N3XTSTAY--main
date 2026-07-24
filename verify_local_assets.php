<?php
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== VERIFYING LOCAL ASSETS ===\n";

// 1. Check a specific file (Dhaka)
$testFile = __DIR__ . '/assets/images/destinations/dhaka.jpg';
if (file_exists($testFile)) {
    echo "File 'dhaka.jpg' EXISTS. Size: " . filesize($testFile) . " bytes\n";
} else {
    echo "File 'dhaka.jpg' DOES NOT EXIST at $testFile\n";
}

// 2. Check DB Value
$res = $conn->query("SELECT name, image FROM destinations WHERE name LIKE 'Dhaka%'");
$row = $res->fetch_assoc();
echo "DB Value for Dhaka: '{$row['image']}'\n";

// 3. Test HTTP Access
$url = $row['image']; // This should be http://localhost/...
echo "Testing URL: $url\n";

$headers = @get_headers($url);
if ($headers) {
    echo "HTTP Status: " . $headers[0] . "\n";
} else {
    echo "Failed to get headers (URL might be unreachable from script).\n";
}

// 4. Directory permissions check (basic)
echo "Permissions for assets/images/destinations: " . substr(sprintf('%o', fileperms(__DIR__ . '/assets/images/destinations')), -4) . "\n";
?>