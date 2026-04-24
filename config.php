<?php
// ============================================================
//  config.php — FINAL WORKING VERSION
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Kavin@2005');
define('DB_NAME', 'rentease');
define('DB_PORT', 3306); // IMPORTANT (your MySQL is 3306)

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================= MYSQLi CONNECTION =================
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $conn->connect_error
    ]));
}

// Set charset
$conn->set_charset('utf8mb4');


// ================= PDO CONNECTION =================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'PDO connection failed',
        'error' => $e->getMessage()
    ]));
}


// ================= HELPERS =================

// JSON response
function sendJSON($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit();
}

// Sanitize input
function clean($str) {
    return htmlspecialchars(strip_tags(trim($str)));
}

// Upload image
function uploadImage($file, $folder = 'listings') {

    $uploadDir = dirname(__DIR__) . '/uploads/' . $folder . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!in_array($file['type'], $allowed)) {
        return ['success' => false, 'message' => 'Only JPG, PNG, WEBP, GIF allowed'];
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Image must be under 5MB'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . strtolower($ext);
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['success' => false, 'message' => 'Failed to save image'];
    }

    return ['success' => true, 'path' => 'uploads/' . $folder . '/' . $filename];
}
?>