<?php
// Database Import Script for Railway Deployment
// This script imports the database schema and sample data

// Get environment variables
$dbHost = getenv('MYSQLHOST') ?: getenv('DB_HOST');
$dbUser = getenv('MYSQLUSER') ?: getenv('DB_USER');
$dbPass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS');
$dbName = getenv('MYSQLDATABASE') ?: getenv('DB_NAME');

echo "Starting database import...\n";
echo "Connecting to: $dbHost\n";
echo "Database: $dbName\n";

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✓ Database connection successful\n";
    
    // Read and execute hotel_booking.sql
    echo "\nImporting hotel_booking.sql...\n";
    $sql = file_get_contents(__DIR__ . '/database/hotel_booking.sql');
    
    if ($sql === false) {
        echo "✗ Failed to read hotel_booking.sql\n";
        exit;
    }
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($conn->query($statement)) {
                echo "✓ Executed statement\n";
            } else {
                echo "✗ Error: " . $conn->error . "\n";
            }
        }
    }
    
    echo "✓ hotel_booking.sql imported successfully\n";
    
    // Read and execute sample_data.sql
    echo "\nImporting sample_data.sql...\n";
    $sql = file_get_contents(__DIR__ . '/database/sample_data.sql');
    
    if ($sql === false) {
        echo "✗ Failed to read sample_data.sql\n";
        exit;
    }
    
    $statements = explode(';', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($conn->query($statement)) {
                echo "✓ Executed statement\n";
            } else {
                echo "✗ Error: " . $conn->error . "\n";
            }
        }
    }
    
    echo "✓ sample_data.sql imported successfully\n";
    
    // Read and execute update_destination_images.sql
    echo "\nImporting update_destination_images.sql...\n";
    $sql = file_get_contents(__DIR__ . '/database/update_destination_images.sql');
    
    if ($sql === false) {
        echo "✗ Failed to read update_destination_images.sql\n";
        exit;
    }
    
    $statements = explode(';', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($conn->query($statement)) {
                echo "✓ Executed statement\n";
            } else {
                echo "✗ Error: " . $conn->error . "\n";
            }
        }
    }
    
    echo "✓ update_destination_images.sql imported successfully\n";
    
    echo "\n✓✓✓ Database import completed successfully! ✓✓✓\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
