<?php
// password_finder.php — DROP IN project root, open in browser, DELETE AFTER USE

mysqli_report(MYSQLI_REPORT_OFF); // must be FIRST — disables strict exception mode

$passwords = [
    'Kavin@2005',
    '',
    'root',
    'mysql',
    'admin',
    'password',
    'xampp',
    'Kavin2005',
    'kavin@2005',
];

$ports = [3307, 3306];

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><title>MySQL Password Finder</title>';
echo '<style>
body{font-family:monospace;padding:30px;background:#0a0a0a;color:#eee}
h2{color:#FF4D00}
.ok{color:#22c55e;font-weight:bold}
.fail{color:#ef4444}
table{border-collapse:collapse;width:100%;max-width:700px}
td,th{padding:10px 14px;border:1px solid #333;text-align:left}
th{background:#1a1a1a;color:#FF4D00}
tr:nth-child(even){background:#111}
.winner{background:#052e16 !important;border:2px solid #22c55e}
</style></head><body>';
echo '<h2>MySQL Password Finder</h2>';
echo '<table><tr><th>Port</th><th>Password</th><th>Result</th></tr>';

$found = false;
foreach ($ports as $port) {
    foreach ($passwords as $pass) {
        $label = $pass === '' ? '<em>(empty)</em>' : htmlspecialchars($pass);
        try {
            $conn = new mysqli('127.0.0.1', 'root', $pass, '', $port);
            if ($conn->connect_errno) {
                echo "<tr><td>$port</td><td>$label</td><td><span class='fail'>❌ "
                    . htmlspecialchars($conn->connect_error) . "</span></td></tr>";
            } else {
                echo "<tr class='winner'><td>$port</td><td>$label</td><td><span class='ok'>✅ CONNECTED — use this!</span></td></tr>";
                $found = true;
                $conn->close();
            }
        } catch (Exception $e) {
            echo "<tr><td>$port</td><td>$label</td><td><span class='fail'>❌ "
                . htmlspecialchars($e->getMessage()) . "</span></td></tr>";
        }
    }
}

if (!$found) {
    echo "<tr><td colspan='3' style='color:#f59e0b;padding:16px'>
        ⚠️ None worked. Make sure MySQL is Running (green) in XAMPP Control Panel.
    </td></tr>";
}

echo '</table><br><p style="color:#666">Delete password_finder.php after use.</p>';
echo '</body></html>';
