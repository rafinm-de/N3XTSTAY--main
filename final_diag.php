<?php
require_once __DIR__ . '/includes/Database.php';
$conn = Database::getInstance()->getConnection();

echo "--- FINAL DIAGNOSTIC: DESTINATIONS ---\n";
$res = $conn->query("SELECT name, image FROM destinations WHERE is_active = 1 LIMIT 6");
while ($row = $res->fetch_assoc()) {
    echo "{$row['name']}: {$row['image']}\n";
}

echo "\n--- FINAL DIAGNOSTIC: HOTELS ---\n";
$res = $conn->query("SELECT name, image FROM hotels WHERE is_active = 1 LIMIT 6");
while ($row = $res->fetch_assoc()) {
    echo "{$row['name']}: {$row['image']}\n";
}
?>