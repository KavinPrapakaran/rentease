<?php
// ============================================================
// test_login.php  —  Open this in your browser to diagnose
// DELETE THIS FILE after fixing the issue
// ============================================================

$results = [];

// TEST 1: PHP version
$results['php_version'] = PHP_VERSION;
$results['php_ok']      = version_compare(PHP_VERSION, '7.4', '>=');

// TEST 2: MySQLi extension loaded?
$results['mysqli_loaded'] = extension_loaded('mysqli');

// TEST 3: DB Connection
$host = '127.0.0.1';
$user = 'root';
$pass = 'Kavin@2005';
$db   = 'rentease';
$port = 3307;

$conn = @new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    $results['db_connected']  = false;
    $results['db_error']      = $conn->connect_error;
    $results['db_errno']      = $conn->connect_errno;
} else {
    $results['db_connected']  = true;
    $results['db_error']      = null;

    // TEST 4: Tables exist?
    $tables = ['customers', 'vendors', 'bookings', 'listings'];
    foreach ($tables as $t) {
        $r = $conn->query("SHOW TABLES LIKE '$t'");
        $results['table_' . $t] = ($r && $r->num_rows > 0);
    }

    // TEST 5: Sample users exist?
    $r = $conn->query("SELECT id, email, LEFT(password,10) as pass_prefix FROM customers WHERE email='kavin@gmail.com'");
    if ($r && $row = $r->fetch_assoc()) {
        $results['customer_found']       = true;
        $results['customer_pass_prefix'] = $row['pass_prefix'];
        $results['customer_pass_is_hash']= (str_starts_with($row['pass_prefix'], '$2'));
        // Verify password
        $r2 = $conn->query("SELECT password FROM customers WHERE email='kavin@gmail.com'");
        $row2 = $r2->fetch_assoc();
        $results['customer_password_verify'] = password_verify('Kavin@2005', $row2['password']);
    } else {
        $results['customer_found'] = false;
    }

    $r = $conn->query("SELECT id, email, status, LEFT(password,10) as pass_prefix FROM vendors WHERE email='bruce@gmail.com'");
    if ($r && $row = $r->fetch_assoc()) {
        $results['vendor_found']        = true;
        $results['vendor_status']       = $row['status'];
        $results['vendor_pass_prefix']  = $row['pass_prefix'];
        $results['vendor_pass_is_hash'] = (str_starts_with($row['pass_prefix'], '$2'));
        $r2 = $conn->query("SELECT password FROM vendors WHERE email='bruce@gmail.com'");
        $row2 = $r2->fetch_assoc();
        $results['vendor_password_verify'] = password_verify('batman', $row2['password']);
    } else {
        $results['vendor_found'] = false;
    }
}

// TEST 6: Session working?
session_start();
$_SESSION['test'] = 'ok';
$results['session_working'] = ($_SESSION['test'] === 'ok');

// TEST 7: config.php exists and loads?
if (file_exists(__DIR__ . '/config.php')) {
    $results['config_php_exists'] = true;
} else {
    $results['config_php_exists'] = false;
}

// ---- Output ----
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><title>RentEase Diagnostics</title>';
echo '<style>body{font-family:monospace;padding:30px;background:#0a0a0a;color:#eee}
h2{color:#FF4D00}
.ok{color:#22c55e}
.fail{color:#ef4444}
.warn{color:#f59e0b}
table{border-collapse:collapse;width:100%;max-width:700px}
td,th{padding:10px 14px;border:1px solid #333;text-align:left}
th{background:#1a1a1a;color:#FF4D00}
tr:nth-child(even){background:#111}
</style></head><body>';
echo '<h2>🔍 RentEase Login Diagnostics</h2>';
echo '<table><tr><th>Test</th><th>Result</th></tr>';

foreach ($results as $key => $val) {
    if (is_bool($val)) {
        $display = $val
            ? '<span class="ok">✅ YES</span>'
            : '<span class="fail">❌ NO</span>';
    } else {
        $display = htmlspecialchars((string)$val);
        if ($val === null) $display = '<span class="warn">null</span>';
    }
    echo "<tr><td>$key</td><td>$display</td></tr>";
}

echo '</table>';
echo '<br><p style="color:#666">Delete <strong>test_login.php</strong> after you have diagnosed the issue.</p>';
echo '</body></html>';
?>
