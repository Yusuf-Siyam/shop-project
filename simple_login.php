<?php
include("connect.php");
session_start();

// Create users table with simple structure
$createTable = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL
)";

if (!mysqli_query($conn, $createTable)) {
    echo "Error creating table: " . mysqli_error($conn);
}

// Add test user
$email = "mdsiyam1011@gmail.com";
$password = "1011"; // Store password directly for testing

// Check if user exists
$checkUser = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $checkUser);

if (mysqli_num_rows($result) == 0) {
    // Create user if not exists
    $insertUser = "INSERT INTO users (firstName, lastName, email, password) 
                   VALUES ('yusuf', 'siyam', '$email', '$password')";
    mysqli_query($conn, $insertUser);
    echo "Test user created successfully!<br>";
}

// Handle login
if(isset($_POST['login'])) {
    $inputEmail = $_POST['email'];
    $inputPassword = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = '$inputEmail' AND password = '$inputPassword'";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['email'] = $user['email'];
        $_SESSION['firstName'] = $user['firstName'];
        $_SESSION['lastName'] = $user['lastName'];
        echo "Login successful! Redirecting...";
        header("Location: homepage.php");
        exit();
    } else {
        echo "Login failed! Please check your email and password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Login</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .login-form { max-width: 300px; margin: 0 auto; }
        input { width: 100%; padding: 8px; margin: 10px 0; }
        button { width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Login</h2>
        <form method="post">
            <input type="email" name="email" placeholder="Email" value="mdsiyam1011@gmail.com" required>
            <input type="password" name="password" placeholder="Password" value="1011" required>
            <button type="submit" name="login">Login</button>
        </form>
        
        <p>Test Credentials:</p>
        <p>Email: mdsiyam1011@gmail.com</p>
        <p>Password: 1011</p>
    </div>
</body>
</html> 