<?php
// ============================================================
//  create_booking.php
//  POST: listing_id, start_date, end_date,
//        payment_method, special_note,
//        cust_name, cust_email, cust_phone (if guest)
// ============================================================
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(false, 'Invalid request method');
}

// ── Get customer id (session or from POST for guest) ──────
$customer_id = $_SESSION['customer_id'] ?? 0;

// If not logged in, try guest details
if (!$customer_id) {
    $cust_name  = clean($_POST['cust_name']  ?? '');
    $cust_email = clean($_POST['cust_email'] ?? '');
    $cust_phone = clean($_POST['cust_phone'] ?? '');

    if (!$cust_name || !$cust_email || !$cust_phone) {
        sendJSON(false, 'Please sign in or provide your name, email and phone');
    }

    // Check if customer exists by email
    $cs = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $cs->bind_param('s', $cust_email);
    $cs->execute();
    $cr = $cs->get_result()->fetch_assoc();
    $cs->close();

    if ($cr) {
        $customer_id = $cr['id'];
    } else {
        // Create guest account with temp password
        $tempPass = password_hash(uniqid(), PASSWORD_BCRYPT);
        $ins = $conn->prepare(
            "INSERT INTO customers (full_name, email, phone, password) VALUES (?, ?, ?, ?)"
        );
        $ins->bind_param('ssss', $cust_name, $cust_email, $cust_phone, $tempPass);
        $ins->execute();
        $customer_id = $ins->insert_id;
        $ins->close();
    }
}

$listing_id     = (int) ($_POST['listing_id']     ?? 0);
$start_date     = clean($_POST['start_date']      ?? '');
$end_date       = clean($_POST['end_date']        ?? '');
$payment_method = clean($_POST['payment_method']  ?? 'cod');
$special_note   = clean($_POST['special_note']    ?? '');

// ── Validate ─────────────────────────────────────────────
if (!$listing_id || !$start_date || !$end_date) {
    sendJSON(false, 'Listing, start date and end date are required');
}

$startDT = new DateTime($start_date);
$endDT   = new DateTime($end_date);
$today   = new DateTime();
$today->setTime(0,0,0);

if ($startDT < $today) {
    sendJSON(false, 'Start date cannot be in the past');
}
if ($endDT < $startDT) {
    sendJSON(false, 'End date must be after start date');
}

$total_days = $startDT->diff($endDT)->days + 1;

// ── Fetch listing ─────────────────────────────────────────
$lstmt = $conn->prepare(
    "SELECT id, vendor_id, title, price_per_day, security_deposit, availability, status
     FROM listings WHERE id = ?"
);
$lstmt->bind_param('i', $listing_id);
$lstmt->execute();
$listing = $lstmt->get_result()->fetch_assoc();
$lstmt->close();

if (!$listing) {
    sendJSON(false, 'Listing not found');
}
if ($listing['status'] !== 'approved') {
    sendJSON(false, 'This listing is not available for booking');
}
if ($listing['availability'] !== 'available') {
    sendJSON(false, 'This item is currently booked. Please choose another date.');
}

// ── Check overlapping bookings ────────────────────────────
$overlap = $conn->prepare(
    "SELECT id FROM bookings
     WHERE listing_id = ?
       AND status NOT IN ('cancelled')
       AND NOT (end_date < ? OR start_date > ?)"
);
$overlap->bind_param('iss', $listing_id, $start_date, $end_date);
$overlap->execute();
$overlap->store_result();
if ($overlap->num_rows > 0) {
    sendJSON(false, 'These dates are already booked. Please choose different dates.');
}
$overlap->close();

// ── Calculate amounts ─────────────────────────────────────
$price_per_day    = $listing['price_per_day'];
$security_deposit = $listing['security_deposit'];
$subtotal         = $price_per_day * $total_days;
$platform_fee     = round($subtotal * 0.10, 2);   // 10% commission
$total_amount     = $subtotal + $platform_fee + $security_deposit;
$vendor_payout    = $subtotal - $platform_fee;
$vendor_id        = $listing['vendor_id'];

// ── Generate booking code ─────────────────────────────────
$booking_code = 'SRMI-' . strtoupper(substr(md5(uniqid()), 0, 6));

// ── Insert booking ────────────────────────────────────────
$bstmt = $conn->prepare(
    "INSERT INTO bookings
     (booking_code, customer_id, listing_id, vendor_id,
      start_date, end_date, total_days,
      price_per_day, subtotal, platform_fee, security_deposit,
      total_amount, vendor_payout, payment_method, special_note,
      status, payment_status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'paid')"
);
$bstmt->bind_param(
    'siiiissddddddss',
    $booking_code, $customer_id, $listing_id, $vendor_id,
    $start_date, $end_date, $total_days,
    $price_per_day, $subtotal, $platform_fee, $security_deposit,
    $total_amount, $vendor_payout, $payment_method, $special_note
);

if (!$bstmt->execute()) {
    sendJSON(false, 'Booking failed: ' . $bstmt->error);
}
$booking_id = $bstmt->insert_id;
$bstmt->close();

// ── Update listing availability ───────────────────────────
$conn->prepare("UPDATE listings SET availability = 'booked' WHERE id = ?")
     ->execute() || null;
$ustmt = $conn->prepare("UPDATE listings SET availability = 'booked' WHERE id = ?");
$ustmt->bind_param('i', $listing_id);
$ustmt->execute();

// ── Notify vendor ─────────────────────────────────────────
$vmsg = "New booking ($booking_code) for your listing: {$listing['title']}";
$vnstmt = $conn->prepare(
    "INSERT INTO notifications (role, user_id, message) VALUES ('vendor', ?, ?)"
);
$vnstmt->bind_param('is', $vendor_id, $vmsg);
$vnstmt->execute();

// ── Notify customer ───────────────────────────────────────
$cmsg = "Booking confirmed! Code: $booking_code for {$listing['title']} — ₹$total_amount";
$cnstmt = $conn->prepare(
    "INSERT INTO notifications (role, user_id, message) VALUES ('customer', ?, ?)"
);
$cnstmt->bind_param('is', $customer_id, $cmsg);
$cnstmt->execute();

sendJSON(true, 'Booking confirmed successfully!', [
    'booking_id'      => $booking_id,
    'booking_code'    => $booking_code,
    'listing_title'   => $listing['title'],
    'start_date'      => $start_date,
    'end_date'        => $end_date,
    'total_days'      => $total_days,
    'price_per_day'   => $price_per_day,
    'subtotal'        => $subtotal,
    'platform_fee'    => $platform_fee,
    'security_deposit'=> $security_deposit,
    'total_amount'    => $total_amount,
    'payment_method'  => $payment_method,
]);
?>
