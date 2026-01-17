<?php
session_start();
include("connect.php");

// 1. Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// 2. STRICT SECURITY CHECK: Check if user is an ADMIN
// If the session role is NOT set or is NOT 'admin', redirect them immediately.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

// User is confirmed as Admin
$email = $_SESSION['email'];
$userFirstName = $_SESSION['firstName'];
$userLastName = $_SESSION['lastName'];

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
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Allow certain file formats
        if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif" ) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $target_file;
            }
        }
    } else if (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
        $image = $_POST['image_url'];
    }
    
    $insertQuery = "INSERT INTO products (name, description, price, category, stock, featured, image) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssdsiis", $name, $description, $price, $category, $stock, $featured, $image);
    
    if($stmt->execute()) {
        $message = "Product added successfully!";
    } else {
        $error = "Error adding product: " . $conn->error;
    }
}

// Delete product
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $deleteQuery = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $message = "Product deleted successfully!";
    } else {
        $error = "Error deleting product: " . $conn->error;
    }
}

// Add new category
if(isset($_POST['add_category'])) {
    $catName = $_POST['cat_name'];
    $catDesc = $_POST['cat_desc'];
    
    $insertCat = "INSERT INTO categories (name, description) VALUES (?, ?)";
    $stmt = $conn->prepare($insertCat);
    $stmt->bind_param("ss", $catName, $catDesc);
    
    if($stmt->execute()) {
        $message = "Category added successfully!";
        $activeTab = 'categories';
    } else {
        $error = "Error adding category: " . $conn->error;
        $activeTab = 'categories';
    }
}

// Update Order Status
if(isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];
    
    $updateOrder = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateOrder);
    $stmt->bind_param("si", $status, $orderId);
    
    if($stmt->execute()) {
        $message = "Order status updated!";
        $activeTab = 'orders';
    } else {
        $error = "Error updating order: " . $conn->error;
        $activeTab = 'orders';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Management - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f50057;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 4px 24px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            display: flex;
        }
        
        .sidebar {
            width: 260px;
            background: var(--white);
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            padding: 20px 0;
            z-index: 100;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        
        .logo h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .menu-items {
            list-style: none;
        }
        
        .menu-item {
            padding: 15px 30px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #666;
            text-decoration: none;
        }
        
        .menu-item i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(108, 99, 255, 0.1);
            color: var(--primary-color);
            border-right: 3px solid var(--primary-color);
        }
        
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        textarea.form-control {
            height: 120px;
            resize: vertical;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            font-weight: 600;
            color: #666;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-right: 5px;
            color: white;
            text-decoration: none;
        }
        
        .edit-btn { background: #4caf50; }
        .delete-btn { background: #f44336; }
        .view-btn { background: #2196f3; }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        
        .order-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2>E-Shop Admin</h2>
        </div>
        <ul class="menu-items">
            <li class="menu-item <?php echo $activeTab=='dashboard'?'active':''; ?>" onclick="showTab('dashboard')">
                <i class="fas fa-home"></i> Dashboard
            </li>
            <li class="menu-item <?php echo $activeTab=='products'?'active':''; ?>" onclick="showTab('products')">
                <i class="fas fa-box"></i> Products
            </li>
            <li class="menu-item <?php echo $activeTab=='categories'?'active':''; ?>" onclick="showTab('categories')">
                <i class="fas fa-tags"></i> Categories
            </li>
            <li class="menu-item <?php echo $activeTab=='orders'?'active':''; ?>" onclick="showTab('orders')">
                <i class="fas fa-shopping-cart"></i> Orders
            </li>
            <a href="admin.php" class="menu-item">
                <i class="fas fa-briefcase"></i> Portfolio Admin
            </a>
            <a href="homepage.php" class="menu-item">
                <i class="fas fa-arrow-left"></i> Back to Shop
            </a>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>Shop Management</h2>
            <div class="user-profile">
                <div class="avatar">
                    <?php echo substr($userFirstName, 0, 1); ?>
                </div>
                <div>
                    <h4><?php echo $userFirstName . ' ' . $userLastName; ?></h4>
                    <small>Administrator</small>
                </div>
            </div>
        </div>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div id="dashboard" class="tab-content <?php echo $activeTab=='dashboard'?'active':''; ?>">
            <div class="card">
                <h3>Overview</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                    <?php
                    $prodCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products"))['c'];
                    $catCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM categories"))['c'];
                    $orderCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'];
                    $userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='user'"))['c'];
                    ?>
                    <div style="background: #e3f2fd; padding: 20px; border-radius: 8px;">
                        <h3><?php echo $prodCount; ?></h3>
                        <p>Products</p>
                    </div>
                    <div style="background: #e8f5e9; padding: 20px; border-radius: 8px;">
                        <h3><?php echo $orderCount; ?></h3>
                        <p>Orders</p>
                    </div>
                    <div style="background: #fff3e0; padding: 20px; border-radius: 8px;">
                        <h3><?php echo $userCount; ?></h3>
                        <p>Customers</p>
                    </div>
                    <div style="background: #f3e5f5; padding: 20px; border-radius: 8px;">
                        <h3><?php echo $catCount; ?></h3>
                        <p>Categories</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="products" class="tab-content <?php echo $activeTab=='products'?'active':''; ?>">
            <div class="card">
                <div class="card-header">
                    <h3>All Products</h3>
                    <button onclick="document.getElementById('addProductForm').style.display='block'" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
                
                <div id="addProductForm" style="display: none; margin-bottom: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                    <h4>Add New Product</h4>
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div style="display: flex; gap: 20px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Price</label>
                                <input type="number" step="0.01" name="price" class="form-control" required>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Stock</label>
                                <input type="number" name="stock" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="form-control" required>
                                <?php
                                $cats = mysqli_query($conn, "SELECT * FROM categories");
                                while($c = mysqli_fetch_assoc($cats)) {
                                    echo "<option value='".$c['name']."'>".$c['name']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Image</label>
                            <input type="file" name="image" class="form-control">
                            <small>Or provide URL:</small>
                            <input type="text" name="image_url" class="form-control" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="featured"> Featured Product
                            </label>
                        </div>
                        <button type="submit" name="add_product" class="btn btn-primary">Save Product</button>
                        <button type="button" onclick="document.getElementById('addProductForm').style.display='none'" class="btn" style="background: #ddd;">Cancel</button>
                    </form>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
                        while($p = mysqli_fetch_assoc($products)):
                        ?>
                        <tr>
                            <td><img src="<?php echo $p['image']; ?>" class="product-img" alt="img"></td>
                            <td><?php echo $p['name']; ?></td>
                            <td><?php echo $p['category']; ?></td>
                            <td>$<?php echo $p['price']; ?></td>
                            <td><?php echo $p['stock']; ?></td>
                            <td>
                                <a href="?delete=<?php echo $p['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this product?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="categories" class="tab-content <?php echo $activeTab=='categories'?'active':''; ?>">
            <div class="card">
                <h3>Manage Categories</h3>
                <form method="post" style="margin-bottom: 30px; background: #f9f9f9; padding: 20px; border-radius: 8px;">
                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <input type="text" name="cat_name" placeholder="Category Name" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 2;">
                            <input type="text" name="cat_desc" placeholder="Description" class="form-control">
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary">Add</button>
                    </div>
                </form>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $categories = mysqli_query($conn, "SELECT * FROM categories");
                        while($c = mysqli_fetch_assoc($categories)):
                        ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td><?php echo $c['name']; ?></td>
                            <td><?php echo $c['description']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="orders" class="tab-content <?php echo $activeTab=='orders'?'active':''; ?>">
            <div class="card">
                <h3>Customer Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $orders = mysqli_query($conn, "SELECT o.*, u.firstName, u.lastName FROM orders o JOIN users u ON o.user_id = u.id ORDER BY created_at DESC");
                        foreach($orders as $order):
                        ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo $order['firstName'] . ' ' . $order['lastName']; ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="order-status status-<?php echo $order['status']; ?>" style="border:none; cursor:pointer;">
                                            <option value="pending" <?php echo $order['status']=='pending'?'selected':''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status']=='processing'?'selected':''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status']=='shipped'?'selected':''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status']=='delivered'?'selected':''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
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
            
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.classList.remove('active');
            });

            document.getElementById(tabId).classList.add('active');
            
            // Highlight menu item
            event.currentTarget.classList.add('active');
        }
        
        // Initialize based on URL parameter or default
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || '<?php echo $activeTab; ?>';
        if(document.getElementById(tab)) {
            document.getElementById(tab).classList.add('active');
        }
    </script>
</body>
</html>