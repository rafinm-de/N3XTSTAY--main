<?php
require_once __DIR__ . '/../includes/Database.php';

echo "Updating database images...\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Destinations
    $destinations = [
        'Dhaka' => 'https://images.unsplash.com/photo-1628116904423-d85c13635aa9?w=800',
        'Cox\'s Bazar' => 'https://images.unsplash.com/photo-1596700877903-88e99498246a?w=800',
        'Sylhet' => 'https://images.unsplash.com/photo-1602446738914-b4aeac54a557?w=800', // Better tea garden match
        'Dubai' => 'https://images.unsplash.com/photo-1512453979798-5ea936a79405?w=800',
        'Bangkok' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=800',
        'Singapore' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?w=800'
    ];

    $stmtDest = $conn->prepare("UPDATE destinations SET image = ? WHERE name LIKE ?");

    foreach ($destinations as $name => $url) {
        $likeName = "%$name%";
        $stmtDest->bind_param("ss", $url, $likeName);
        if ($stmtDest->execute()) {
            echo "Updated destination: $name\n";
        } else {
            echo "Failed to update: $name - " . $conn->error . "\n";
        }
    }

    // Hotels
    // We'll update based on destination or name
    $hotels = [
        'The Bengal Palace Hotel' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
        'Cox\'s Bazar Beach Resort' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800',
        'Sylhet Tea Garden Resort' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800',
        'Al-Masaah Grand Hotel' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800',
        'Bangkok Heritage Boutique' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=800',
        'Marina Bay Tower Hotel' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800'
    ];

    $stmtHotel = $conn->prepare("UPDATE hotels SET image = ? WHERE name LIKE ?");

    foreach ($hotels as $name => $url) {
        $likeName = "%$name%";
        $stmtHotel->bind_param("ss", $url, $likeName);
        if ($stmtHotel->execute()) {
            echo "Updated hotel: $name\n";
        } else {
            echo "Failed to update: $name - " . $conn->error . "\n";
        }
    }

    echo "Image updates completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>