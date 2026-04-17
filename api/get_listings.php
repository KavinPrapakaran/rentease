<?php
require_once "../config.php";

$stmt = $pdo->query("
    SELECT l.id, l.title, l.price_per_day, l.city, c.name as category
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    WHERE l.status='approved'
    ORDER BY l.id DESC
");

echo json_encode($stmt->fetchAll());
?>