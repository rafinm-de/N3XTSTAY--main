<?php
require_once __DIR__ . '/includes/Database.php';
$conn = Database::getInstance()->getConnection();

$names = ["Cox's Bazar", "Dhaka", "Dubai", "Bangkok Heritage Boutique", "Marina Bay Tower Hotel"];

echo "--- DEEP INSPECTION FOR HIDDEN CHARACTERS ---\n";

foreach ($names as $name) {
    echo "Checking: $name\n";
    // Try to find it in destinations or hotels
    $found = false;

    // Check Destinations
    $sql = "SELECT 'dest' as type, name, image, is_active FROM destinations WHERE name LIKE '%" . $conn->real_escape_string($name) . "%'";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            echo "  Found DEST: '{$row['name']}'\n";
            echo "    Active: {$row['is_active']}\n";
            echo "    Image: '{$row['image']}'\n";
            echo "    Length: " . strlen($row['image']) . "\n";
            echo "    Hex: " . bin2hex($row['image']) . "\n";
            $found = true;
        }
    }

    // Check Hotels
    $sql = "SELECT 'hotel' as type, name, image, is_active FROM hotels WHERE name LIKE '%" . $conn->real_escape_string($name) . "%'";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            echo "  Found HOTEL: '{$row['name']}'\n";
            echo "    Active: {$row['is_active']}\n";
            echo "    Image: '{$row['image']}'\n";
            echo "    Length: " . strlen($row['image']) . "\n";
            echo "    Hex: " . bin2hex($row['image']) . "\n";
            $found = true;
        }
    }

    if (!$found)
        echo "  NOT FOUND\n";
    echo "----------------\n";
}
?>