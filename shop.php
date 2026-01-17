<?php
session_start();
include("connect.php");

// Check if user is logged in
$loggedIn = isset($_SESSION['email']);

// Set default category filter
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Fetch all products with error handling
$productsQuery = "SELECT * FROM products";
$params = [];
$types = "";

if ($categoryFilter != 'all') {
    $productsQuery .= " WHERE category = ?";
    $params[] = $categoryFilter;
    $types .= "s";
}
$productsQuery .= " ORDER BY created_at DESC";

$productsStmt = $conn->prepare($productsQuery);
if (!empty($params)) {
    $productsStmt->bind_param($types, ...$params);
}
$productsStmt->execute();
$productsResult = $productsStmt->get_result();

if (!$productsResult) {
    die("Error fetching products: " . $productsStmt->error);
}

// Fetch all categories with error handling
$categoriesStmt = $conn->prepare("SELECT * FROM categories ORDER BY name ASC");
$categoriesStmt->execute();
$categoriesResult = $categoriesStmt->get_result();
if (!$categoriesResult) {
    die("Error fetching categories: " . $categoriesStmt->error);
}

$categories = [];
while ($row = mysqli_fetch_assoc($categoriesResult)) {
    $categories[] = $row;
}

// Handle add to cart
if (isset($_POST['add_to_cart']) && $loggedIn) {
    $productId = (int)$_POST['product_id'];
    $userId = (int)$_SESSION['user_id'];
    $quantity = 1;
    
    // Check if product already in cart using prepared statement
    $checkCartQuery = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $checkCartStmt = $conn->prepare($checkCartQuery);
    $checkCartStmt->bind_param("ii", $userId, $productId);
    $checkCartStmt->execute();
    $checkCartResult = $checkCartStmt->get_result();
    
    if (!$checkCartResult) {
        die("Error checking cart: " . $checkCartStmt->error);
    }
    
    if ($checkCartResult->num_rows > 0) {
        // Update quantity using prepared statement
        $cartItem = $checkCartResult->fetch_assoc();
        $newQuantity = $cartItem['quantity'] + 1;
        $updateQuery = "UPDATE cart SET quantity = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ii", $newQuantity, $cartItem['id']);
        if (!$updateStmt->execute()) {
            die("Error updating cart: " . $updateStmt->error);
        }
        $updateStmt->close();
    } else {
        // Add new item to cart using prepared statement
        $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("iii", $userId, $productId, $quantity);
        if (!$insertStmt->execute()) {
            die("Error adding to cart: " . $insertStmt->error);
        }
        $insertStmt->close();
    }
    
    $checkCartStmt->close();
    
    // Redirect to prevent form resubmission
    header("Location: shop.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Shop - Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6c63ff;
            --primary-dark: #5a52d5;
            --secondary-color: #f50057;
            --text-color: #2c3e50;
            --text-gray: #7f8c8d;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 5px 20px rgba(0,0,0,0.05);
            --border: #e1e8ed;
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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* --- Header Override --- */
        /* Forces the nav component to have a clean white background with shadow */
        .smart-nav {
            background-color: var(--white) !important;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05) !important;
            border-bottom: 1px solid var(--border);
        }

        /* Shop Section Styles */
        .shop-section {
            padding: 4rem 0;
            flex: 1; /* Pushes footer down */
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: var(--text-color);
            font-weight: 700;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            margin: 10px auto 0;
            border-radius: 2px;
        }

        .shop-container {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 2.5rem;
        }

        /* Sidebar Styles */
        .shop-sidebar {
            background: var(--white);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .sidebar-widget {
            margin-bottom: 2.5rem;
        }

        .sidebar-widget:last-child {
            margin-bottom: 0;
        }

        .sidebar-widget h3 {
            font-size: 1.2rem;
            margin-bottom: 1.2rem;
            color: var(--text-color);
            font-weight: 600;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .category-list {
            list-style: none;
        }

        .category-list li {
            margin-bottom: 0.5rem;
        }

        .category-list a {
            text-decoration: none;
            color: var(--text-gray);
            display: block;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .category-list a:hover {
            color: var(--primary-color);
            background: rgba(108, 99, 255, 0.05);
            transform: translateX(5px);
        }

        .category-list a.active {
            background: var(--primary-color);
            color: var(--white);
            box-shadow: 0 4px 10px rgba(108, 99, 255, 0.3);
        }

        /* Price Filter */
        .price-filter {
            padding: 0 5px;
        }
        
        .slider {
            width: 100%;
            cursor: pointer;
            accent-color: var(--primary-color);
        }
        
        .price-values {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Product Grid Styles */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: rgba(108, 99, 255, 0.1);
        }

        .product-img {
            position: relative;
            height: 220px;
            overflow: hidden;
            background: #f9f9f9;
        }

        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-img img {
            transform: scale(1.05);
        }

        .product-tag {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--secondary-color);
            color: var(--white);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(245, 0, 87, 0.3);
            z-index: 2;
        }

        .product-details {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-price {
            display: flex;
            align-items: center;
            margin-bottom: 1.2rem;
        }

        .current-price {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.3rem;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .add-to-cart {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            border: none;
            padding: 0.8rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none; /* In case it's an <a> tag */
        }

        .add-to-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }

        .wishlist-btn {
            background: var(--white);
            border: 2px solid #eee;
            width: 45px;
            height: 45px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-gray);
        }

        .wishlist-btn:hover {
            color: var(--secondary-color);
            border-color: rgba(245, 0, 87, 0.2);
            background: rgba(245, 0, 87, 0.05);
        }

        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem;
            background: var(--white);
            border-radius: 16px;
            color: var(--text-gray);
        }

        .no-products i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #ddd;
        }

        /* Cart Sidebar Styles */
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: var(--white);
            box-shadow: -5px 0 30px rgba(0,0,0,0.1);
            padding: 2rem;
            transition: right 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            z-index: 1001;
            display: flex;
            flex-direction: column;
        }

        .cart-sidebar.active {
            right: 0;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .cart-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close-cart {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-gray);
            transition: color 0.3s;
        }
        
        .close-cart:hover {
            color: var(--secondary-color);
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding-right: 5px;
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f9f9f9;
        }

        .cart-item-img {
            width: 70px;
            height: 70px;
            flex-shrink: 0;
        }

        .cart-item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-size: 0.95rem;
            margin-bottom: 0.3rem;
            font-weight: 600;
        }

        .cart-item-price {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .qty-btn {
            background: #f0f0f0;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: background 0.2s;
        }
        
        .qty-btn:hover {
            background: #e0e0e0;
        }

        .remove-item {
            background: none;
            border: none;
            color: #ffadb0;
            cursor: pointer;
            align-self: center;
            transition: color 0.3s;
        }
        
        .remove-item:hover {
            color: var(--secondary-color);
        }

        .cart-footer {
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid #f0f0f0;
        }
        
        .cart-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .checkout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }

        .empty-cart {
            text-align: center;
            padding: 2rem 0;
            color: var(--text-gray);
        }
        
        .empty-cart i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #e0e0e0;
        }
        
        .empty-cart .btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.6rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            backdrop-filter: blur(2px);
        }

        .overlay.active {
            display: block;
        }

        /* --- NEW MODERN DARK FOOTER --- */
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
            color: var(--primary-color);
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
            background: var(--primary-color);
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
            color: var(--primary-color);
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
            background: var(--primary-color);
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
            background: var(--primary-color);
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

        /* Responsive Design */
        @media (max-width: 992px) {
            .shop-container {
                grid-template-columns: 1fr;
            }

            .shop-sidebar {
                position: relative;
                top: 0;
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 768px) {
            .cart-sidebar {
                width: 100%;
                right: -100%;
            }
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
        <?php include 'nav-component.php'; ?>

    <section class="shop-section">
        <div class="container">
            <h2 class="section-title">Our Products</h2>
            
            <div class="shop-container">
                <div class="shop-sidebar">
                    <div class="sidebar-widget">
                        <h3>Categories</h3>
                        <ul class="category-list">
                            <li>
                                <a href="shop.php?category=all" class="<?php echo $categoryFilter == 'all' ? 'active' : ''; ?>">
                                    <i class="fas fa-th-large" style="margin-right: 8px;"></i> All Categories
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="shop.php?category=<?php echo htmlspecialchars($category['name']); ?>" 
                                   class="<?php echo $categoryFilter == $category['name'] ? 'active' : ''; ?>">
                                    <i class="fas fa-angle-right" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="sidebar-widget">
                        <h3>Price Range</h3>
                        <div class="price-filter">
                            <input type="range" min="0" max="1000" value="500" class="slider" id="priceRange">
                            <div class="price-values">
                                <span>$0</span>
                                <span id="priceValue">$500</span>
                                <span>$1000</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="product-grid">
                    <?php if (mysqli_num_rows($productsResult) > 0): ?>
                        <?php while ($product = mysqli_fetch_assoc($productsResult)): ?>
                            <div class="product-card">
                                <div class="product-img">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php if ($product['featured']): ?>
                                        <div class="product-tag featured-tag">Featured</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-details">
                                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <div class="product-price">
                                        <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                                    </div>
                                    <div class="product-actions">
                                        <?php if ($loggedIn): ?>
                                            <form method="post" action="" style="flex: 1;">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" name="add_to_cart" class="add-to-cart">
                                                    <i class="fas fa-shopping-cart"></i> Add
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="index.php" class="add-to-cart">
                                                <i class="fas fa-sign-in-alt"></i> Login
                                            </a>
                                        <?php endif; ?>
                                        <button class="wishlist-btn">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <i class="fas fa-box-open"></i>
                            <h3>No products found</h3>
                            <p>Sorry, we couldn't find any products in this category.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <div class="cart-sidebar" id="cart-sidebar">
        <div class="cart-header">
            <h3>Your Cart</h3>
            <button class="close-cart" id="close-cart">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="cart-items">
            <?php
            if ($loggedIn) {
                $userId = $_SESSION['user_id'];
                $cartQuery = "SELECT c.*, p.name, p.price, p.image FROM cart c
                              JOIN products p ON c.product_id = p.id 
                              WHERE c.user_id = $userId";
                $cartResult = mysqli_query($conn, $cartQuery);
                
                $totalAmount = 0;
                
                if (mysqli_num_rows($cartResult) > 0) {
                    while ($item = mysqli_fetch_assoc($cartResult)) {
                        $itemTotal = $item['price'] * $item['quantity'];
                        $totalAmount += $itemTotal;
                        ?>
                        <div class="cart-item">
                            <div class="cart-item-img">
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="cart-item-details">
                                <h4 class="cart-item-title"><?php echo $item['name']; ?></h4>
                                <div class="cart-item-price">$<?php echo $item['price']; ?></div>
                                <div class="cart-item-quantity">
                                    <button class="qty-btn qty-decrease" data-id="<?php echo $item['id']; ?>">-</button>
                                    <span class="qty-value"><?php echo $item['quantity']; ?></span>
                                    <button class="qty-btn qty-increase" data-id="<?php echo $item['id']; ?>">+</button>
                                </div>
                            </div>
                            <button class="remove-item" data-id="<?php echo $item['id']; ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <p>Your cart is empty</p>
                            <a href="shop.php" class="btn primary-btn">Start Shopping</a>
                          </div>';
                }
            } else {
                echo '<div class="empty-cart">
                        <i class="fas fa-sign-in-alt"></i>
                        <p>Please login to view your cart</p>
                        <a href="index.php" class="btn primary-btn">Login Now</a>
                      </div>';
            }
            ?>
        </div>
        
        <?php if ($loggedIn && isset($totalAmount) && $totalAmount > 0): ?>
        <div class="cart-footer">
            <div class="cart-total">
                <h4>Total:</h4>
                <span>$<?php echo number_format($totalAmount, 2); ?></span>
            </div>
            <a href="checkout.php" class="checkout-btn">
                Proceed to Checkout <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="overlay" id="overlay"></div>
    
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
    
    <script>
        // Cart Sidebar
        // Note: You need a button with id="cart-btn" in your navbar to trigger this
        const cartBtn = document.getElementById('cart-btn'); // Ensure this ID exists in nav-component.php
        const cartSidebar = document.getElementById('cart-sidebar');
        const closeCart = document.getElementById('close-cart');
        const overlay = document.getElementById('overlay');
        
        if (cartBtn) {
            cartBtn.addEventListener('click', (e) => {
                e.preventDefault();
                cartSidebar.classList.add('active');
                overlay.classList.add('active');
            });
        }
        
        if (closeCart) {
            closeCart.addEventListener('click', () => {
                cartSidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', () => {
                cartSidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
        
        // Price Range Slider
        const priceRange = document.getElementById('priceRange');
        const priceValue = document.getElementById('priceValue');
        
        if(priceRange && priceValue) {
            priceRange.addEventListener('input', () => {
                priceValue.textContent = '$' + priceRange.value;
            });
        }
        
        // Cart Quantity Update (using AJAX)
        const qtyIncrease = document.querySelectorAll('.qty-increase');
        const qtyDecrease = document.querySelectorAll('.qty-decrease');
        const removeItem = document.querySelectorAll('.remove-item');
        
        qtyIncrease.forEach(btn => {
            btn.addEventListener('click', () => {
                const cartId = btn.getAttribute('data-id');
                updateCartQuantity(cartId, 'increase');
            });
        });
        
        qtyDecrease.forEach(btn => {
            btn.addEventListener('click', () => {
                const cartId = btn.getAttribute('data-id');
                updateCartQuantity(cartId, 'decrease');
            });
        });
        
        removeItem.forEach(btn => {
            btn.addEventListener('click', () => {
                const cartId = btn.getAttribute('data-id');
                removeCartItem(cartId);
            });
        });
        
        function updateCartQuantity(cartId, action) {
            window.location.href = `update-cart.php?id=${cartId}&action=${action}`;
        }
        
        function removeCartItem(cartId) {
            window.location.href = `update-cart.php?id=${cartId}&action=remove`;
        }
    </script>
</body>
</html>