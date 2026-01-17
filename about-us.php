<?php
session_start();
include("connect.php");

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Get user info
$email = $_SESSION['email'];
$userFirstName = $userLastName = "";
$query = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'");
if($row = mysqli_fetch_array($query)) {
    $userFirstName = $row['firstName'];
    $userLastName = $row['lastName'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - E-Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f50057;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
        }

        .hero-section {
            text-align: center;
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--primary-color), #8b85ff);
            color: var(--white);
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover;
            opacity: 0.1;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .hero-section p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .about-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .about-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s;
        }

        .about-card:hover {
            transform: translateY(-5px);
        }

        .about-card i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .about-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .about-card p {
            color: #666;
        }

        .story-section {
            background: var(--white);
            padding: 4rem 0;
            margin-bottom: 4rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .story-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        .story-image {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
        }

        .story-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .story-text h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .story-text p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .stats-section {
            background: var(--white);
            padding: 4rem 0;
            margin-bottom: 4rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item {
            padding: 2rem;
            background: var(--light-bg);
            border-radius: 12px;
            transition: transform 0.3s;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-item h3 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            color: #666;
            font-size: 1.1rem;
        }

        .team-section {
            margin-bottom: 4rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .section-title p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .team-member {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            text-align: center;
            transition: transform 0.3s;
        }

        .team-member:hover {
            transform: translateY(-5px);
        }

        .member-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .member-info {
            padding: 1.5rem;
        }

        .member-info h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .member-info p {
            color: #666;
            margin-bottom: 1rem;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .social-links a {
            color: var(--primary-color);
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        .social-links a:hover {
            color: var(--secondary-color);
        }

        .values-section {
            background: var(--white);
            padding: 4rem 0;
            margin-bottom: 4rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .value-item {
            text-align: center;
            padding: 2rem;
            background: var(--light-bg);
            border-radius: 12px;
            transition: transform 0.3s;
        }

        .value-item:hover {
            transform: translateY(-5px);
        }

        .value-item i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .value-item h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .value-item p {
            color: #666;
        }

        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5rem;
            }

            .hero-section p {
                font-size: 1rem;
            }

            .story-content {
                grid-template-columns: 1fr;
            }

            .story-image {
                height: 300px;
            }

            .section-title h2 {
                font-size: 2rem;
            }
        }

        .nav-menu {
            background: var(--white);
            padding: 1rem 0;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-menu span {
            color: var(--text-color);
        }

        .logout-btn {
            background: var(--secondary-color);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #d5004f;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }

            .user-menu {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="nav-menu">
        <div class="nav-container">
            <a href="homepage.php" class="nav-logo">E-Shop</a>
            <div class="nav-links">
                <a href="shop.php"><i class="fas fa-store"></i> Shop</a>
                <a href="view-order.php"><i class="fas fa-list"></i> Orders</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
            </div>
            <div class="user-menu">
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($userFirstName . ' ' . $userLastName); ?></span>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1>Welcome to E-Shop</h1>
            <p>Your trusted destination for quality products and exceptional shopping experience since 2020</p>
        </div>
    </div>

    <div class="container">
        <div class="about-content">
            <div class="about-card">
                <i class="fas fa-shopping-bag"></i>
                <h3>Our Mission</h3>
                <p>To provide customers with high-quality products at competitive prices while ensuring an exceptional shopping experience.</p>
            </div>
            <div class="about-card">
                <i class="fas fa-eye"></i>
                <h3>Our Vision</h3>
                <p>To become the leading e-commerce platform known for customer satisfaction, product quality, and innovative shopping solutions.</p>
            </div>
            <div class="about-card">
                <i class="fas fa-heart"></i>
                <h3>Our Values</h3>
                <p>Quality, Integrity, Customer Satisfaction, and Innovation are the core values that drive our business forward.</p>
            </div>
        </div>

        <div class="story-section">
            <div class="container">
                <div class="story-content">
                    <div class="story-image">
                        <img src="https://images.unsplash.com/photo-1607082349566-187342175e2f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Our Story">
                    </div>
                    <div class="story-text">
                        <h2>Our Story</h2>
                        <p>Founded in 2020, E-Shop began with a simple idea: to make online shopping easier, more enjoyable, and more accessible for everyone. What started as a small team of passionate individuals has grown into a thriving e-commerce platform serving thousands of customers worldwide.</p>
                        <p>Today, we're proud to offer a wide range of products, from electronics to fashion, home goods to beauty products, all carefully selected to meet our high standards of quality and value.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="values-section">
            <div class="container">
                <div class="section-title">
                    <h2>Our Core Values</h2>
                    <p>The principles that guide everything we do</p>
                </div>
                <div class="values-grid">
                    <div class="value-item">
                        <i class="fas fa-star"></i>
                        <h3>Quality First</h3>
                        <p>We never compromise on the quality of our products and services.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-handshake"></i>
                        <h3>Customer Trust</h3>
                        <p>Building lasting relationships through transparency and reliability.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-lightbulb"></i>
                        <h3>Innovation</h3>
                        <p>Continuously improving and adapting to meet customer needs.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-users"></i>
                        <h3>Community</h3>
                        <p>Creating a positive impact in the communities we serve.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <h3>10K+</h3>
                        <p>Happy Customers</p>
                    </div>
                    <div class="stat-item">
                        <h3>5K+</h3>
                        <p>Products</p>
                    </div>
                    <div class="stat-item">
                        <h3>24/7</h3>
                        <p>Customer Support</p>
                    </div>
                    <div class="stat-item">
                        <h3>100%</h3>
                        <p>Satisfaction</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="team-section">
            <div class="section-title">
                <h2>Meet Our Team</h2>
                <p>The passionate individuals behind E-Shop's success</p>
            </div>
            <div class="team-grid">
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a" alt="Team Member" class="member-image">
                    <div class="member-info">
                        <h3>John Doe</h3>
                        <p>Founder & CEO</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e" alt="Team Member" class="member-image">
                    <div class="member-info">
                        <h3>Jane Smith</h3>
                        <p>Operations Manager</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7" alt="Team Member" class="member-image">
                    <div class="member-info">
                        <h3>Mike Johnson</h3>
                        <p>Customer Support Lead</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 