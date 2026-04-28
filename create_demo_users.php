<?php
require_once 'config.php';

$customerName = 'Kavin';
$customerEmail = 'kavin@gmail.com';
$customerPhone = '9876543210';
$customerPass = password_hash('Kavin@2005', PASSWORD_DEFAULT);

$vendorName = 'Bruce';
$vendorEmail = 'bruce@gmail.com';
$vendorPhone = '9123456780';
$vendorPass = password_hash('batman', PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO customers (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $customerName, $customerEmail, $customerPhone, $customerPass);
$stmt->execute();

$stmt = $conn->prepare("INSERT INTO vendors (full_name, email, phone, password, business_name, city, area, full_address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
$business = 'Bruce Rentals';
$city = 'Salem';
$area = 'Town Area';
$address = 'Salem, Tamil Nadu, India';
$stmt->bind_param("ssssssss", $vendorName, $vendorEmail, $vendorPhone, $vendorPass, $business, $city, $area, $address);
$stmt->execute();

echo "Demo accounts created";
?>