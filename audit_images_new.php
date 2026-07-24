<?php
require_once __DIR__ . '/includes/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "--- DESTINATIONS ---\n";
$res = $conn->query("SELECT id, name, image FROM destinations");
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | Name: {$row['name']} | Img: '{$row['image']}'\n";
}

echo "\n--- HOTELS ---\n";
$res = $conn->query("SELECT id, name, image FROM hotels");
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | Name: {$row['name']} | Img: '{$row['image']}'\n";
}
?>