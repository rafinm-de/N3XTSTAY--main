<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Re-create database
$conn->query("DROP DATABASE IF EXISTS hotel_booking");
$conn->query("CREATE DATABASE hotel_booking");
$conn->select_db('hotel_booking');

// Execute SQL files
function execute_sql_file($conn, $filename) {
    $sql = file_get_contents($filename);
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "Successfully executed $filename\n";
    } else {
        echo "Error executing $filename: " . $conn->error . "\n";
    }
}

execute_sql_file($conn, 'database/hotel_booking.sql');
execute_sql_file($conn, 'database/sample_data.sql');

// Fix destination image paths matching what was done before because sample_data doesn't update them
$destinations = [
    'Dhaka' => 'assets/images/destinations/dhaka.png',
    'Cox\'s Bazar' => 'assets/images/destinations/coxs_bazar.png',
    'Sylhet' => 'assets/images/destinations/sylhet.png',
    'Dubai' => 'assets/images/destinations/dubai.png',
    'Bangkok' => 'assets/images/destinations/bangkok.png',
    'Singapore' => 'assets/images/destinations/singapore.png'
];

foreach ($destinations as $name => $image) {
    $stmt = $conn->prepare("UPDATE destinations SET image = ? WHERE name = ?");
    $stmt->bind_param("ss", $image, $name);
    $stmt->execute();
}
echo "Destination images updated.\n";

$conn->close();
?>
