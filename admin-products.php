<?php
session_start();
include("connect.php");

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Check if user is admin (you may need to adapt this based on your user roles system)
$email = $_SESSION['email'];
$isAdmin = false;
$userFirstName = $userLastName = "";

$query = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'");
while($row = mysqli_fetch_array($query)) {
    $userFirstName = $row['firstName'];
    $userLastName = $row['lastName'];
    $isAdmin = true; // For simplicity, all logged in users can access admin (modify as needed)
}

if(!$isAdmin) {
    header("Location: homepage.php");
    exit();
}

// Set default tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'products';

// Handle product actions (add, edit, delete)
$message = "";
$error = "";

// Add new product
if(isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Image upload handling
    $image = "https://via.placeholder.com/600x400"; // Default image
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if file is an actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            // Generate unique filename
            $filename = uniqid() . "." . $imageFileType;
            $target_file = $target_dir . $filename;
            
            // Upload file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $target_file;
            }
        }
    }
    
    $insertProduct = "INSERT INTO products (name, description, price, image, category, stock, featured) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insertProduct);
    $stmt->bind_param("ssdssi", $name, $description, $price, $image, $category, $stock, $featured);
    
    if($stmt->execute()) {
        $message = "Product added successfully!";
    } else {
        $error = "Error adding product: " . $stmt->error;
    }
    $stmt->close();
}

// Update product
if(isset($_POST['update_product'])) {
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Get current product info using prepared statement
    $currentProductQuery = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $currentProductQuery->bind_param("i", $id);
    $currentProductQuery->execute();
    $currentProduct = $currentProductQuery->get_result()->fetch_assoc();
    $currentProductQuery->close();
    $image = $currentProduct['image'];
    
    // Check if new image was uploaded
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if file is an actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            // Generate unique filename
            $filename = uniqid() . "." . $imageFileType;
            $target_file = $target_dir . $filename;
            
            // Upload file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $target_file;
            }
        }
    }
    
    $updateProduct = "UPDATE products SET 
                     name = ?, 
                     description = ?, 
                     price = ?, 
                     image = ?, 
                     category = ?, 
                     stock = ?, 
                     featured = ?
                     WHERE id = ?";
    
    $updateStmt = $conn->prepare($updateProduct);
    $updateStmt->bind_param("ssdssii", $name, $description, $price, $image, $category, $stock, $featured, $id);
    
    if($updateStmt->execute()) {
        $message = "Product updated successfully!";
    } else {
        $error = "Error updating product: " . $updateStmt->error;
    }
    $updateStmt->close();
}

// Delete product
if(isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $deleteProduct = "DELETE FROM products WHERE id = $id";
    
    if(mysqli_query($conn, $deleteProduct)) {
        $message = "Product deleted successfully!";
    } else {
        $error = "Error deleting product: " . mysqli_error($conn);
    }
}

// Get product to edit
$editProduct = null;
if(isset($_GET['edit_product'])) {
    $id = $_GET['edit_product'];
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
    $editProduct = mysqli_fetch_assoc($result);
}

// Fetch all products
$products = [];
$productsResult = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC");
$productsResult->execute();
$productsResult = $productsResult->get_result();
if($productsResult) {
    while($row = $productsResult->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch all categories
$categories = [];
$categoriesResult = $conn->prepare("SELECT * FROM categories ORDER BY name ASC");
$categoriesResult->execute();
$categoriesResult = $categoriesResult->get_result();
if($categoriesResult) {
    while($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Add new category
if(isset($_POST['add_category'])) {
    $categoryName = $_POST['category_name'];
    $categoryDescription = $_POST['category_description'];
    
    $insertCategory = "INSERT INTO categories (name, description) VALUES (?, ?)";
    
    $stmt = $conn->prepare($insertCategory);
    $stmt->bind_param("ss", $categoryName, $categoryDescription);
    
    if($stmt->execute()) {
        $message = "Category added successfully!";
    } else {
        $error = "Error adding category: " . $stmt->error;
    }
    $stmt->close();
}

// Delete category
if(isset($_GET['delete_category'])) {
    $id = $_GET['delete_category'];
    $deleteCategory = "DELETE FROM categories WHERE id = ?";
    
    $stmt = $conn->prepare($deleteCategory);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $message = "Category deleted successfully!";
    } else {
        $error = "Error deleting category: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all orders
$orders = [];
$ordersQuery = "SELECT o.*, u.firstName, u.lastName, u.email 
               FROM orders o 
               JOIN users u ON o.user_id = u.id 
               ORDER BY o.created_at DESC";
$ordersResult = $conn->prepare($ordersQuery);
$ordersResult->execute();
$ordersResult = $ordersResult->get_result();
if($ordersResult) {
    while($row = $ordersResult->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Shop - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="e-shop.css">
    <style>
        /* Admin-specific styles */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: var(--primary-color);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.1);
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .product-grid, .category-grid, .order-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .admin-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .admin-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--radius);
            margin-bottom: 10px;
        }
        
        .admin-card h3 {
            margin: 10px 0;
        }
        
        .admin-card p {
            color: #666;
            margin-bottom: 10px;
        }
        
        .card-footer {
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
        }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: var(--radius);
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .edit-btn {
            background-color: var(--info-color);
        }
        
        .delete-btn {
            background-color: var(--danger-color);
        }
        
        .view-btn {
            background-color: var(--success-color);
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .badge-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .badge-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .badge-info {
            background-color: var(--info-color);
            color: white;
        }
        
        .order-status {
            font-weight: 500;
        }
        
        .status-pending {
            color: var(--warning-color);
        }
        
        .status-processing {
            color: var(--info-color);
        }
        
        .status-shipped {
            color: var(--primary-color);
        }
        
        .status-delivered {
            color: var(--success-color);
        }
        
        .status-cancelled {
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>E-Shop Admin</h2>
            </div>
            <div class="sidebar-menu">
                <a href="admin-products.php?tab=dashboard" class="menu-item <?php echo $activeTab == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="admin-products.php?tab=products" class="menu-item <?php echo $activeTab == 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="admin-products.php?tab=categories" class="menu-item <?php echo $activeTab == 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="admin-products.php?tab=orders" class="menu-item <?php echo $activeTab == 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="admin.php" class="menu-item">
                    <i class="fas fa-project-diagram"></i> Portfolio
                </a>
                <a href="homepage.php" class="menu-item">
                    <i class="fas fa-home"></i> Main Site
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $userFirstName . ' ' . $userLastName; ?></span>
                </div>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Dashboard Tab -->
            <div class="tab-content <?php echo $activeTab == 'dashboard' ? 'active' : ''; ?>" id="dashboard">
                <h2>Dashboard Overview</h2>
                <div class="dashboard-stats">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Total Products</h3>
                                <p><?php echo count($products); ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Categories</h3>
                                <p><?php echo count($categories); ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Total Orders</h3>
                                <p><?php echo count($orders); ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Revenue</h3>
                                <p>$<?php 
                                    $revenue = 0;
                                    foreach ($orders as $order) {
                                        $revenue += $order['total_amount'];
                                    }
                                    echo number_format($revenue, 2);
                                ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h3>Recent Orders</h3>
                <div class="recent-orders">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recentOrders = array_slice($orders, 0, 5);
                            foreach ($recentOrders as $order): 
                            ?>
                            <tr>
                                <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $order['firstName'] . ' ' . $order['lastName']; ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Products Tab -->
            <div class="tab-content <?php echo $activeTab == 'products' ? 'active' : ''; ?>" id="products">
                <h2><?php echo $editProduct ? 'Edit Product' : 'Add New Product'; ?></h2>
                
                <form action="" method="post" enctype="multipart/form-data">
                    <?php if($editProduct): ?>
                        <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" id="name" name="name" value="<?php echo $editProduct ? $editProduct['name'] : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price ($)</label>
                            <input type="number" id="price" name="price" step="0.01" value="<?php echo $editProduct ? $editProduct['price'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['name']; ?>" <?php echo ($editProduct && $editProduct['category'] == $category['name']) ? 'selected' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" id="stock" name="stock" value="<?php echo $editProduct ? $editProduct['stock'] : '1'; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required><?php echo $editProduct ? $editProduct['description'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <?php if($editProduct && $editProduct['image']): ?>
                            <div class="current-image">
                                <img src="<?php echo $editProduct['image']; ?>" alt="Current Image" style="max-width: 200px; margin-bottom: 10px;">
                                <p>Current image. Upload a new one to replace it.</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" accept="image/*" <?php echo $editProduct ? '' : 'required'; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="featured" <?php echo ($editProduct && $editProduct['featured']) ? 'checked' : ''; ?>>
                            Featured Product
                        </label>
                    </div>
                    
                    <button type="submit" name="<?php echo $editProduct ? 'update_product' : 'add_product'; ?>" class="btn primary-btn">
                        <?php echo $editProduct ? 'Update Product' : 'Add Product'; ?>
                    </button>
                    
                    <?php if($editProduct): ?>
                        <a href="admin-products.php?tab=products" class="btn secondary-btn">Cancel</a>
                    <?php endif; ?>
                </form>
                
                <h2>All Products</h2>
                <div class="product-grid">
                    <?php foreach($products as $product): ?>
                        <div class="admin-card">
                            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            <h3><?php echo $product['name']; ?></h3>
                            <p><?php echo substr($product['description'], 0, 100) . '...'; ?></p>
                            <div>
                                <p><strong>Price:</strong> $<?php echo $product['price']; ?></p>
                                <p><strong>Category:</strong> <?php echo $product['category']; ?></p>
                                <p><strong>Stock:</strong> <?php echo $product['stock']; ?></p>
                                <?php if($product['featured']): ?>
                                    <span class="badge badge-success">Featured</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <a href="admin-products.php?tab=products&edit_product=<?php echo $product['id']; ?>" class="action-btn edit-btn">Edit</a>
                                <a href="admin-products.php?tab=products&delete_product=<?php echo $product['id']; ?>" 
                                   class="action-btn delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Categories Tab -->
            <div class="tab-content <?php echo $activeTab == 'categories' ? 'active' : ''; ?>" id="categories">
                <h2>Add New Category</h2>
                
                <form action="" method="post">
                    <div class="form-group">
                        <label for="category_name">Category Name</label>
                        <input type="text" id="category_name" name="category_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_description">Description</label>
                        <textarea id="category_description" name="category_description" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" name="add_category" class="btn primary-btn">Add Category</button>
                </form>
                
                <h2>All Categories</h2>
                <div class="category-grid">
                    <?php foreach($categories as $category): ?>
                        <div class="admin-card">
                            <h3><?php echo $category['name']; ?></h3>
                            <p><?php echo $category['description']; ?></p>
                            <div class="card-footer">
                                <a href="admin-products.php?tab=categories&delete_category=<?php echo $category['id']; ?>" 
                                   class="action-btn delete-btn"
                                   onclick="return confirm('Are you sure you want to delete this category? Products in this category will not be deleted.')">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Orders Tab -->
            <div class="tab-content <?php echo $activeTab == 'orders' ? 'active' : ''; ?>" id="orders">
                <h2>All Orders</h2>
                
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $order['firstName'] . ' ' . $order['lastName']; ?></td>
                                <td><?php echo $order['email']; ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="view-order.php?id=<?php echo $order['id']; ?>" class="action-btn view-btn">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // Function to show active tab
        function showTab(tabId) {
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            
            const activeTab = document.getElementById(tabId);
            if (activeTab) {
                activeTab.classList.add('active');
            }
        }
        
        // Initialize based on URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'dashboard';
        showTab(tab);
    </script>
</body>
</html> 