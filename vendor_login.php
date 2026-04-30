<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'config.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success"=>false,"message"=>"Fill all fields"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM vendors WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();

if ($vendor = $result->fetch_assoc()) {

    if ($vendor['status'] !== 'approved') {
        echo json_encode(["success"=>false,"message"=>"Not approved"]);
        exit;
    }

    if (password_verify($password, $vendor['password'])) {

        $_SESSION['user_id'] = $vendor['id'];
        $_SESSION['role'] = 'vendor';
        $_SESSION['name'] = $vendor['full_name'];

        echo json_encode([
            "success"=>true,
            "message"=>"Vendor login success",
            "vendor_id"=>$vendor['id'],
            "name"=>$vendor['full_name'],
            "redirect"=>"vendor-dashboard.php"
        ]);

    } else {
        echo json_encode(["success"=>false,"message"=>"Wrong password"]);
    }

} else {
    echo json_encode(["success"=>false,"message"=>"Vendor not found"]);
}
?>
