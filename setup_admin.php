<?php
include("connect.php");

echo "<h2>⚙️ Admin Account Setup</h2>";

// 1. Ensure the 'role' column exists
$checkColumn = "SHOW COLUMNS FROM users LIKE 'role'";
$result = mysqli_query($conn, $checkColumn);

if (mysqli_num_rows($result) == 0) {
    $alterTable = "ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER password";
    if (mysqli_query($conn, $alterTable)) {
        echo "<p style='color:green;'>✓ Added 'role' column to users table.</p>";
    } else {
        die("<p style='color:red;'>✗ Error adding column: " . mysqli_error($conn) . "</p>");
    }
} else {
    echo "<p>✓ 'role' column check passed.</p>";
}

// 2. Define the Admin Credentials you want
$adminEmail = "admin123@gmail.com";
$adminPass  = "admin123"; // This will be hashed
$firstName  = "Super";
$lastName   = "Admin";

echo "<hr>";
echo "Target Email: <strong>$adminEmail</strong><br>";

// 3. Check if this user exists
$checkUser = "SELECT * FROM users WHERE email = '$adminEmail'";
$userResult = mysqli_query($conn, $checkUser);

if (mysqli_num_rows($userResult) > 0) {
    // --- SCENARIO A: User Exists -> UPDATE ROLE ---
    echo "Status: User found. Updating role to 'admin'...<br>";
    
    $updateSql = "UPDATE users SET role = 'admin' WHERE email = '$adminEmail'";
    if (mysqli_query($conn, $updateSql)) {
        echo "<h3 style='color:green;'>SUCCESS! User promoted to Admin.</h3>";
    } else {
        echo "<h3 style='color:red;'>Error updating user: " . mysqli_error($conn) . "</h3>";
    }

} else {
    // --- SCENARIO B: User Missing -> CREATE USER ---
    echo "Status: User NOT found. Creating new Admin account...<br>";
    
    $hashedPass = password_hash($adminPass, PASSWORD_DEFAULT);
    
    $insertSql = "INSERT INTO users (firstName, lastName, email, password, role) 
                  VALUES ('$firstName', '$lastName', '$adminEmail', '$hashedPass', 'admin')";
                  
    if (mysqli_query($conn, $insertSql)) {
        echo "<h3 style='color:green;'>SUCCESS! New Admin account created.</h3>";
    } else {
        echo "<h3 style='color:red;'>Error creating account: " . mysqli_error($conn) . "</h3>";
    }
}

echo "<hr>";
echo "<h3>👇 Login Details:</h3>";
echo "Email: <strong>$adminEmail</strong><br>";
echo "Password: <strong>$adminPass</strong><br>";
echo "<br><a href='index.php' style='background:#6c63ff; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Login Page</a>";
?>