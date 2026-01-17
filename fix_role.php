<?php
include("connect.php");

echo "<h2>Fixing Database Roles...</h2>";

// 1. Check if 'role' column exists
$checkColumn = "SHOW COLUMNS FROM users LIKE 'role'";
$result = mysqli_query($conn, $checkColumn);

if (mysqli_num_rows($result) == 0) {
    // Column doesn't exist, so add it
    echo "• 'role' column is missing. Adding it now...<br>";
    $alterTable = "ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER password";
    
    if (mysqli_query($conn, $alterTable)) {
        echo "<span style='color:green;'>✓ Successfully added 'role' column.</span><br>";
    } else {
        die("<span style='color:red;'>✗ Error adding column: " . mysqli_error($conn) . "</span>");
    }
} else {
    echo "• 'role' column already exists.<br>";
}

// 2. Make your specific user an Admin
$emailToPromote = "admin123@gmail.com"; // CHANGE THIS if your email is different

echo "<br>• Promoting <strong>$emailToPromote</strong> to Admin...<br>";

// Check if user exists first
$checkUser = "SELECT * FROM users WHERE email = '$emailToPromote'";
$userResult = mysqli_query($conn, $checkUser);

if (mysqli_num_rows($userResult) > 0) {
    $updateSql = "UPDATE users SET role = 'admin' WHERE email = '$emailToPromote'";
    if (mysqli_query($conn, $updateSql)) {
        echo "<span style='color:green;'>✓ SUCCESS! User '$emailToPromote' is now an ADMIN.</span><br>";
    } else {
        echo "<span style='color:red;'>✗ Error updating user: " . mysqli_error($conn) . "</span><br>";
    }
} else {
    echo "<span style='color:orange;'>⚠ User '$emailToPromote' not found. Please register this account first.</span><br>";
}

echo "<br><hr><a href='index.php'><strong>&larr; Go Back to Login</strong></a>";
?>