<?php
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Forcing updates using LIKE/ID for stubborn records...\n";

// DESTINATIONS
$dests = [
    // [Pattern, URL]
    ['Cox%Bazar%', 'https://images.unsplash.com/photo-1608958435020-e8a710988090?w=800'],
    ['Dhaka%', 'https://images.unsplash.com/photo-1629891089921-96c21e35ddc0?w=800'],
    ['Dubai%', 'https://images.unsplash.com/photo-1512453979798-5ea904acfb5a?w=800']
];

foreach ($dests as $d) {
    $stmt = $conn->prepare("UPDATE destinations SET image = ? WHERE name LIKE ?");
    $stmt->bind_param("ss", $d[1], $d[0]);
    $stmt->execute();
    echo "Dest '{$d[0]}': " . ($stmt->affected_rows > 0 ? "UPDATED" : "No Change (Already Correct?)") . "\n";
}

// HOTELS
$hotels = [
    ['Bangkok%Heritage%', 'https://images.unsplash.com/photo-1505628346881-b72e27f99555?w=800'],
    ['Marina%Bay%', 'https://images.unsplash.com/photo-1565031491339-6951219602b8?w=800']
];

foreach ($hotels as $h) {
    $stmt = $conn->prepare("UPDATE hotels SET image = ? WHERE name LIKE ?");
    $stmt->bind_param("ss", $h[1], $h[0]);
    $stmt->execute();
    echo "Hotel '{$h[0]}': " . ($stmt->affected_rows > 0 ? "UPDATED" : "No Change") . "\n";
}

echo "Done.";
?>