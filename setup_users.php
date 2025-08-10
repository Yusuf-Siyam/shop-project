<?php
include("connect.php");

// Create users table with correct columns
$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createUsersTable)) {
    echo "Error creating users table: " . mysqli_error($conn) . "<br>";
} else {
    echo "Users table created successfully<br>";
}

// Add the user data
$firstName = "yusuf";
$lastName = "siyam";
$email = "mdsiyam1011@gmail.com";
$password = password_hash("1011", PASSWORD_DEFAULT); // Securely hash the password

$insertUser = "INSERT INTO users (firstName, lastName, email, password) 
               VALUES ('$firstName', '$lastName', '$email', '$password')";

if (!mysqli_query($conn, $insertUser)) {
    echo "Error adding user: " . mysqli_error($conn) . "<br>";
} else {
    echo "User added successfully!<br>";
}

// Verify the user was added
$checkUser = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
if ($user = mysqli_fetch_assoc($checkUser)) {
    echo "User verification successful!<br>";
    echo "User details:<br>";
    echo "Name: " . $user['firstName'] . " " . $user['lastName'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
}
?> 