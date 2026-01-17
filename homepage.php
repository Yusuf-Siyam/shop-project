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
$stmt = $conn->prepare("SELECT * FROM `users` WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$query = $stmt->get_result();
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
    <title>Dashboard - E-Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            max-width: 1100px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 24px 24px 24px;
        }
        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        .dashboard-header .user-info {
            display: flex;
            align-items: center;
        }
        .dashboard-header .user-info i {
            font-size: 2.5rem;
            color: #6c63ff;
            margin-right: 16px;
        }
        .dashboard-header .user-info span {
            font-size: 1.2rem;
            font-weight: 500;
        }
        .dashboard-header .logout-btn {
            background: #f50057;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .dashboard-header .logout-btn:hover {
            background: #c51162;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 28px;
        }
        .dashboard-card {
            background: #f4f6ff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(108,99,255,0.07);
            padding: 32px 20px;
            text-align: center;
            transition: box-shadow 0.2s, transform 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }
        .dashboard-card:hover {
            box-shadow: 0 6px 24px rgba(108,99,255,0.13);
            transform: translateY(-4px) scale(1.03);
            color: #6c63ff;
        }
        .dashboard-card i {
            font-size: 2.2rem;
            margin-bottom: 12px;
            color: #6c63ff;
        }
        .dashboard-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .dashboard-card p {
            font-size: 0.97rem;
            color: #666;
        }
        @media (max-width: 600px) {
            .dashboard-container {
                padding: 12px 2px;
                margin: 20px 15px;
            }
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Include Navigation Component -->
    <?php include 'nav-component.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Welcome, <?php echo htmlspecialchars($userFirstName . ' ' . $userLastName); ?>!</span>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="dashboard-grid">
            <a href="shop.php" class="dashboard-card">
                <i class="fas fa-store"></i>
                <h3>Shop</h3>
                <p>Browse and buy products</p>
            </a>
            <a href="checkout.php" class="dashboard-card">
                <i class="fas fa-shopping-cart"></i>
                <h3>Cart & Checkout</h3>
                <p>View your cart and place orders</p>
            </a>
            <a href="order-confirmation.php" class="dashboard-card">
                <i class="fas fa-clipboard-check"></i>
                <h3>Order Confirmation</h3>
                <p>See your latest order status</p>
            </a>
            <a href="view-order.php" class="dashboard-card">
                <i class="fas fa-list"></i>
                <h3>Order Details</h3>
                <p>View all your orders</p>
            </a>
            <a href="admin-products.php" class="dashboard-card">
                <i class="fas fa-boxes"></i>
                <h3>Product Management</h3>
                <p>Add, edit, or delete products</p>
            </a>
            <a href="admin.php" class="dashboard-card">
                <i class="fas fa-user-shield"></i>
                <h3>Admin Panel</h3>
                <p>Manage site, projects, and messages</p>
            </a>
            <a href="shop.php" class="dashboard-card">
                <i class="fas fa-search"></i>
                <h3>Product Search</h3>
                <p>Find products by name or category</p>
            </a>
            <a href="about-us.php" class="dashboard-card">
                <i class="fas fa-info-circle"></i>
                <h3>About Us</h3>
                <p>Learn more about our company</p>
            </a>
        </div>
    </div>
</body>
</html>
