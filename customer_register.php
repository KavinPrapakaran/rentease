<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

require_once 'config.php';
// session_start() already called inside config.php

ob_clean();
header('Content-Type: application/json');

$name     = trim($_POST['full_name'] ?? '');
$email    = trim($_POST['email']     ?? '');
$phone    = trim($_POST['phone']     ?? '');
$password =       $_POST['password'] ?? '';

if (!$name || !$email || !$password) {
    echo json_encode(["success" => false, "message" => "Name, email and password are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email address"]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "This email is already registered"]);
    exit;
}
$stmt->close();

// Hash password and insert
$hashed = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO customers (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $phone, $hashed);

if ($stmt->execute()) {
    $new_id = $conn->insert_id;

    $_SESSION['user_id'] = $new_id;
    $_SESSION['role']    = 'customer';
    $_SESSION['name']    = $name;

    echo json_encode([
        "success" => true,
        "message" => "Account created successfully",
        "role"    => "customer"
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Registration failed. Please try again."]);
}
?>
