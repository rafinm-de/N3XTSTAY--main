<?php
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$targets = [
    'destinations' => ["Cox's Bazar", "Dhaka", "Dubai"],
    'hotels' => ["Bangkok Heritage Boutique", "Marina Bay Tower Hotel"]
];

echo "--- DEBUGGING NAMES ---\n";

foreach ($targets as $table => $names) {
    echo "\nTable: $table\n";
    $sql = "SELECT id, name, image FROM $table";
    $res = $conn->query($sql);

    while ($row = $res->fetch_assoc()) {
        $dbName = $row['name'];
        echo "[ID: {$row['id']}] '$dbName' (Len: " . strlen($dbName) . ")";

        // Hex dump for hidden chars
        $hex = bin2hex($dbName);
        echo " Hex: $hex";

        // Check if image is set
        echo " Image: " . (empty($row['image']) ? "EMPTY" : substr($row['image'], 0, 20) . "...") . "\n";
    }
}
?>