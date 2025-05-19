<?php
session_start();
include("connect.php");

// Check if user is logged in
$loggedIn = isset($_SESSION['email']);

// Set default category filter
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Fetch all products
$productsQuery = "SELECT * FROM products";
if ($categoryFilter != 'all') {
    $categoryFilter = mysqli_real_escape_string($conn, $categoryFilter);
    $productsQuery .= " WHERE category = '$categoryFilter'";
}
$productsQuery .= " ORDER BY created_at DESC";
$productsResult = mysqli_query($conn, $productsQuery);

// Fetch all categories
$categoriesResult = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$categories = [];
if ($categoriesResult) {
    while ($row = mysqli_fetch_assoc($categoriesResult)) {
        $categories[] = $row;
    }
}

// Handle add to cart
if (isset($_POST['add_to_cart']) && $loggedIn) {
    $productId = $_POST['product_id'];
    $userId = $_SESSION['user_id'];
    $quantity = 1;
    
    // Check if product already in cart
    $checkCartQuery = "SELECT * FROM cart WHERE user_id = $userId AND product_id = $productId";
    $checkCartResult = mysqli_query($conn, $checkCartQuery);
    
    if (mysqli_num_rows($checkCartResult) > 0) {
        // Update quantity
        $cartItem = mysqli_fetch_assoc($checkCartResult);
        $newQuantity = $cartItem['quantity'] + 1;
        mysqli_query($conn, "UPDATE cart SET quantity = $newQuantity WHERE id = {$cartItem['id']}");
    } else {
        // Add new item to cart
        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ($userId, $productId, $quantity)");
    }
    
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
                    <li><a href="shop.php" class="active">Shop</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
                
                <div class="nav-icons">
                    <div class="nav-icon">
                        <a href="#" class="cart-icon" id="cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <?php
                            if ($loggedIn) {
                                $userId = $_SESSION['user_id'];
                                $cartCountQuery = mysqli_query($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id = $userId");
                                $cartCount = mysqli_fetch_assoc($cartCountQuery)['total'];
                                echo "<span class='count'>$cartCount</span>";
                            } else {
                                echo "<span class='count'>0</span>";
                            }
                            ?>
                        </a>
                    </div>
                    
                    <div class="nav-icon">
                        <?php if ($loggedIn): ?>
                            <a href="account.php"><i class="fas fa-user"></i></a>
                        <?php else: ?>
                            <a href="index.php"><i class="fas fa-sign-in-alt"></i></a>
                        <?php endif; ?>
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
                                <a href="shop.php?category=<?php echo $category['name']; ?>" 
                                   class="<?php echo $categoryFilter == $category['name'] ? 'active' : ''; ?>">
                                    <?php echo $category['name']; ?>
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
                                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                    <?php if ($product['featured']): ?>
                                        <div class="product-tag featured-tag">Featured</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-details">
                                    <h3 class="product-title"><?php echo $product['name']; ?></h3>
                                    <div class="product-price">
                                        <span class="current-price">$<?php echo $product['price']; ?></span>
                                    </div>
                                    <div class="product-actions">
                                        <form method="post" action="">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="add_to_cart" class="add-to-cart">
                                                <i class="fas fa-shopping-cart"></i> Add to Cart
                                            </button>
                                        </form>
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
        // Toggle Mobile Navigation
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');
        
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
        
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