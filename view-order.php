<?php
session_start();
include("connect.php");

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch all orders for the user
$ordersQuery = "SELECT o.*, 
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items
                FROM orders o 
                WHERE o.user_id = $userId 
                ORDER BY o.created_at DESC";
$ordersResult = mysqli_query($conn, $ordersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - E-Shop</title>
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
            padding: 2rem 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .orders-list {
            display: grid;
            gap: 1.5rem;
        }

        .order-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .order-header {
            padding: 1.5rem;
            background: var(--light-bg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .order-info {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.9rem;
            color: #666;
        }

        .info-value {
            font-weight: 500;
            color: var(--text-color);
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
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

        .order-summary {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            border-top: 1px solid #eee;
        }

        .order-total {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .order-items {
            font-size: 0.9rem;
            color: #666;
        }

        .view-order-btn {
            background: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .view-order-btn:hover {
            background: #5a52d5;
        }

        .no-orders {
            text-align: center;
            padding: 3rem;
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .no-orders i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .no-orders h3 {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .no-orders p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .shop-now-btn {
            display: inline-block;
            background: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .shop-now-btn:hover {
            background: #5a52d5;
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-info {
                flex-direction: column;
                gap: 1rem;
            }

            .order-summary {
                flex-direction: column;
                align-items: flex-start;
            }

            .view-order-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>My Orders</h1>
            <p>View and track your order history</p>
        </div>

        <div class="orders-list">
            <?php if(mysqli_num_rows($ordersResult) > 0): ?>
                <?php while($order = mysqli_fetch_assoc($ordersResult)): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <div class="info-item">
                                    <span class="info-label">Order Number</span>
                                    <span class="order-number">#<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Date</span>
                                    <span class="info-value"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Items</span>
                                    <span class="info-value"><?php echo $order['total_items']; ?> items</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Status</span>
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="order-summary">
                            <div class="order-total">
                                Total: $<?php echo number_format($order['total_amount'], 2); ?>
                            </div>
                            <a href="order-confirmation.php?order_id=<?php echo $order['id']; ?>" class="view-order-btn">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders yet. Start shopping to see your orders here.</p>
                    <a href="shop.php" class="shop-now-btn">
                        <i class="fas fa-shopping-cart"></i> Shop Now
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 