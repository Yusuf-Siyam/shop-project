<?php
include("connect.php");

// Drop the existing users table if it exists
$dropTable = "DROP TABLE IF EXISTS users";
if (!mysqli_query($conn, $dropTable)) {
    echo "Error dropping table: " . mysqli_error($conn) . "<br>";
} else {
    echo "Old users table dropped successfully<br>";
}

// Create users table with correct columns
$createUsersTable = "CREATE TABLE users (
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
    echo "Users table created successfully with correct columns<br>";
}

// Add the user data
$firstName = "yusuf";
$lastName = "siyam";
$email = "mdsiyam1011@gmail.com";
$password = password_hash("1011", PASSWORD_DEFAULT);

$insertUser = "INSERT INTO users (firstName, lastName, email, password) 
               VALUES ('$firstName', '$lastName', '$email', '$password')";

if (!mysqli_query($conn, $insertUser)) {
    echo "Error adding user: " . mysqli_error($conn) . "<br>";
} else {
    echo "User added successfully!<br>";
}

// Verify the table structure
$checkColumns = "SHOW COLUMNS FROM users";
$columns = mysqli_query($conn, $checkColumns);

echo "<br>Table Structure:<br>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while($column = mysqli_fetch_assoc($columns)) {
    echo "<tr>";
    echo "<td>" . $column['Field'] . "</td>";
    echo "<td>" . $column['Type'] . "</td>";
    echo "<td>" . $column['Null'] . "</td>";
    echo "<td>" . $column['Key'] . "</td>";
    echo "<td>" . $column['Default'] . "</td>";
    echo "<td>" . $column['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Verify the user was added
$checkUser = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
if ($user = mysqli_fetch_assoc($checkUser)) {
    echo "<br>User verification successful!<br>";
    echo "User details:<br>";
    echo "Name: " . $user['firstName'] . " " . $user['lastName'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
}
?> 