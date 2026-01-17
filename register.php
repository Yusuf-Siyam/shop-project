<?php
session_start();
include 'connect.php';

// Button er nam check na kore Request Method check kora hocche
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Retrieve form data matching the input names in index.php
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Check if email already exists
    $checkEmail = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($checkEmail);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        // Email exists
        $_SESSION['error'] = "Email Address Already Exists!";
        header("Location: index.php");
        exit();
    } else {
        // 2. Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert User (Role defaults to 'user' as per database_setup.sql)
        $insertQuery = "INSERT INTO users(firstName, lastName, email, password)
                        VALUES (?, ?, ?, ?)";
        
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);
        
        if($insertStmt->execute()){
            $_SESSION['success'] = "Registration successful! Please Sign In.";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Error: " . $conn->error;
            header("Location: index.php");
            exit();
        }
        $insertStmt->close();
    }
    $stmt->close();
} else {
    // If accessed directly without submitting form
    header("Location: index.php");
    exit();
}
?>