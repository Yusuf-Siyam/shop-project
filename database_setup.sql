-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firstName` VARCHAR(50) NOT NULL,
    `lastName` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE IF NOT EXISTS `products` (
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
);

-- Create cart table
CREATE TABLE IF NOT EXISTS `cart` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
);

-- Create orders table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO `categories` (`name`, `description`) VALUES
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Fashion and apparel'),
('Books', 'Books and publications'),
('Home & Kitchen', 'Home appliances and kitchen items'),
('Beauty', 'Beauty and personal care products');

-- Insert sample products
INSERT INTO `products` (`name`, `description`, `price`, `image`, `category`, `stock`, `featured`) VALUES
('Smartphone X', 'Latest smartphone with advanced features', 699.99, 'images/products/smartphone.jpg', 'Electronics', 50, TRUE),
('Laptop Pro', 'High-performance laptop for professionals', 1299.99, 'images/products/laptop.jpg', 'Electronics', 30, TRUE),
('Wireless Earbuds', 'Premium wireless earbuds with noise cancellation', 149.99, 'images/products/earbuds.jpg', 'Electronics', 100, FALSE),
('Men\'s T-Shirt', 'Comfortable cotton t-shirt for everyday wear', 29.99, 'images/products/tshirt.jpg', 'Clothing', 200, FALSE),
('Women\'s Dress', 'Elegant summer dress', 59.99, 'images/products/dress.jpg', 'Clothing', 75, TRUE),
('Coffee Maker', 'Automatic coffee maker with timer', 89.99, 'images/products/coffee-maker.jpg', 'Home & Kitchen', 40, FALSE),
('Blender', 'High-speed blender for smoothies and more', 79.99, 'images/products/blender.jpg', 'Home & Kitchen', 60, FALSE),
('Fiction Novel', 'Bestselling fiction novel', 19.99, 'images/products/book.jpg', 'Books', 150, FALSE),
('Skincare Set', 'Complete skincare routine set', 49.99, 'images/products/skincare.jpg', 'Beauty', 80, TRUE),
('Perfume', 'Luxury fragrance for women', 89.99, 'images/products/perfume.jpg', 'Beauty', 45, FALSE);

-- Insert a test admin user (password: admin123)
INSERT INTO `users` (`firstName`, `lastName`, `email`, `password`, `role`) VALUES
('Admin', 'User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert a test regular user (password: user123)
INSERT INTO `users` (`firstName`, `lastName`, `email`, `password`, `role`) VALUES
('Regular', 'User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'); 