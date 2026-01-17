<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "shop_project";
$port = "3306";

// First connect without database to create it if needed
$conn = new mysqli($host, $user, $pass, "", $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$createDb = "CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($createDb)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
if (!$conn->select_db($db)) {
    die("Error selecting database: " . $conn->error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Debugging: Check if connection is established
// echo "Connected successfully to database: $db";
?>
