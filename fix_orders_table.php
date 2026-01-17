<?php
include("connect.php");

echo "<h2>🔧 Fixing Orders Table</h2>";

// 1. Check if 'payment_method' column exists
$checkColumn = "SHOW COLUMNS FROM orders LIKE 'payment_method'";
$result = mysqli_query($conn, $checkColumn);

if (mysqli_num_rows($result) == 0) {
    // Column doesn't exist, so add it
    echo "• 'payment_method' column is missing. Adding it now...<br>";
    
    // Add the column after 'total_amount'
    $alterTable = "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery' AFTER total_amount";
    
    if (mysqli_query($conn, $alterTable)) {
        echo "<h3 style='color:green;'>✓ Successfully added 'payment_method' column.</h3>";
        echo "<p>Your checkout should work now!</p>";
    } else {
        die("<h3 style='color:red;'>✗ Error adding column: " . mysqli_error($conn) . "</h3>");
    }
} else {
    echo "<h3 style='color:green;'>✓ 'payment_method' column already exists.</h3>";
    echo "<p>No changes needed.</p>";
}

echo "<br><hr>";
echo "<a href='checkout.php' style='background:#6c63ff; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Try Checkout Again</a>";
?>