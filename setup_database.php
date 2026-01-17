<?php
include("connect.php");

echo "<h2>Setting up Shop Project Database</h2>";

// Create users table
$createUsersTable = "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firstName` VARCHAR(50) NOT NULL,
    `lastName` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createUsersTable)) {
    echo "Error creating users table: " . $conn->error . "<br>";
} else {
    echo "✓ Users table created successfully<br>";
}

// Create categories table
$createCategoriesTable = "CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createCategoriesTable)) {
    echo "Error creating categories table: " . $conn->error . "<br>";
} else {
    echo "✓ Categories table created successfully<br>";
}

// Create products table
$createProductsTable = "CREATE TABLE IF NOT EXISTS `products` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `image` VARCHAR(255) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `featured` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category`) REFERENCES `categories`(`name`) ON DELETE CASCADE
)";

if (!$conn->query($createProductsTable)) {
    echo "Error creating products table: " . $conn->error . "<br>";
} else {
    echo "✓ Products table created successfully<br>";
}

// Create cart table
$createCartTable = "CREATE TABLE IF NOT EXISTS `cart` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
)";

if (!$conn->query($createCartTable)) {
    echo "Error creating cart table: " . $conn->error . "<br>";
} else {
    echo "✓ Cart table created successfully<br>";
}

// Create orders table
$createOrdersTable = "CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
    `shipping_address` TEXT NOT NULL,
    `shipping_city` VARCHAR(100) NOT NULL,
    `shipping_postal_code` VARCHAR(20) NOT NULL,
    `shipping_phone` VARCHAR(20) NOT NULL,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
)";

if (!$conn->query($createOrdersTable)) {
    echo "Error creating orders table: " . $conn->error . "<br>";
} else {
    echo "✓ Orders table created successfully<br>";
}

// Create order_items table
$createOrderItemsTable = "CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
)";

if (!$conn->query($createOrderItemsTable)) {
    echo "Error creating order_items table: " . $conn->error . "<br>";
} else {
    echo "✓ Order items table created successfully<br>";
}

// Insert default categories
$categories = [
    ['Electronics', 'Electronic devices and accessories'],
    ['Clothing', 'Fashion and apparel'],
    ['Books', 'Books and publications'],
    ['Home & Kitchen', 'Home appliances and kitchen items'],
    ['Beauty', 'Beauty and personal care products']
];

foreach ($categories as $category) {
    $name = $conn->real_escape_string($category[0]);
    $description = $conn->real_escape_string($category[1]);
    
    $insertCategory = "INSERT IGNORE INTO `categories` (`name`, `description`) VALUES ('$name', '$description')";
    if ($conn->query($insertCategory)) {
        echo "✓ Category '$name' added<br>";
    }
}

// Insert sample products
$products = [
    ['Smartphone X', 'Latest smartphone with advanced features', 699.99, 'smartphone.jpg', 'Electronics', 50, 1],
    ['Laptop Pro', 'High-performance laptop for professionals', 1299.99, 'laptop.jpg', 'Electronics', 30, 1],
    ['Wireless Earbuds', 'Premium wireless earbuds with noise cancellation', 149.99, 'earbuds.jpg', 'Electronics', 100, 0],
    ['Men\'s T-Shirt', 'Comfortable cotton t-shirt for everyday wear', 29.99, 'tshirt.jpg', 'Clothing', 200, 0],
    ['Women\'s Dress', 'Elegant summer dress', 59.99, 'dress.jpg', 'Clothing', 75, 1],
    ['Coffee Maker', 'Automatic coffee maker with timer', 89.99, 'coffee-maker.jpg', 'Home & Kitchen', 40, 0],
    ['Blender', 'High-speed blender for smoothies and more', 79.99, 'blender.jpg', 'Home & Kitchen', 60, 0],
    ['Fiction Novel', 'Bestselling fiction novel', 19.99, 'book.jpg', 'Books', 150, 0],
    ['Skincare Set', 'Complete skincare routine set', 49.99, 'skincare.jpg', 'Beauty', 80, 1],
    ['Perfume', 'Luxury fragrance for women', 89.99, 'perfume.jpg', 'Beauty', 45, 0]
];

foreach ($products as $product) {
    $name = $conn->real_escape_string($product[0]);
    $description = $conn->real_escape_string($product[1]);
    $price = $product[2];
    $image = $conn->real_escape_string($product[3]);
    $category = $conn->real_escape_string($product[4]);
    $stock = $product[5];
    $featured = $product[6];
    
    $insertProduct = "INSERT IGNORE INTO `products` (`name`, `description`, `price`, `image`, `category`, `stock`, `featured`) 
                      VALUES ('$name', '$description', $price, '$image', '$category', $stock, $featured)";
    if ($conn->query($insertProduct)) {
        echo "✓ Product '$name' added<br>";
    }
}

// Create admin user
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$insertAdmin = "INSERT IGNORE INTO `users` (`firstName`, `lastName`, `email`, `password`, `role`) 
                VALUES ('Admin', 'User', 'admin@example.com', '$adminPassword', 'admin')";

if ($conn->query($insertAdmin)) {
    echo "✓ Admin user created<br>";
}

// Create test user
$userPassword = password_hash('user123', PASSWORD_DEFAULT);
$insertUser = "INSERT IGNORE INTO `users` (`firstName`, `lastName`, `email`, `password`, `role`) 
               VALUES ('Regular', 'User', 'user@example.com', '$userPassword', 'user')";

if ($conn->query($insertUser)) {
    echo "✓ Test user created<br>";
}

echo "<br><strong>Database setup completed successfully!</strong><br><br>";
echo "<strong>Test Credentials:</strong><br>";
echo "Admin: admin@example.com / admin123<br>";
echo "User: user@example.com / user123<br><br>";
echo "<strong>Next Steps:</strong><br>";
echo "1. Make sure XAMPP is running (Apache + MySQL)<br>";
echo "2. Visit your project in browser<br>";
echo "3. Test login with the credentials above<br>";

$conn->close();
?> 