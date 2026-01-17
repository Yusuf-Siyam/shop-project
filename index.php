<?php
session_start();
include("connect.php");

// Initialize variables to avoid "undefined variable" errors
$error = "";
$success = "";

// 1. Check for messages from register.php (Redirection messages)
if(isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
if(isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// 2. LOGIN LOGIC (Updated to work even if button is disabled by JS)
// Register form posts to register.php, so any POST to this file MUST be login
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Select user by email
    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify Password
        if (password_verify($password, $row['password'])) {
            // Set Session Variables
            $_SESSION['email'] = $row['email'];
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['firstName'] = $row['firstName'];
            $_SESSION['lastName'] = $row['lastName'];
            $_SESSION['role'] = $row['role']; 
            
            // Redirect to Homepage
            header("Location: homepage.php");
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found with this email.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Shop - Login & Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f50057;
            --accent-color: #00d4ff;
            --success-color: #4caf50;
            --error-color: #f44336;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 10px 40px rgba(0,0,0,0.1);
            --shadow-hover: 0 20px 60px rgba(108,99,255,0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            display: flex;
            position: relative;
        }

        .left-panel {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--white);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .left-panel h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .left-panel p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }

        .brand-logo {
            font-size: 4rem;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .right-panel {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tabs {
            display: flex;
            background: var(--bg-light);
            border-radius: 12px;
            padding: 6px;
            margin-bottom: 40px;
            position: relative;
        }

        .tab {
            flex: 1;
            padding: 15px 20px;
            text-align: center;
            cursor: pointer;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .tab.active {
            background: var(--white);
            color: var(--primary-color);
            box-shadow: 0 4px 20px rgba(108,99,255,0.15);
        }

        .form-container {
            display: none;
            animation: fadeInUp 0.5s ease;
        }

        .form-container.active {
            display: block;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            transition: color 0.3s ease;
        }

        .form-input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(108,99,255,0.1);
        }

        .form-input:focus + i {
            color: var(--primary-color);
        }

        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(244, 67, 54, 0.2);
        }

        .success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.2);
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: var(--accent-color);
        }

        .social-login {
            margin-top: 30px;
            text-align: center;
        }

        .social-login p {
            color: var(--text-light);
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .social-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .social-btn {
            width: 50px;
            height: 50px;
            border: 2px solid #e1e8ed;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .social-btn:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(108,99,255,0.15);
        }

        .social-btn i {
            font-size: 1.2rem;
            color: var(--text-light);
            transition: color 0.3s ease;
        }

        .social-btn:hover i {
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                max-width: 400px;
            }
            
            .left-panel {
                padding: 40px 20px;
            }
            
            .left-panel h1 {
                font-size: 2rem;
            }
            
            .right-panel {
                padding: 40px 20px;
            }
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .shape {
            position: absolute;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            animation: float-shape 8s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 20%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float-shape {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
            </div>
            <div class="brand-logo">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h1>Welcome to E-Shop</h1>
            <p>Your one-stop destination for quality products. Sign in to explore our amazing collection and enjoy seamless shopping experience.</p>
        </div>

        <div class="right-panel">
            <div class="tabs">
                <div class="tab active" onclick="showForm('login')">
                    <i class="fas fa-sign-in-alt"></i> Login
                </div>
                <div class="tab" onclick="showForm('register')">
                    <i class="fas fa-user-plus"></i> Register
                </div>
            </div>

            <?php if(!empty($error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div id="loginForm" class="form-container active">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="login-email">Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" id="login-email" name="email" class="form-input" placeholder="Enter your email" required>
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="login-password" name="password" class="form-input" placeholder="Enter your password" required>
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                    <button type="submit" name="signIn" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
                
                <div class="forgot-password">
                    <a href="#"><i class="fas fa-key"></i> Forgot Password?</a>
                </div>

                <div class="social-login">
                    <p>Or continue with</p>
                    <div class="social-buttons">
                        <div class="social-btn">
                            <i class="fab fa-google"></i>
                        </div>
                        <div class="social-btn">
                            <i class="fab fa-facebook-f"></i>
                        </div>
                        <div class="social-btn">
                            <i class="fab fa-twitter"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div id="registerForm" class="form-container">
                <form method="post" action="register.php">
                    <div class="form-group">
                        <label for="reg-fname">First Name</label>
                        <div class="input-wrapper">
                            <input type="text" id="reg-fname" name="fName" class="form-input" placeholder="Enter your first name" required>
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg-lname">Last Name</label>
                        <div class="input-wrapper">
                            <input type="text" id="reg-lname" name="lName" class="form-input" placeholder="Enter your last name" required>
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg-email">Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" id="reg-email" name="email" class="form-input" placeholder="Enter your email" required>
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg-password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="reg-password" name="password" class="form-input" placeholder="Create a password" required>
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                    <button type="submit" name="signUp" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <div class="social-login">
                    <p>Or sign up with</p>
                    <div class="social-buttons">
                        <div class="social-btn">
                            <i class="fab fa-google"></i>
                        </div>
                        <div class="social-btn">
                            <i class="fab fa-facebook-f"></i>
                        </div>
                        <div class="social-btn">
                            <i class="fab fa-twitter"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showForm(formType) {
            // Hide all forms
            document.querySelectorAll('.form-container').forEach(form => {
                form.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected form and activate tab
            if(formType === 'login') {
                document.getElementById('loginForm').classList.add('active');
                document.querySelector('.tab:first-child').classList.add('active');
            } else {
                document.getElementById('registerForm').classList.add('active');
                document.querySelector('.tab:last-child').classList.add('active');
            }
        }

        // Add input focus effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('i').style.color = '#6c63ff';
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.querySelector('i').style.color = '#7f8c8d';
                }
            });
        });

        // Add loading state to buttons
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const button = this.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                button.disabled = true; // This line was causing the issue, fixed by updating PHP
                
                // Re-enable after 3 seconds (in case of error)
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 3000);
            });
        });
    </script>
</body>
</html>