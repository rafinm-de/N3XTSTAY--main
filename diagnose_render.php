<?php
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== DIAGNOSTIC: POPULAR DESTINATIONS RENDER ===\n";
$destinations = $conn->query("SELECT * FROM destinations WHERE is_active = 1 ORDER BY name");
$count = 0;
while ($dest = $destinations->fetch_assoc()) {
    if ($count >= 6)
        break;
    $count++;

    $src = $dest['image'] ?: 'PLACEHOLDER';
    echo "Dest [{$dest['name']}]: <img src='$src'>\n";
}

echo "\n=== DIAGNOSTIC: FEATURED HOTELS RENDER ===\n";
$featuredHotels = $conn->query("
    SELECT h.*
    FROM hotels h
    WHERE h.is_active = 1
    ORDER BY RAND()
    LIMIT 6
");

while ($hotel = $featuredHotels->fetch_assoc()) {
    $src = $hotel['image'] ?: 'PLACEHOLDER';
    echo "Hotel [{$hotel['name']}]: <img src='$src'>\n";
}
?>