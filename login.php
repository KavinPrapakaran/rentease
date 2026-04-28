<?php
require 'config.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success"=>false,"message"=>"Fill all fields"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM customers WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {

    if (password_verify($password, $user['password'])) {
        echo json_encode([
            "success"=>true,
            "message"=>"Login success"
        ]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Wrong password"]);
    }

} else {
    echo json_encode(["success"=>false,"message"=>"User not found"]);
}