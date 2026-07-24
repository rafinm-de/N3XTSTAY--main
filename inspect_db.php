<?php
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Tables:\n";
$tables = $conn->query("SHOW TABLES");
while ($row = $tables->fetch_row()) {
    echo "- " . $row[0] . "\n";
}

echo "\nUsers Column:\n";
$columns = $conn->query("SHOW COLUMNS FROM users");
if ($columns) {
    while ($row = $columns->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Users table not found or error.\n";
}
?>