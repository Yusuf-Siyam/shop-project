<?php
include("connect.php");

echo "<h2>🔧 Fixing Orders Table - Shipping Columns</h2>";

// List of columns to check and add
$columnsToAdd = [
    'shipping_address' => "VARCHAR(255) NOT NULL",
    'shipping_city' => "VARCHAR(100) NOT NULL",
    'shipping_postal_code' => "VARCHAR(20) NOT NULL",
    'shipping_phone' => "VARCHAR(20) NOT NULL",
    'notes' => "TEXT"
];

$successCount = 0;

foreach ($columnsToAdd as $columnName => $columnType) {
    // 1. Check if column exists
    $checkColumn = "SHOW COLUMNS FROM orders LIKE '$columnName'";
    $result = mysqli_query($conn, $checkColumn);

    if (mysqli_num_rows($result) == 0) {
        // 2. Add the missing column
        echo "• Column <strong>'$columnName'</strong> is missing. Adding it...<br>";
        
        $alterTable = "ALTER TABLE orders ADD COLUMN $columnName $columnType";
        
        if (mysqli_query($conn, $alterTable)) {
            echo "<span style='color:green;'>✓ Added '$columnName' successfully.</span><br>";
            $successCount++;
        } else {
            echo "<span style='color:red;'>✗ Error adding '$columnName': " . mysqli_error($conn) . "</span><br>";
        }
    } else {
        echo "<span style='color:gray;'>• Column '$columnName' already exists.</span><br>";
    }
}

echo "<br><hr>";
if ($successCount > 0) {
    echo "<h3 style='color:green;'>✓ Database updated! Shipping columns added.</h3>";
} else {
    echo "<h3>Database check complete. No changes needed.</h3>";
}

echo "<a href='checkout.php' style='background:#6c63ff; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Try Checkout Again</a>";
?>