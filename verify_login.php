<?php
include("connect.php");
session_start();

// Your test credentials
$testEmail = "mdsiyam1011@gmail.com";
$testPassword = "1011";

echo "<h3>Testing Login System</h3>";

// 1. Check if user exists
$checkUser = "SELECT * FROM users WHERE email = '$testEmail'";
$result = mysqli_query($conn, $checkUser);

if($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    echo "1. User found in database<br>";
    echo "User details:<br>";
    echo "- Name: " . $user['firstName'] . " " . $user['lastName'] . "<br>";
    echo "- Email: " . $user['email'] . "<br>";
    echo "- Hashed Password: " . $user['password'] . "<br>";
    
    // 2. Verify password
    if(password_verify($testPassword, $user['password'])) {
        echo "<br>2. Password verification: SUCCESS<br>";
        echo "The password '1011' is correct!<br>";
    } else {
        echo "<br>2. Password verification: FAILED<br>";
        echo "The password '1011' is incorrect!<br>";
        
        // 3. Try to reset the password
        echo "<br>3. Attempting to reset password...<br>";
        $newHashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $updatePassword = "UPDATE users SET password = '$newHashedPassword' WHERE email = '$testEmail'";
        
        if(mysqli_query($conn, $updatePassword)) {
            echo "Password has been reset to '1011'<br>";
            echo "Please try logging in again with:<br>";
            echo "Email: mdsiyam1011@gmail.com<br>";
            echo "Password: 1011<br>";
        } else {
            echo "Error resetting password: " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "1. User not found in database<br>";
    echo "Creating new user account...<br>";
    
    // Create new user
    $firstName = "yusuf";
    $lastName = "siyam";
    $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
    
    $insertUser = "INSERT INTO users (firstName, lastName, email, password) 
                   VALUES ('$firstName', '$lastName', '$testEmail', '$hashedPassword')";
    
    if(mysqli_query($conn, $insertUser)) {
        echo "New user account created successfully!<br>";
        echo "Please try logging in with:<br>";
        echo "Email: mdsiyam1011@gmail.com<br>";
        echo "Password: 1011<br>";
    } else {
        echo "Error creating user: " . mysqli_error($conn) . "<br>";
    }
}

// 4. Show login form for testing
echo "<br><h4>Test Login Form:</h4>";
echo '<form method="post" action="register.php">';
echo '<input type="hidden" name="email" value="' . $testEmail . '">';
echo '<input type="hidden" name="password" value="' . $testPassword . '">';
echo '<input type="submit" name="signIn" value="Test Login">';
echo '</form>';
?> 