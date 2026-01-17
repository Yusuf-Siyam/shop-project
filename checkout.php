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

// Get user information
$userFirstName = $userLastName = "";
$query = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'");
while($row = mysqli_fetch_array($query)) {
    $userFirstName = $row['firstName'];
    $userLastName = $row['lastName'];
}

// Get cart items
$cartQuery = "SELECT c.*, p.name, p.price, p.image FROM cart c
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = $userId";
$cartResult = mysqli_query($conn, $cartQuery);

$cartItems = [];
$totalAmount = 0;

if (mysqli_num_rows($cartResult) > 0) {
    while ($item = mysqli_fetch_assoc($cartResult)) {
        $cartItems[] = $item;
        $totalAmount += $item['price'] * $item['quantity'];
    }
}

// Handle checkout submission
$message = "";
$error = "";

if (isset($_POST['place_order'])) {
    // Validate form inputs
    $shippingAddress = $_POST['address'];
    $shippingCity = $_POST['city'];
    $shippingPostalCode = $_POST['postal_code'];
    $shippingPhone = $_POST['phone'];
    $paymentMethod = $_POST['payment_method'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    // Create order using prepared statement
    $createOrderQuery = "INSERT INTO orders (user_id, total_amount, payment_method, shipping_address, 
                        shipping_city, shipping_postal_code, shipping_phone, notes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($createOrderQuery);
    
    // Error handling for prepare
    if ($stmt === false) {
        die("Error preparing order query: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("idssssss", $userId, $totalAmount, $paymentMethod, $shippingAddress, 
                      $shippingCity, $shippingPostalCode, $shippingPhone, $notes);
    
    if ($stmt->execute()) {
        $orderId = mysqli_insert_id($conn);
        
        // Add order items using prepared statement
        $addItemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $itemStmt = $conn->prepare($addItemQuery);
        
        if ($itemStmt) {
            foreach ($cartItems as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                
                $itemStmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
                $itemStmt->execute();
            }
            $itemStmt->close();
        }
        
        // Clear the cart using prepared statement
        $clearCartQuery = "DELETE FROM cart WHERE user_id = ?";
        $clearStmt = $conn->prepare($clearCartQuery);
        if ($clearStmt) {
            $clearStmt->bind_param("i", $userId);
            $clearStmt->execute();
            $clearStmt->close();
        }
        
        // Redirect to order confirmation
        header("Location: order-confirmation.php?order_id=$orderId");
        exit();
    } else {
        $error = "Error creating order: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - E-Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6c63ff;
            --primary-dark: #5a52d5;
            --secondary: #f50057;
            --text-dark: #2c3e50;
            --text-gray: #7f8c8d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 5px 20px rgba(0,0,0,0.05);
            --border: #e1e8ed;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* --- CHECKOUT SPECIFIC STYLES --- */
        .checkout-container-wrapper {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            flex: 1; /* Pushes footer down */
        }

        .checkout-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .checkout-title h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-gray);
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 10px 20px;
            background: var(--white);
            border-radius: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .back-link:hover {
            color: var(--primary);
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.15);
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
        }

        @media (max-width: 992px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow);
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--primary);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.1);
        }

        textarea.form-control {
            resize: vertical;
        }

        .payment-options {
            display: grid;
            gap: 15px;
        }

        .payment-radio {
            display: none;
        }

        .payment-label {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-label i {
            font-size: 1.2rem;
            margin-right: 15px;
            color: var(--text-gray);
        }

        .payment-radio:checked + .payment-label {
            border-color: var(--primary);
            background: rgba(108, 99, 255, 0.05);
        }

        .payment-radio:checked + .payment-label i {
            color: var(--primary);
        }

        .payment-label.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #f9f9f9;
        }

        .summary-items {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding-right: 5px;
        }

        .summary-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 4px;
            display: block;
        }

        .item-meta {
            font-size: 0.85rem;
            color: var(--text-gray);
        }

        .item-total {
            font-weight: 600;
            color: var(--primary);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
            color: var(--text-gray);
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px dashed var(--border);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .btn-place-order {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 25px;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        }

        .btn-place-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 99, 255, 0.4);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .btn-shop {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        /* --- NEW MODERN FOOTER STYLES --- */
        .footer {
            background-color: #1a1a1a;
            color: #ffffff;
            padding: 70px 0 30px;
            margin-top: 60px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .footer-logo h2 {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #fff;
        }

        .footer-logo span {
            color: var(--primary);
        }

        .footer-desc {
            color: #b0b0b0;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .footer-heading {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-heading::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #b0b0b0;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .footer-links a:hover {
            color: var(--primary);
            transform: translateX(5px);
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .newsletter-form {
            display: flex;
            margin-top: 15px;
        }

        .newsletter-input {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px 0 0 5px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            outline: none;
        }

        .newsletter-btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 0 15px;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            transition: background 0.3s;
        }

        .newsletter-btn:hover {
            background: var(--primary-dark);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 50px;
            padding-top: 30px;
            text-align: center;
            color: #888;
            font-size: 0.9rem;
        }

        .payment-methods-footer {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 15px;
            font-size: 1.5rem;
            color: #ccc;
        }
    </style>
</head>
<body>
    <?php include 'nav-component.php'; ?>

    <div class="checkout-container-wrapper">
        <div class="checkout-header">
            <div class="checkout-title">
                <h2>Secure Checkout</h2>
            </div>
            <a href="shop.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Shop
            </a>
        </div>

        <?php if(count($cartItems) > 0): ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="checkout-content">
                    
                    <div class="checkout-left">
                        <div class="card" style="margin-bottom: 30px;">
                            <div class="card-title">
                                <i class="fas fa-map-marker-alt"></i> Shipping Details
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($userFirstName); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($userLastName); ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+880..." required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Delivery Address</label>
                                <input type="text" name="address" class="form-control" placeholder="House no, Street name, Area" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="city" class="form-control" placeholder="Dhaka" required>
                                </div>
                                <div class="form-group">
                                    <label>Postal Code</label>
                                    <input type="text" name="postal_code" class="form-control" placeholder="1234" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Order Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Notes about your order, e.g. special delivery instructions."></textarea>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-title">
                                <i class="fas fa-wallet"></i> Payment Method
                            </div>
                            <div class="payment-options">
                                <div>
                                    <input type="radio" id="cod" name="payment_method" value="Cash on Delivery" class="payment-radio" checked>
                                    <label for="cod" class="payment-label">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Cash on Delivery (Pay upon arrival)</span>
                                    </label>
                                </div>
                                
                                <div>
                                    <input type="radio" id="card" name="payment_method" value="Card" class="payment-radio" disabled>
                                    <label for="card" class="payment-label disabled">
                                        <i class="far fa-credit-card"></i>
                                        <span>Credit / Debit Card (Coming Soon)</span>
                                    </label>
                                </div>

                                <div>
                                    <input type="radio" id="bkash" name="payment_method" value="Bkash" class="payment-radio" disabled>
                                    <label for="bkash" class="payment-label disabled">
                                        <i class="fas fa-mobile-alt"></i>
                                        <span>Mobile Banking (Coming Soon)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-right">
                        <div class="card" style="position: sticky; top: 100px;">
                            <div class="card-title">
                                <i class="fas fa-shopping-bag"></i> Order Summary
                            </div>
                            
                            <div class="summary-items">
                                <?php foreach($cartItems as $item): ?>
                                    <div class="summary-item">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Product" class="item-image">
                                        <div class="item-info">
                                            <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                            <span class="item-meta">Qty: <?php echo $item['quantity']; ?></span>
                                        </div>
                                        <div class="item-total">
                                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($totalAmount, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span style="color: var(--primary);">Free</span>
                            </div>
                            <div class="summary-total">
                                <span>Total</span>
                                <span>$<?php echo number_format($totalAmount, 2); ?></span>
                            </div>

                            <button type="submit" name="place_order" class="btn-place-order">
                                Place Order <i class="fas fa-arrow-right" style="margin-left:8px;"></i>
                            </button>
                            
                            <div style="text-align:center; margin-top:15px; font-size:0.85rem; color:var(--text-gray);">
                                <i class="fas fa-lock"></i> Secure Checkout
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        <?php else: ?>
            <div class="card empty-cart">
                <i class="fas fa-shopping-basket"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added anything to your cart yet.</p>
                <a href="shop.php" class="btn-shop">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-column">
                <div class="footer-logo">
                    <h2>E-<span>Shop</span></h2>
                </div>
                <p class="footer-desc">
                    Your premier destination for quality products. We are dedicated to providing the best shopping experience with fast delivery and top-notch customer support.
                </p>
                <div class="social-icons">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="footer-column">
                <h3 class="footer-heading">Quick Links</h3>
                <div class="footer-links">
                    <ul>
                        <li><a href="homepage.php">Home</a></li>
                        <li><a href="shop.php">Shop All</a></li>
                        <li><a href="about-us.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="view-order.php">My Orders</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-column">
                <h3 class="footer-heading">Customer Care</h3>
                <div class="footer-links">
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Shipping Policy</a></li>
                        <li><a href="#">Returns & Exchanges</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-column">
                <h3 class="footer-heading">Newsletter</h3>
                <p style="color: #b0b0b0; font-size: 0.9rem; margin-bottom: 15px;">Subscribe to get updates on new arrivals and special offers.</p>
                <form class="newsletter-form" onsubmit="event.preventDefault();">
                    <input type="email" placeholder="Your email address" class="newsletter-input">
                    <button class="newsletter-btn"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> E-Shop. All Rights Reserved.</p>
            <div class="payment-methods-footer">
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-paypal"></i>
                <i class="fab fa-cc-amex"></i>
            </div>
        </div>
    </footer>
</body>
</html>