<?php
include("connect.php");

// Products Table
$createProductsTable = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    stock INT(11) NOT NULL DEFAULT 1,
    featured BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createProductsTable)) {
    echo "Error creating products table: " . mysqli_error($conn) . "<br>";
} else {
    echo "Products table created successfully<br>";
}

// Categories Table
$createCategoriesTable = "CREATE TABLE IF NOT EXISTS categories (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createCategoriesTable)) {
    echo "Error creating categories table: " . mysqli_error($conn) . "<br>";
} else {
    echo "Categories table created successfully<br>";
}

// Cart Table
$createCartTable = "CREATE TABLE IF NOT EXISTS cart (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createCartTable)) {
    echo "Error creating cart table: " . mysqli_error($conn) . "<br>";
} else {
    echo "Cart table created successfully<br>";
}

// Orders Table
$createOrdersTable = "CREATE TABLE IF NOT EXISTS orders (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_postal_code VARCHAR(20) NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createOrdersTable)) {
    echo "Error creating orders table: " . mysqli_error($conn) . "<br>";
} else {
    echo "Orders table created successfully<br>";
}

// Order Items Table
$createOrderItemsTable = "CREATE TABLE IF NOT EXISTS order_items (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createOrderItemsTable)) {
    echo "Error creating order_items table: " . mysqli_error($conn) . "<br>";
} else {
    echo "Order items table created successfully<br>";
}

// Add some default categories
$insertCategories = "INSERT INTO categories (name, description) VALUES 
    ('Electronics', 'Electronic devices and accessories'),
    ('Clothing', 'Fashionable apparel for all ages'),
    ('Home & Kitchen', 'Essential items for your home'),
    ('Books', 'Books across various genres'),
    ('Toys & Games', 'Fun activities for kids and adults')";

if (!mysqli_query($conn, $insertCategories)) {
    echo "Error inserting default categories: " . mysqli_error($conn) . "<br>";
} else {
    echo "Default categories added successfully<br>";
}

echo "Database setup completed!";
?> 