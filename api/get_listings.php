<?php
// Connect to the config hub using a relative path
require_once dirname(__DIR__) . '/config.php';

// Now $pdo is available from config.php
$stmt = $pdo->query("
    SELECT l.id, l.title, l.price_per_day, l.city, c.name as category
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    WHERE l.status='approved'
    ORDER BY l.id DESC
");

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
