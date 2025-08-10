<?php
session_start();
include("connect.php");

// Define variables and set to empty values
$name = $email = $subject = $message = "";
$errors = [];

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty($_POST["name"])) {
        $errors[] = "Name is required";
    } else {
        $name = test_input($_POST["name"]);
    }
    
    // Validate email
    if (empty($_POST["email"])) {
        $errors[] = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
    }
    
    // Validate subject
    if (empty($_POST["subject"])) {
        $errors[] = "Subject is required";
    } else {
        $subject = test_input($_POST["subject"]);
    }
    
    // Validate message
    if (empty($_POST["message"])) {
        $errors[] = "Message is required";
    } else {
        $message = test_input($_POST["message"]);
    }
    
    // If no errors, insert data to database
    if (empty($errors)) {
        // Create contacts table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS contacts (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            email VARCHAR(50) NOT NULL,
            subject VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!mysqli_query($conn, $createTable)) {
            $errors[] = "Error creating table: " . mysqli_error($conn);
        }
        
        // Insert the data
        $insertMessage = "INSERT INTO contacts (name, email, subject, message) 
                          VALUES ('$name', '$email', '$subject', '$message')";
        
        if (mysqli_query($conn, $insertMessage)) {
            // Set success message
            $_SESSION['contact_success'] = "Message sent successfully! We'll get back to you soon.";
            
            // Optional: Send email notification
            // sendEmailNotification($name, $email, $subject, $message);
            
            // Redirect back to the contact section
            header("Location: homepage.php#contact");
            exit();
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
    }
    
    // If there are errors, set them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['contact_errors'] = $errors;
        
        // Also store form data to repopulate the form
        $_SESSION['form_data'] = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ];
        
        header("Location: homepage.php#contact");
        exit();
    }
}

// Function to sanitize form data
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/* 
// Uncomment to enable email sending
function sendEmailNotification($name, $email, $subject, $message) {
    // Set up recipient email (admin email)
    $to = "your-email@example.com";
    
    // Email headers
    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Email content
    $emailContent = "<h2>Contact Form Submission</h2>
                    <p><strong>Name:</strong> $name</p>
                    <p><strong>Email:</strong> $email</p>
                    <p><strong>Subject:</strong> $subject</p>
                    <p><strong>Message:</strong></p>
                    <p>$message</p>";
    
    // Send email
    mail($to, "Contact Form: $subject", $emailContent, $headers);
}
*/
?> 