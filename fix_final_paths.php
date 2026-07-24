<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Standardizing Image Paths to SITE_URL...\n";

function standardize($table, $conn)
{
    echo "Processing $table...\n";
    $res = $conn->query("SELECT id, image FROM $table");
    while ($row = $res->fetch_assoc()) {
        $img = $row['image'];
        // If image starts with /WBT/hotel_booking, replace it with SITE_URL
        if (strpos($img, '/WBT/hotel_booking/') === 0) {
            $newPath = SITE_URL . substr($img, strlen('/WBT/hotel_booking'));
            $stmt = $conn->prepare("UPDATE $table SET image = ? WHERE id = ?");
            $stmt->bind_param("si", $newPath, $row['id']);
            $stmt->execute();
            echo "  ID {$row['id']}: Updated to $newPath\n";
        } elseif (strpos($img, 'assets/') === 0) {
            $newPath = SITE_URL . '/' . $img;
            $stmt = $conn->prepare("UPDATE $table SET image = ? WHERE id = ?");
            $stmt->bind_param("si", $newPath, $row['id']);
            $stmt->execute();
            echo "  ID {$row['id']}: Prefixed with SITE_URL -> $newPath\n";
        }
    }
}

standardize('destinations', $conn);
standardize('hotels', $conn);

echo "Standardization Done.\n";

echo "\n--- UNIQUE IMAGE DOWNLOAD (FINAL PUSH) ---\n";

function downloadCurl($url, $filepath)
{
    echo "Downloading to $filepath... ";
    $ch = curl_init($url);
    $fp = fopen($filepath, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    return ($code == 200 && filesize($filepath) > 1000);
}

$unique = [
    // Destinations
    'assets/images/destinations/dhaka.jpg' => 'https://images.unsplash.com/photo-1629891089921-96c21e35ddc0?w=800',
    'assets/images/destinations/cox_s_bazar.jpg' => 'https://images.unsplash.com/photo-1622543952736-53a5c1f5793e?w=800', // Different Cox's Bazar
    'assets/images/destinations/dubai.jpg' => 'https://images.unsplash.com/photo-1512453979798-5ea904acfb5a?w=800',
    'assets/images/destinations/bangkok.jpg' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=800',

    // Hotels
    'assets/images/hotels/marina_bay_tower_hotel.jpg' => 'https://images.unsplash.com/photo-1518599804246-848d795536fd?w=800',
    'assets/images/hotels/bangkok_heritage_boutique.jpg' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'
];

foreach ($unique as $path => $url) {
    if (downloadCurl($url, __DIR__ . '/' . $path)) {
        echo "SUCCESS: $path\n";
    } else {
        echo "FAILED: $path\n";
    }
}

echo "All Done.";
?>