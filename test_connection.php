<?php
echo "<h2>Testing Database Connection</h2>";

// Test database connection
include("connect.php");
echo "✓ Database connection successful<br>";
echo "✓ Connected to database: " . $conn->database . "<br><br>";

// Test if tables exist
$tables = ['users', 'categories', 'products', 'cart', 'orders', 'order_items'];

echo "<h3>Checking Tables:</h3>";
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✓ Table '$table' exists<br>";
    } else {
        echo "✗ Table '$table' does not exist<br>";
    }
}

echo "<br><h3>Table Structures:</h3>";
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<strong>$table table structure:</strong><br>";
        $columns = $conn->query("DESCRIBE `$table`");
        while ($col = $columns->fetch_assoc()) {
            echo "  - {$col['Field']}: {$col['Type']} ({$col['Null']})<br>";
        }
        echo "<br>";
    }
}

echo "<br><h3>Sample Data Check:</h3>";

// Check users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$count = $result->fetch_assoc()['count'];
echo "Users: $count records<br>";

// Check categories
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
$count = $result->fetch_assoc()['count'];
echo "Categories: $count records<br>";

// Check products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$count = $result->fetch_assoc()['count'];
echo "Products: $count records<br>";

$conn->close();
echo "<br>✓ Connection test completed!";
?>
