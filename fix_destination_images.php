<?php
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Define destination images mapping
$destinationImages = [
    'Dhaka' => 'assets/images/destinations/dhaka.jpg',
    'Cox\'s Bazar' => 'assets/images/destinations/cox_s_bazar.jpg',
    'Sylhet' => 'assets/images/destinations/sylhet.jpg',
    'Dubai' => 'assets/images/destinations/dubai.jpg',
    'Bangkok' => 'assets/images/destinations/bangkok.jpg',
    'Singapore' => 'assets/images/destinations/singapore.jpg'
];

echo "=== Fixing Destination Images ===\n\n";

// Get all destinations
$result = $conn->query("SELECT id, name, image FROM destinations ORDER BY id");

echo "Current Destinations:\n";
while ($dest = $result->fetch_assoc()) {
    echo "ID: {$dest['id']} | Name: {$dest['name']} | Image: " . ($dest['image'] ?: 'NULL') . "\n";
}

echo "\n=== Updating Images ===\n";

// Update each destination with the correct image
foreach ($destinationImages as $name => $imagePath) {
    $stmt = $conn->prepare("UPDATE destinations SET image = ? WHERE name = ?");
    $stmt->bind_param("ss", $imagePath, $name);

    if ($stmt->execute()) {
        echo "✓ Updated: $name -> $imagePath\n";
    } else {
        echo "✗ Failed to update: $name\n";
    }
}

echo "\n=== Verification ===\n";

// Verify the updates
$result = $conn->query("SELECT id, name, image FROM destinations ORDER BY id");
echo "\nUpdated Destinations:\n";
while ($dest = $result->fetch_assoc()) {
    $imageStatus = file_exists(__DIR__ . '/' . $dest['image']) ? '✓ EXISTS' : '✗ MISSING';
    echo "ID: {$dest['id']} | Name: {$dest['name']} | Image: {$dest['image']} | Status: $imageStatus\n";
}

echo "\n=== COMPLETED ===\n";
