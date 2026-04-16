<?php
// ============================================================
//  customer_register.php
//  POST: full_name, email, phone, password
// ============================================================
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(false, 'Invalid request method');
}

$full_name = clean($_POST['full_name'] ?? '');
$email     = clean($_POST['email']     ?? '');
$phone     = clean($_POST['phone']     ?? '');
$password  =        $_POST['password'] ?? '';
$city      = clean($_POST['city']      ?? '');

// ── Validate ─────────────────────────────────────────────
if (!$full_name || !$email || !$phone || !$password) {
    sendJSON(false, 'All fields are required');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJSON(false, 'Invalid email address');
}
if (strlen($password) < 6) {
    sendJSON(false, 'Password must be at least 6 characters');
}

// ── Check duplicate email ─────────────────────────────────
$stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    sendJSON(false, 'This email is already registered. Please sign in.');
}
$stmt->close();

// ── Hash password & insert ────────────────────────────────
$hashed = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare(
    "INSERT INTO customers (full_name, email, phone, password, city) VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param('sssss', $full_name, $email, $phone, $hashed, $city);

if ($stmt->execute()) {
    $customer_id = $stmt->insert_id;
    // Auto login after registration
    $_SESSION['customer_id']   = $customer_id;
    $_SESSION['customer_name'] = $full_name;
    $_SESSION['customer_email']= $email;
    $_SESSION['role']          = 'customer';
    sendJSON(true, 'Account created successfully! Welcome to Smart Rental Marketplace.', [
        'customer_id'   => $customer_id,
        'name'          => $full_name,
        'redirect'      => '../index.html'
    ]);
} else {
    sendJSON(false, 'Registration failed. Please try again.');
}
?>
