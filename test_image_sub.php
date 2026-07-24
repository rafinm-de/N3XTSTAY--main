<?php
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Swapping to TEST images...\n";

// Cox's Bazar -> Placeholder 1
$conn->query("UPDATE destinations SET image = 'https://via.placeholder.com/800x600?text=Coxs+Bazar+TEST' WHERE name LIKE 'Cox%Bazar%'");
echo "Updated Cox's Bazar to Placeholder.\n";

// Dhaka -> Placeholder 2
$conn->query("UPDATE destinations SET image = 'https://dummyimage.com/800x600/000/fff&text=Dhaka+Test' WHERE name LIKE 'Dhaka%'");
echo "Updated Dhaka to Placeholder.\n";

echo "Done.";
?>