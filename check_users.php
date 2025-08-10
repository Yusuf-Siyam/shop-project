<?php
include("connect.php");

// Check if users table exists
$checkTable = "SHOW TABLES LIKE 'users'";
$tableExists = mysqli_query($conn, $checkTable);

if(mysqli_num_rows($tableExists) == 0) {
    echo "Users table does not exist! Please run setup_users.php first.<br>";
} else {
    // Get all users from the database
    $query = "SELECT * FROM users";
    $result = mysqli_query($conn, $query);
    
    if($result) {
        echo "<h3>Users in Database:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Created At</th></tr>";
        
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['firstName'] . "</td>";
            echo "<td>" . $row['lastName'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Error fetching users: " . mysqli_error($conn);
    }
}
?> 