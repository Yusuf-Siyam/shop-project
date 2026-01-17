<?php
// Navigation Component - Include this file in all pages for consistent navigation

// FIX 1: Only start session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FIX 2: Ensure database connection exists for the cart counter
if (!isset($conn)) {
    include_once 'connect.php';
}

// Get current page name for active navigation
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Function to get page title
function getPageTitle($page) {
    $titles = [
        'index' => 'Login & Registration',
        'homepage' => 'Dashboard',
        'shop' => 'Shop',
        'checkout' => 'Cart & Checkout',
        'order-confirmation' => 'Order Confirmation',
        'view-order' => 'Order Details',
        'admin-products' => 'Product Management',
        'admin' => 'Admin Panel',
        'about-us' => 'About Us',
        'contact' => 'Contact Us',
        'register' => 'Registration'
    ];
    return isset($titles[$page]) ? $titles[$page] : 'E-Shop';
}

// Function to get breadcrumb trail
function getBreadcrumbTrail($page) {
    $breadcrumbs = [
        'index' => ['Home' => 'index.php'],
        'homepage' => ['Home' => 'homepage.php'],
        'shop' => ['Home' => 'homepage.php', 'Shop' => 'shop.php'],
        'checkout' => ['Home' => 'homepage.php', 'Shop' => 'shop.php', 'Cart' => 'checkout.php'],
        'order-confirmation' => ['Home' => 'homepage.php', 'Orders' => 'view-order.php', 'Confirmation' => 'order-confirmation.php'],
        'view-order' => ['Home' => 'homepage.php', 'Orders' => 'view-order.php'],
        'admin-products' => ['Home' => 'homepage.php', 'Admin' => 'admin.php', 'Products' => 'admin-products.php'],
        'admin' => ['Home' => 'homepage.php', 'Admin' => 'admin.php'],
        'about-us' => ['Home' => 'homepage.php', 'About' => 'about-us.php'],
        'contact' => ['Home' => 'homepage.php', 'Contact' => 'contact.php'],
        'register' => ['Home' => 'index.php', 'Register' => 'register.php']
    ];
    return isset($breadcrumbs[$page]) ? $breadcrumbs[$page] : ['Home' => 'homepage.php'];
}

// Function to get previous page for back button
function getPreviousPage($page) {
    $previous_pages = [
        'homepage' => 'index.php',
        'shop' => 'homepage.php',
        'checkout' => 'shop.php',
        'order-confirmation' => 'checkout.php',
        'view-order' => 'homepage.php',
        'admin-products' => 'admin.php',
        'admin' => 'homepage.php',
        'about-us' => 'homepage.php',
        'contact' => 'homepage.php',
        'register' => 'index.php'
    ];
    return isset($previous_pages[$page]) ? $previous_pages[$page] : 'homepage.php';
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['email']);
$userFirstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : '';
$userLastName = isset($_SESSION['lastName']) ? $_SESSION['lastName'] : '';
?>

<nav class="smart-nav">
    <div class="nav-container">
        <div class="nav-left">
            <?php if($current_page !== 'index' && $current_page !== 'homepage'): ?>
                <a href="<?php echo getPreviousPage($current_page); ?>" class="back-btn" title="Go Back">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </a>
            <?php endif; ?>
            
            <a href="homepage.php" class="logo">
                <i class="fas fa-shopping-bag"></i>
                <span class="logo-text">
                    <span class="logo-primary">E</span><span class="logo-secondary">-Shop</span>
                </span>
            </a>
        </div>

        <div class="nav-center">
            <div class="breadcrumbs">
                <?php 
                $breadcrumbTrail = getBreadcrumbTrail($current_page);
                $lastKey = array_key_last($breadcrumbTrail);
                foreach($breadcrumbTrail as $label => $link): 
                    if($label === $lastKey): ?>
                        <span class="breadcrumb-current"><?php echo $label; ?></span>
                    <?php else: ?>
                        <a href="<?php echo $link; ?>" class="breadcrumb-link"><?php echo $label; ?></a>
                        <i class="fas fa-chevron-right breadcrumb-separator"></i>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="nav-right">
            <?php if($isLoggedIn): ?>
                <div class="nav-links">
                    <a href="shop.php" class="nav-link <?php echo $current_page === 'shop' ? 'active' : ''; ?>">
                        <i class="fas fa-store"></i>
                        <span>Shop</span>
                    </a>
                    <a href="checkout.php" class="nav-link <?php echo $current_page === 'checkout' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <?php
                        // Show cart count if available
                        if(isset($_SESSION['user_id']) && isset($conn)) {
                            $cartCountQuery = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
                            if ($cartCountQuery) {
                                $cartCountQuery->bind_param("i", $_SESSION['user_id']);
                                $cartCountQuery->execute();
                                $cartCountResult = $cartCountQuery->get_result();
                                $cartCount = $cartCountResult->fetch_assoc()['total'];
                                $cartCountQuery->close();
                                if($cartCount > 0) {
                                    echo "<span class='cart-count'>$cartCount</span>";
                                }
                            }
                        }
                        ?>
                    </a>
                    <a href="view-order.php" class="nav-link <?php echo $current_page === 'view-order' ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i>
                        <span>Orders</span>
                    </a>
                </div>

                <div class="user-menu">
                    <div class="user-avatar" onclick="toggleUserMenu()">
                        <i class="fas fa-user-circle"></i>
                        <span class="user-name"><?php echo htmlspecialchars($userFirstName); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <div class="user-info">
                            <i class="fas fa-user-circle"></i>
                            <div>
                                <div class="user-fullname"><?php echo htmlspecialchars($userFirstName . ' ' . $userLastName); ?></div>
                                <div class="user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="homepage.php" class="dropdown-item">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="shop.php" class="dropdown-item">
                            <i class="fas fa-store"></i>
                            <span>Shop</span>
                        </a>
                        <a href="view-order.php" class="dropdown-item">
                            <i class="fas fa-list"></i>
                            <span>My Orders</span>
                        </a>
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): // Admin check ?>
                        <a href="admin-products.php" class="dropdown-item">
                            <i class="fas fa-boxes"></i>
                            <span>Product Management</span>
                        </a>
                        <a href="admin.php" class="dropdown-item">
                            <i class="fas fa-user-shield"></i>
                            <span>Admin Panel</span>
                        </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="nav-links">
                    <a href="about-us.php" class="nav-link <?php echo $current_page === 'about-us' ? 'active' : ''; ?>">
                        <i class="fas fa-info-circle"></i>
                        <span>About</span>
                    </a>
                    <a href="contact.php" class="nav-link <?php echo $current_page === 'contact' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                </div>
                <div class="auth-buttons">
                    <a href="index.php" class="btn btn-outline">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </div>
    </div>

    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <h3><?php echo getPageTitle($current_page); ?></h3>
            <button class="close-mobile-menu" onclick="toggleMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mobile-nav-content">
            <?php if($isLoggedIn): ?>
                <div class="mobile-user-info">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <div class="mobile-user-name"><?php echo htmlspecialchars($userFirstName . ' ' . $userLastName); ?></div>
                        <div class="mobile-user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                    </div>
                </div>
                <div class="mobile-nav-links">
                    <a href="homepage.php" class="mobile-nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="shop.php" class="mobile-nav-link">
                        <i class="fas fa-store"></i>
                        <span>Shop</span>
                    </a>
                    <a href="checkout.php" class="mobile-nav-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                    </a>
                    <a href="view-order.php" class="mobile-nav-link">
                        <i class="fas fa-list"></i>
                        <span>Orders</span>
                    </a>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin-products.php" class="mobile-nav-link">
                        <i class="fas fa-boxes"></i>
                        <span>Product Management</span>
                    </a>
                    <a href="admin.php" class="mobile-nav-link">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin Panel</span>
                    </a>
                    <?php endif; ?>
                    <a href="logout.php" class="mobile-nav-link logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="mobile-nav-links">
                    <a href="index.php" class="mobile-nav-link">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                    <a href="register.php" class="mobile-nav-link">
                        <i class="fas fa-user-plus"></i>
                        <span>Register</span>
                    </a>
                    <a href="about-us.php" class="mobile-nav-link">
                        <i class="fas fa-info-circle"></i>
                        <span>About</span>
                    </a>
                    <a href="contact.php" class="mobile-nav-link">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
:root {
    --nav-bg: #ffffff;
    --nav-shadow: 0 2px 20px rgba(0,0,0,0.08);
    --nav-border: #e1e8ed;
    --primary-color: #6c63ff;
    --secondary-color: #f50057;
    --text-dark: #2c3e50;
    --text-light: #7f8c8d;
    --hover-bg: #f8f9fa;
    --active-bg: rgba(108,99,255,0.1);
    --active-color: #6c63ff;
}

.smart-nav {
    background: var(--nav-bg);
    box-shadow: var(--nav-shadow);
    border-bottom: 1px solid var(--nav-border);
    position: sticky;
    top: 0;
    z-index: 1000;
    transition: all 0.3s ease;
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 70px;
}

/* Left Section */
.nav-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.back-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--hover-bg);
    color: var(--text-dark);
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: var(--active-bg);
    color: var(--active-color);
    transform: translateX(-2px);
}

.back-btn i {
    font-size: 14px;
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    font-size: 1.8rem;
    font-weight: bold;
    letter-spacing: 1px;
}

.logo i {
    font-size: 2rem;
    color: var(--primary-color);
}

.logo-text {
    display: flex;
}

.logo-primary {
    color: var(--primary-color);
}

.logo-secondary {
    color: var(--secondary-color);
}

/* Center Section */
.nav-center {
    display: none;
}

@media (min-width: 1024px) {
    .nav-center {
        display: block;
    }
}

.breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.breadcrumb-link {
    color: var(--text-light);
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb-link:hover {
    color: var(--primary-color);
}

.breadcrumb-current {
    color: var(--text-dark);
    font-weight: 500;
}

.breadcrumb-separator {
    font-size: 10px;
    color: var(--text-light);
}

/* Right Section */
.nav-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.nav-links {
    display: none;
}

@media (min-width: 768px) {
    .nav-links {
        display: flex;
        align-items: center;
        gap: 20px;
    }
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    color: var(--text-dark);
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
}

.nav-link:hover {
    background: var(--hover-bg);
    color: var(--primary-color);
}

.nav-link.active {
    background: var(--active-bg);
    color: var(--active-color);
}

.cart-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--secondary-color);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: bold;
}

/* User Menu */
.user-menu {
    position: relative;
}

.user-avatar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--hover-bg);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.user-avatar:hover {
    background: var(--active-bg);
}

.user-avatar i:first-child {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.user-name {
    font-weight: 500;
    color: var(--text-dark);
}

.user-avatar i:last-child {
    font-size: 12px;
    color: var(--text-light);
    transition: transform 0.3s ease;
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    min-width: 250px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
}

.user-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px;
    border-bottom: 1px solid var(--nav-border);
}

.user-info i {
    font-size: 2rem;
    color: var(--primary-color);
}

.user-fullname {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 4px;
}

.user-email {
    font-size: 0.9rem;
    color: var(--text-light);
}

.dropdown-divider {
    height: 1px;
    background: var(--nav-border);
    margin: 8px 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: var(--text-dark);
    text-decoration: none;
    transition: background 0.3s ease;
}

.dropdown-item:hover {
    background: var(--hover-bg);
}

.dropdown-item.logout {
    color: var(--secondary-color);
}

.dropdown-item.logout:hover {
    background: rgba(245,0,87,0.1);
}

/* Auth Buttons */
.auth-buttons {
    display: flex;
    gap: 12px;
}

.btn {
    padding: 8px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-outline {
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.btn-outline:hover {
    background: var(--primary-color);
    color: white;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: #5a52d5;
    transform: translateY(-2px);
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: var(--hover-bg);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.mobile-menu-toggle:hover {
    background: var(--active-bg);
    color: var(--active-color);
}

@media (min-width: 768px) {
    .mobile-menu-toggle {
        display: none;
    }
}

/* Mobile Navigation */
.mobile-nav {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background: white;
    z-index: 2000;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    overflow-y: auto;
}

.mobile-nav.show {
    transform: translateX(0);
}

.mobile-nav-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    border-bottom: 1px solid var(--nav-border);
    background: var(--hover-bg);
}

.mobile-nav-header h3 {
    color: var(--text-dark);
    font-size: 1.2rem;
}

.close-mobile-menu {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--text-light);
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.close-mobile-menu:hover {
    background: var(--active-bg);
    color: var(--active-color);
}

.mobile-nav-content {
    padding: 20px;
}

.mobile-user-info {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: var(--hover-bg);
    border-radius: 12px;
    margin-bottom: 20px;
}

.mobile-user-info i {
    font-size: 2.5rem;
    color: var(--primary-color);
}

.mobile-user-name {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 4px;
}

.mobile-user-email {
    font-size: 0.9rem;
    color: var(--text-light);
}

.mobile-nav-links {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    color: var(--text-dark);
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.mobile-nav-link:hover {
    background: var(--hover-bg);
    color: var(--primary-color);
}

.mobile-nav-link.logout {
    color: var(--secondary-color);
}

.mobile-nav-link.logout:hover {
    background: rgba(245,0,87,0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    .nav-container {
        height: 60px;
        padding: 0 15px;
    }
    
    .nav-left {
        gap: 15px;
    }
    
    .logo {
        font-size: 1.5rem;
    }
    
    .logo i {
        font-size: 1.5rem;
    }
    
    .back-btn span {
        display: none;
    }
    
    .user-avatar .user-name {
        display: none;
    }
    
    .nav-right {
        gap: 15px;
    }
}

/* Animation for dropdown arrow */
.user-avatar.active i:last-child {
    transform: rotate(180deg);
}
</style>

<script>
// Toggle user dropdown
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    const avatar = document.querySelector('.user-avatar');
    
    dropdown.classList.toggle('show');
    avatar.classList.toggle('active');
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!avatar.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
            avatar.classList.remove('active');
            document.removeEventListener('click', closeDropdown);
        }
    });
}

// Toggle mobile menu
function toggleMobileMenu() {
    const mobileNav = document.getElementById('mobileNav');
    mobileNav.classList.toggle('show');
    
    // Prevent body scroll when mobile menu is open
    if (mobileNav.classList.contains('show')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
}

// Close mobile menu when clicking on a link
document.addEventListener('DOMContentLoaded', function() {
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', function() {
            toggleMobileMenu();
        });
    });
});

// Add scroll effect to navigation
window.addEventListener('scroll', function() {
    const nav = document.querySelector('.smart-nav');
    if (window.scrollY > 50) {
        nav.style.background = 'rgba(255,255,255,0.95)';
        nav.style.backdropFilter = 'blur(10px)';
    } else {
        nav.style.background = 'var(--nav-bg)';
        nav.style.backdropFilter = 'none';
    }
});
</script>