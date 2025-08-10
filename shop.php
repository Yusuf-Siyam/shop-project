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
            padding: 0 20px;
        }



        /* Shop Section Styles */
        .shop-section {
            padding: 3rem 0;
        }

        .section-title {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }

        .shop-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
        }

        /* Sidebar Styles */
        .shop-sidebar {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            height: fit-content;
        }

        .sidebar-widget {
            margin-bottom: 2rem;
        }

        .sidebar-widget h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .category-list {
            list-style: none;
        }

        .category-list li {
            margin-bottom: 0.5rem;
        }

        .category-list a {
            text-decoration: none;
            color: var(--text-color);
            display: block;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .category-list a:hover,
        .category-list a.active {
            background: var(--primary-color);
            color: var(--white);
        }

        /* Product Grid Styles */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-img {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-tag {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--secondary-color);
            color: var(--white);
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .product-details {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .product-price {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .product-actions {
            display: flex;
            gap: 1rem;
        }

        .add-to-cart {
            flex: 1;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .add-to-cart:hover {
            background: #5a52d5;
        }

        .wishlist-btn {
            background: var(--white);
            border: 1px solid #ddd;
            padding: 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .wishlist-btn:hover {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Cart Sidebar Styles */
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: var(--white);
            box-shadow: var(--shadow);
            padding: 2rem;
            transition: right 0.3s;
            z-index: 1001;
        }

        .cart-sidebar.active {
            right: 0;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .close-cart {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color);
        }

        .cart-items {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item-img {
            width: 80px;
            height: 80px;
        }

        .cart-item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .cart-item-price {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .qty-btn {
            background: var(--light-bg);
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            cursor: pointer;
        }

        .remove-item {
            background: none;
            border: none;
            color: var(--secondary-color);
            cursor: pointer;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .shop-container {
                grid-template-columns: 1fr;
            }

            .shop-sidebar {
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 768px) {
            .cart-sidebar {
                width: 100%;
                right: -100%;
            }
        }
    </style>
</head>
<body>
        <!-- Include Navigation Component -->
    <?php include 'nav-component.php'; ?>

    <!-- Shop Section -->
    <section class="shop-section">
        <div class="container">
            <h2 class="section-title">Our Products</h2>
            
            <div class="shop-container">
                <!-- Sidebar -->
                <div class="shop-sidebar">
                    <div class="sidebar-widget">
                        <h3>Categories</h3>
                        <ul class="category-list">
                            <li>
                                <a href="shop.php?category=all" class="<?php echo $categoryFilter == 'all' ? 'active' : ''; ?>">
                                    All Categories
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="shop.php?category=<?php echo htmlspecialchars($category['name']); ?>" 
                                   class="<?php echo $categoryFilter == $category['name'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
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
                
                <!-- Products Grid -->
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
                                            <form method="post" action="">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" name="add_to_cart" class="add-to-cart">
                                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="index.php" class="add-to-cart">
                                                <i class="fas fa-sign-in-alt"></i> Login to Buy
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
    
    <!-- Cart Sidebar -->
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
                        <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                        <li>
                            <a href="shop.php?category=<?php echo $category['name']; ?>">
                                <?php echo $category['name']; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
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
        // Cart Sidebar
        const cartBtn = document.getElementById('cart-btn');
        const cartSidebar = document.getElementById('cart-sidebar');
        const closeCart = document.getElementById('close-cart');
        const overlay = document.getElementById('overlay');
        
        cartBtn.addEventListener('click', (e) => {
            e.preventDefault();
            cartSidebar.classList.add('active');
            overlay.classList.add('active');
        });
        
        closeCart.addEventListener('click', () => {
            cartSidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
        
        overlay.addEventListener('click', () => {
            cartSidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
        
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
            // You would typically use AJAX to update cart without page refresh
            // This is a simplified version - in a real application, use fetch or XMLHttpRequest
            window.location.href = `update-cart.php?id=${cartId}&action=${action}`;
        }
        
        function removeCartItem(cartId) {
            window.location.href = `update-cart.php?id=${cartId}&action=remove`;
        }
    </script>
</body>
</html> 