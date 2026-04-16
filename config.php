<?php
// ============================================================
//  config.php  —  Database connection + session start
//  Place this file at: C:\xampp\htdocs\rentease\php\config.php
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // XAMPP default username
define('DB_PASS', 'Kavin@2005');           // XAMPP default password (empty)
define('DB_NAME', 'rentease');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Make sure MySQL is running in XAMPP.'
    ]);
    exit();
}

// Set charset
$conn->set_charset('utf8mb4');

// ── HELPER: Send JSON response ──────────────────────────────
function sendJSON($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit();
}

// ── HELPER: Sanitize input ──────────────────────────────────
function clean($str) {
    return htmlspecialchars(strip_tags(trim($str)));
}

// ── HELPER: Upload image ────────────────────────────────────
function uploadImage($file, $folder = 'listings') {
    $uploadDir = dirname(__DIR__) . '/uploads/' . $folder . '/';

    // Create folder if not exists
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

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . strtolower($ext);
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['success' => false, 'message' => 'Failed to save image'];
    }

    return ['success' => true, 'path' => 'uploads/' . $folder . '/' . $filename];
}
?>
