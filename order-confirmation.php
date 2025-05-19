<?php
session_start();
include("connect.php");

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Check if order_id is provided
if(!isset($_GET['order_id'])) {
    header("Location: shop.php");
    exit();
}

$orderId = $_GET['order_id'];

// Get order details
$orderQuery = "SELECT * FROM orders WHERE id = $orderId AND user_id = $userId";
$orderResult = mysqli_query($conn, $orderQuery);

if(mysqli_num_rows($orderResult) == 0) {
    header("Location: shop.php");
    exit();
}

$order = mysqli_fetch_assoc($orderResult);

// Get order items
$itemsQuery = "SELECT oi.*, p.name, p.image FROM order_items oi
              JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = $orderId";
$itemsResult = mysqli_query($conn, $itemsQuery);

$orderItems = [];
while($item = mysqli_fetch_assoc($itemsResult)) {
    $orderItems[] = $item;
}

// Format order date
$orderDate = date("F j, Y, g:i a", strtotime($order['created_at']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Shop - Order Confirmation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="e-shop.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <h1>E-<span>Shop</span></h1>
                </div>
                
                <ul class="nav-links">
                    <li><a href="homepage.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
                
                <div class="nav-icons">
                    <div class="nav-icon">
                        <a href="#" class="cart-icon" id="cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="count">0</span>
                        </a>
                    </div>
                    
                    <div class="nav-icon">
                        <a href="account.php"><i class="fas fa-user"></i></a>
                    </div>
                </div>
                
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </nav>
        </div>
    </header>

    <!-- Order Confirmation Section -->
    <section class="confirmation-section">
        <div class="container">
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Order Confirmed!</h2>
                <p>Thank you for your purchase. Your order has been received and is being processed.</p>
            </div>
            
            <div class="order-details">
                <div class="order-info">
                    <div class="info-item">
                        <h4>Order Number</h4>
                        <p>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Date</h4>
                        <p><?php echo $orderDate; ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Total</h4>
                        <p>$<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Payment Method</h4>
                        <p><?php echo $order['payment_method']; ?></p>
                    </div>
                </div>
                
                <h3>Order Summary</h3>
                <div class="order-items">
                    <?php foreach($orderItems as $item): ?>
                        <div class="order-item">
                            <div class="order-item-img">
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="order-item-info">
                                <h4><?php echo $item['name']; ?></h4>
                                <div class="order-item-details">
                                    <span class="order-item-price">$<?php echo $item['price']; ?></span>
                                    <span class="order-item-quantity">× <?php echo $item['quantity']; ?></span>
                                </div>
                            </div>
                            <div class="order-item-total">
                                $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-totals">
                    <div class="order-subtotal">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="order-shipping">
                        <span>Shipping</span>
                        <span>Free</span>
                    </div>
                    <div class="order-total">
                        <span>Total</span>
                        <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
                
                <h3>Shipping Details</h3>
                <div class="shipping-details">
                    <p><strong>Address:</strong> <?php echo $order['shipping_address']; ?></p>
                    <p><strong>City:</strong> <?php echo $order['shipping_city']; ?></p>
                    <p><strong>Postal Code:</strong> <?php echo $order['shipping_postal_code']; ?></p>
                    <p><strong>Phone:</strong> <?php echo $order['shipping_phone']; ?></p>
                </div>
                
                <div class="confirmation-actions">
                    <a href="shop.php" class="btn primary-btn">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                    <a href="account.php?tab=orders" class="btn secondary-btn">
                        <i class="fas fa-list"></i> View All Orders
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column footer-logo">
                    <h2>E-<span>Shop</span></h2>
                    <p>Your one-stop destination for all your shopping needs.</p>
                </div>
                
                <div class="footer-column footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="homepage.php">Home</a></li>
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-column footer-links">
                    <h3>Categories</h3>
                    <ul>
                        <?php
                        $categoriesResult = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC LIMIT 4");
                        while($category = mysqli_fetch_assoc($categoriesResult)):
                        ?>
                        <li>
                            <a href="shop.php?category=<?php echo $category['name']; ?>">
                                <?php echo $category['name']; ?>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                
                <div class="footer-column footer-social">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="copyright">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> E-Shop. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Toggle Mobile Navigation
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');
        
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
    </script>
</body>
</html> 