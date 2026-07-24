<?php
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Force Downloading images (CURL) & Switching to Relative Paths...\n";

function downloadCurl($url, $filepath)
{
    echo "Downloading $url to $filepath... ";
    $ch = curl_init($url);
    $fp = fopen($filepath, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL verification for robustness
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    if ($code == 200 && filesize($filepath) > 0) {
        echo "OK\n";
        return true;
    }
    else {
        echo "FAILED (HTTP $code)\n";
        return false;
    }
}

// DESTINATIONS
$dests = [
    'Dhaka' => 'https://images.unsplash.com/photo-1629891089921-96c21e35ddc0?w=800',
    'Cox\'s Bazar' => 'https://images.unsplash.com/photo-1608958435020-e8a710988090?w=800',
    'Sylhet' => 'https://images.unsplash.com/photo-1596895111956-bf1cf0599ce5?w=800',
    'Dubai' => 'https://images.unsplash.com/photo-1512453979798-5ea904acfb5a?w=800',
    'Bangkok' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=800',
    'Singapore' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?w=800'
];

foreach ($dests as $name => $url) {
    $filename = strtolower(str_replace([' ', "'"], '_', $name)) . '.jpg';
    $localPath = 'assets/images/destinations/' . $filename; // Relative to project root
    $fullPath = __DIR__ . '/' . $localPath;

    // Always try to download
    if (downloadCurl($url, $fullPath)) {
        // Use Root-Relative Path
        $dbPath = '/WBT/hotel_booking/' . $localPath;
        $stmt = $conn->prepare("UPDATE destinations SET image = ? WHERE name = ?");
        $stmt->bind_param("ss", $dbPath, $name);
        $stmt->execute();
        echo "  Updated DB (Relative): $dbPath\n";
    }
}

// HOTELS
$hotels = [
    'The Bengal Palace Hotel' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
    'Cox\'s Bazar Beach Resort' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=800',
    'Sylhet Tea Garden Resort' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800',
    'Al-Masaah Grand Hotel' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800',
    'Bangkok Heritage Boutique' => 'https://images.unsplash.com/photo-1590483009923-d34a475086d0?w=800',
    'Marina Bay Tower Hotel' => 'https://images.unsplash.com/photo-1518599804246-848d795536fd?w=800'
];

foreach ($hotels as $name => $url) {
    $filename = strtolower(str_replace([' ', "'"], '_', $name)) . '.jpg';
    $localPath = 'assets/images/hotels/' . $filename;
    $fullPath = __DIR__ . '/' . $localPath;

    if (downloadCurl($url, $fullPath)) {
        $dbPath = '/WBT/hotel_booking/' . $localPath;
        $stmt = $conn->prepare("UPDATE hotels SET image = ? WHERE name = ?");
        $stmt->bind_param("ss", $dbPath, $name);
        $stmt->execute();
        echo "  Updated DB (Relative): $dbPath\n";
    }
}

echo "Done.";
?>