<?php
include("connect.php");

// 1. Create users table with the REQUIRED 'role' column
$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user', -- This line was missing!
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createUsersTable)) {
    echo "Error creating users table: " . mysqli_error($conn) . "<br>";
} else {
    echo "Users table check/creation successful<br>";
}

// 2. Add the user data
$firstName = "yusuf";
$lastName = "siyam";
$email = "mdsiyam1011@gmail.com";
$password = password_hash("1011", PASSWORD_DEFAULT);

// Check if user already exists to avoid errors
$checkUser = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

if (mysqli_num_rows($checkUser) == 0) {
    // Insert new user (Defaults to 'user' role)
    // If you want this specific user to be an admin, change the insert below
    $insertUser = "INSERT INTO users (firstName, lastName, email, password, role) 
                   VALUES ('$firstName', '$lastName', '$email', '$password', 'user')"; // Change 'user' to 'admin' here if you want him to be admin

    if (!mysqli_query($conn, $insertUser)) {
        echo "Error adding user: " . mysqli_error($conn) . "<br>";
    } else {
        echo "User added successfully!<br>";
    }
} else {
    echo "User <strong>$email</strong> already exists.<br>";
}

// 3. Verify the user
$verifyUser = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
if ($user = mysqli_fetch_assoc($verifyUser)) {
    echo "<br><strong>User Details:</strong><br>";
    echo "Name: " . $user['firstName'] . " " . $user['lastName'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Role: " . $user['role'] . "<br>"; // Now we can see the role
}
?>