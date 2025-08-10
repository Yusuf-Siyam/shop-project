<?php
session_start();
include("connect.php");

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Fetch order details
$orderQuery = "SELECT o.*, u.firstName, u.lastName, u.email 
               FROM orders o 
               JOIN users u ON o.user_id = u.id 
               WHERE o.id = $orderId AND o.user_id = $userId";
$orderResult = mysqli_query($conn, $orderQuery);

if(!$orderResult || mysqli_num_rows($orderResult) == 0) {
    header("Location: shop.php");
    exit();
}

$order = mysqli_fetch_assoc($orderResult);

// Fetch order items
$itemsQuery = "SELECT oi.*, p.name, p.image 
               FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = $orderId";
$itemsResult = mysqli_query($conn, $itemsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - E-Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f50057;
            --success-color: #4CAF50;
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
            padding: 2rem 20px;
        }

        .confirmation-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .confirmation-header i {
            font-size: 4rem;
            color: var(--success-color);
            margin-bottom: 1rem;
        }

        .confirmation-header h1 {
            font-size: 2.5rem;
            color: var(--success-color);
            margin-bottom: 1rem;
        }

        .confirmation-header p {
            font-size: 1.1rem;
            color: #666;
        }

        .order-details {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .info-group h3 {
            font-size: 1.1rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .info-group p {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .order-items {
            margin-bottom: 2rem;
        }

        .order-items h2 {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 1.5rem;
        }

        .item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .item-img {
            width: 80px;
            height: 80px;
            margin-right: 1.5rem;
        }

        .item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .item-price {
            color: var(--primary-color);
            font-weight: 600;
        }

        .item-quantity {
            color: #666;
        }

        .order-summary {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .summary-row.total {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            border-top: 1px solid #ddd;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: #5a52d5;
        }

        .btn-secondary {
            background: var(--white);
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--primary-color);
            color: var(--white);
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
        }

        .status-shipped {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .shipping-info {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .shipping-info h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .shipping-info p {
            margin-bottom: 0.5rem;
            color: #666;
        }

        .tracking-info {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
            border: 1px solid #eee;
        }

        .tracking-info h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .tracking-steps {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            position: relative;
        }

        .tracking-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #eee;
            z-index: 1;
        }

        .tracking-step {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            background: var(--white);
            border: 2px solid #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            color: #666;
        }

        .step-icon.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white);
        }

        .step-label {
            font-size: 0.9rem;
            color: #666;
        }

        .step-label.active {
            color: var(--primary-color);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .order-info {
                grid-template-columns: 1fr;
            }

            .item {
                flex-direction: column;
                text-align: center;
            }

            .item-img {
                margin: 0 0 1rem 0;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            .tracking-steps {
                flex-direction: column;
                gap: 1.5rem;
            }

            .tracking-steps::before {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-header">
            <i class="fas fa-check-circle"></i>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been received.</p>
        </div>

        <div class="order-details">
            <div class="order-info">
                <div class="info-group">
                    <h3>Order Information</h3>
                    <p>Order Number: #<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></p>
                    <p>Date: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                    <p>Status: 
                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </p>
                </div>

                <div class="info-group">
                    <h3>Customer Information</h3>
                    <p>Name: <?php echo htmlspecialchars($order['firstName'] . ' ' . $order['lastName']); ?></p>
                    <p>Email: <?php echo htmlspecialchars($order['email']); ?></p>
                </div>
            </div>

            <div class="order-items">
                <h2>Order Items</h2>
                <?php while($item = mysqli_fetch_assoc($itemsResult)): ?>
                    <div class="item">
                        <div class="item-img">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                            <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <div class="shipping-info">
                <h3>Shipping Information</h3>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                <p><strong>City:</strong> <?php echo htmlspecialchars($order['shipping_city']); ?></p>
                <p><strong>Postal Code:</strong> <?php echo htmlspecialchars($order['shipping_postal_code']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
            </div>

            <div class="tracking-info">
                <h3>Order Tracking</h3>
                <div class="tracking-steps">
                    <div class="tracking-step">
                        <div class="step-icon active">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="step-label active">Order Placed</div>
                    </div>
                    <div class="tracking-step">
                        <div class="step-icon <?php echo in_array($order['status'], ['processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="step-label <?php echo in_array($order['status'], ['processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">Processing</div>
                    </div>
                    <div class="tracking-step">
                        <div class="step-icon <?php echo in_array($order['status'], ['shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="step-label <?php echo in_array($order['status'], ['shipped', 'delivered']) ? 'active' : ''; ?>">Shipped</div>
                    </div>
                    <div class="tracking-step">
                        <div class="step-icon <?php echo $order['status'] == 'delivered' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="step-label <?php echo $order['status'] == 'delivered' ? 'active' : ''; ?>">Delivered</div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="shop.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Continue Shopping
                </a>
                <a href="view-order.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> View All Orders
                </a>
            </div>
        </div>
    </div>
</body>
</html> 