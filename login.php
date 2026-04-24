<?php
require_once 'config.php';
header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    sendJSON(false, 'Email & password required');
}

// 🔥 IMPORTANT: using customers table
$stmt = $conn->prepare("SELECT * FROM customers WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

if ($user) {

    if (password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['full_name'];

        sendJSON(true, 'Login success');

    } else {
        sendJSON(false, 'Wrong password');
    }

} else {
    sendJSON(false, 'User not found');
}
?>