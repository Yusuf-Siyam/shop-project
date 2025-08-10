<?php
// Test PHP
echo "PHP is working!<br>";

// Test database connection
include("connect.php");

if($conn) {
    echo "Database connection successful!<br>";
    
    // Test if we can query the database
    $testQuery = "SHOW TABLES";
    $result = mysqli_query($conn, $testQuery);
    
    if($result) {
        echo "Database query successful!<br>";
        echo "Tables in database:<br>";
        while($row = mysqli_fetch_array($result)) {
            echo "- " . $row[0] . "<br>";
        }
    } else {
        echo "Error querying database: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Database connection failed!<br>";
    echo "Error: " . mysqli_connect_error() . "<br>";
}

// Show PHP configuration
echo "<br>PHP Configuration:<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "MySQL Extension: " . (extension_loaded('mysqli') ? 'Loaded' : 'Not Loaded') . "<br>";
?> 