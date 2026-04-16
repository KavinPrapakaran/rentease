<?php
// ============================================================
//  vendor_register.php
//  POST: full_name, email, phone, password, business_name,
//        city, area, full_address, aadhar_pan, bank_account,
//        ifsc_code, about
// ============================================================
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(false, 'Invalid request method');
}

$full_name     = clean($_POST['full_name']     ?? '');
$email         = clean($_POST['email']         ?? '');
$phone         = clean($_POST['phone']         ?? '');
$password      =        $_POST['password']     ?? '';
$business_name = clean($_POST['business_name'] ?? '');
$city          = clean($_POST['city']          ?? '');
$area          = clean($_POST['area']          ?? '');
$full_address  = clean($_POST['full_address']  ?? '');
$aadhar_pan    = clean($_POST['aadhar_pan']    ?? '');
$bank_account  = clean($_POST['bank_account']  ?? '');
$ifsc_code     = clean($_POST['ifsc_code']     ?? '');
$about         = clean($_POST['about']         ?? '');

// ── Validate ─────────────────────────────────────────────
if (!$full_name || !$email || !$phone || !$password || !$business_name || !$city) {
    sendJSON(false, 'Please fill all required fields');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJSON(false, 'Invalid email address');
}
if (strlen($password) < 6) {
    sendJSON(false, 'Password must be at least 6 characters');
}

// ── Check duplicate email ─────────────────────────────────
$stmt = $conn->prepare("SELECT id FROM vendors WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    sendJSON(false, 'This email is already registered as a vendor');
}
$stmt->close();

// ── Hash & Insert ─────────────────────────────────────────
$hashed = password_hash($password, PASSWORD_BCRYPT);
$stmt   = $conn->prepare(
    "INSERT INTO vendors
     (full_name, email, phone, password, business_name, city, area, full_address, aadhar_pan, bank_account, ifsc_code, about, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
);
$stmt->bind_param(
    'ssssssssssss',
    $full_name, $email, $phone, $hashed,
    $business_name, $city, $area, $full_address,
    $aadhar_pan, $bank_account, $ifsc_code, $about
);

if ($stmt->execute()) {
    $vendor_id = $stmt->insert_id;

    // Add admin notification
    $msg  = "New vendor registration: $business_name ($email) — awaiting approval";
    $nstmt = $conn->prepare(
        "INSERT INTO notifications (role, user_id, message) VALUES ('admin', 1, ?)"
    );
    $nstmt->bind_param('s', $msg);
    $nstmt->execute();

    sendJSON(true, 'Vendor account submitted! We will review and approve within 24 hours.', [
        'vendor_id' => $vendor_id,
        'redirect'  => '../vendor-register.html'
    ]);
} else {
    sendJSON(false, 'Registration failed: ' . $conn->error);
}
?>
