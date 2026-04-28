<?php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'Kavin@2005');
define('DB_NAME', 'rentease');
define('DB_PORT', 3306);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("DB ERROR: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>