<?php
$conn = new mysqli("127.0.0.1", "root", "Kavin@2005", "rentease", 3306);
if ($conn->connect_error) {
    die("ERROR: " . $conn->connect_error);
}

echo "SUCCESS DB CONNECTED";
?>