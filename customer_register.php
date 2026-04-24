<?php
require_once 'config.php';

header("Content-Type: application/json");

$data = $_POST;

$name = $data['full_name'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';

if (!$name || !$email || !$password) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

try {

    // check existing user
    $check = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email already exists"]);
        exit;
    }

    // hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // insert user
    $stmt = $conn->prepare("
        INSERT INTO customers (full_name, email, phone, password)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("ssss", $name, $email, $phone, $hashed);
    $stmt->execute();

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
}
?>