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
    $isAdmin = true; // For simplicity, all logged in users can access admin
}

if(!$isAdmin) {
    header("Location: homepage.php");
    exit();
}

// Check if order ID is provided
if(!isset($_GET['id'])) {
    header("Location: admin-products.php?tab=orders");
    exit();
}

$orderId = $_GET['id'];

// Get order details
$orderQuery = "SELECT o.*, u.firstName, u.lastName, u.email 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = $orderId";
$orderResult = mysqli_query($conn, $orderQuery);

if(mysqli_num_rows($orderResult) == 0) {
    header("Location: admin-products.php?tab=orders");
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

// Handle status update
$message = "";
$error = "";

if(isset($_POST['update_status'])) {
    $newStatus = mysqli_real_escape_string($conn, $_POST['status']);
    $updateQuery = "UPDATE orders SET status = '$newStatus' WHERE id = $orderId";
    
    if(mysqli_query($conn, $updateQuery)) {
        $message = "Order status updated successfully!";
        $order['status'] = $newStatus; // Update current view
    } else {
        $error = "Error updating order status: " . mysqli_error($conn);
    }
}

// Format order date
$orderDate = date("F j, Y, g:i a", strtotime($order['created_at']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Shop - View Order</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="e-shop.css">
    <style>
        .order-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .order-id {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .order-date {
            color: #666;
        }
        
        .customer-details, .shipping-details {
            margin-bottom: 20px;
        }
        
        .details-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
            font-size: 1.1rem;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .details-item {
            margin-bottom: 10px;
        }
        
        .item-label {
            display: block;
            font-weight: 500;
            color: #666;
            margin-bottom: 3px;
        }
        
        .item-value {
            font-weight: 400;
        }
        
        .order-items {
            margin-bottom: 20px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .items-table th {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
            color: #666;
        }
        
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--radius);
        }
        
        .order-totals {
            width: 300px;
            margin-left: auto;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-label {
            font-weight: 500;
            color: #666;
        }
        
        .grand-total {
            border-top: 1px solid var(--border-color);
            padding-top: 10px;
            margin-top: 10px;
            font-weight: 600;
        }
        
        .status-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .status-form h3 {
            margin-bottom: 10px;
        }
        
        .status-form select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            margin-right: 10px;
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
        
        .back-btn {
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container" style="padding: 20px 0;">
        <a href="admin-products.php?tab=orders" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        
        <h1>Order Details</h1>
        
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
        
        <div class="order-container">
            <div class="order-header">
                <div>
                    <span class="order-id">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                    <span class="order-date"><?php echo $orderDate; ?></span>
                </div>
                <div class="order-status status-<?php echo $order['status']; ?>">
                    <?php echo ucfirst($order['status']); ?>
                </div>
            </div>
            
            <div class="customer-details">
                <h3 class="details-title">Customer Information</h3>
                <div class="details-grid">
                    <div class="details-item">
                        <span class="item-label">Name</span>
                        <span class="item-value"><?php echo $order['firstName'] . ' ' . $order['lastName']; ?></span>
                    </div>
                    <div class="details-item">
                        <span class="item-label">Email</span>
                        <span class="item-value"><?php echo $order['email']; ?></span>
                    </div>
                    <div class="details-item">
                        <span class="item-label">Phone</span>
                        <span class="item-value"><?php echo $order['shipping_phone']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="shipping-details">
                <h3 class="details-title">Shipping Information</h3>
                <div class="details-grid">
                    <div class="details-item">
                        <span class="item-label">Address</span>
                        <span class="item-value"><?php echo $order['shipping_address']; ?></span>
                    </div>
                    <div class="details-item">
                        <span class="item-label">City</span>
                        <span class="item-value"><?php echo $order['shipping_city']; ?></span>
                    </div>
                    <div class="details-item">
                        <span class="item-label">Postal Code</span>
                        <span class="item-value"><?php echo $order['shipping_postal_code']; ?></span>
                    </div>
                    <div class="details-item">
                        <span class="item-label">Payment Method</span>
                        <span class="item-value"><?php echo $order['payment_method']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="order-items">
                <h3 class="details-title">Order Items</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th width="70">Image</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="item-image">
                                </td>
                                <td><?php echo $item['name']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="order-totals">
                <div class="total-row">
                    <span class="total-label">Subtotal</span>
                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="total-row">
                    <span class="total-label">Shipping</span>
                    <span>Free</span>
                </div>
                <div class="total-row grand-total">
                    <span class="total-label">Total</span>
                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
            
            <?php if($order['notes']): ?>
                <div class="order-notes">
                    <h3 class="details-title">Order Notes</h3>
                    <p><?php echo $order['notes']; ?></p>
                </div>
            <?php endif; ?>
            
            <div class="status-form">
                <h3>Update Order Status</h3>
                <form method="post" action="">
                    <select name="status">
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="btn primary-btn">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 