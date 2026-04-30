<?php
define('DB_HOST', 'switchback.proxy.rlwy.net');
define('DB_USER', 'root');
define('DB_PASS', 'DiJeBygtnihEobKbPOEKnOgIGFRjeQcR');
define('DB_NAME', 'railway');
define('DB_PORT', 40123);

if (session_status() === PHP_SESSION_NONE) session_start();

ini_set('display_errors', 0);
error_reporting(0);
mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_errno) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);
    exit;
}

$conn->set_charset('utf8mb4');

function sendJSON($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit();
}
