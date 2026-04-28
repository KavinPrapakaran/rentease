<?php
require_once 'config.php';
header('Content-Type: application/json');

$role = $_SESSION['role'] ?? null;
$name = $_SESSION['name'] ?? null;
$email = $_SESSION['email'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$role || !$userId) {
    echo json_encode([
        'success' => true,
        'logged_in' => false
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'logged_in' => true,
    'role' => $role,
    'name' => $name,
    'email' => $email,
    'user_id' => $userId
]);
exit;
?>
