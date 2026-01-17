<?php
echo "<h1>🚀 Shop Project Startup Guide</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;'>";

// Check PHP version
echo "<h2>📋 System Requirements Check</h2>";
$phpVersion = phpversion();
echo "✓ PHP Version: $phpVersion<br>";

if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "✓ PHP version is compatible<br>";
} else {
    echo "⚠ PHP version should be 7.4 or higher<br>";
}

// Check MySQL extension
if (extension_loaded('mysqli')) {
    echo "✓ MySQLi extension is loaded<br>";
} else {
    echo "✗ MySQLi extension is not loaded<br>";
}

// Check session support
if (function_exists('session_start')) {
    echo "✓ Session support is available<br>";
} else {
    echo "✗ Session support is not available<br>";
}

echo "<br><h2>🔧 Setup Steps</h2>";
echo "<ol>";
echo "<li><strong>Start XAMPP:</strong> Make sure Apache and MySQL services are running</li>";
echo "<li><strong>Setup Database:</strong> <a href='setup_database.php' target='_blank'>Click here to setup database</a></li>";
echo "<li><strong>Test Connection:</strong> <a href='test_connection.php' target='_blank'>Click here to test database connection</a></li>";
echo "<li><strong>Start Application:</strong> <a href='index.php'>Click here to go to login page</a></li>";
echo "</ol>";

echo "<br><h2>📊 Project Status</h2>";

// Check if database setup has been run
if (file_exists('setup_database.php')) {
    echo "✓ Database setup script is available<br>";
} else {
    echo "✗ Database setup script is missing<br>";
}

// Check if connection file exists
if (file_exists('connect.php')) {
    echo "✓ Database connection file is available<br>";
} else {
    echo "✗ Database connection file is missing<br>";
}

// Check if main files exist
$mainFiles = ['index.php', 'homepage.php', 'shop.php', 'admin.php'];
foreach ($mainFiles as $file) {
    if (file_exists($file)) {
        echo "✓ $file is available<br>";
    } else {
        echo "✗ $file is missing<br>";
    }
}

echo "<br><h2>🎯 Quick Start</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0066cc;'>";
echo "<strong>To get started immediately:</strong><br>";
echo "1. Make sure XAMPP is running<br>";
echo "2. <a href='setup_database.php' style='color: #0066cc; font-weight: bold;'>Run Database Setup</a><br>";
echo "3. <a href='index.php' style='color: #0066cc; font-weight: bold;'>Go to Login Page</a><br>";
echo "</div>";

echo "<br><h2>🔑 Test Accounts</h2>";
echo "<div style='background: #f0fff0; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>";
echo "<strong>After running setup, you can use:</strong><br>";
echo "• Admin: admin@example.com / admin123<br>";
echo "• User: user@example.com / user123<br>";
echo "</div>";

echo "<br><h2>📁 Project Structure</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: monospace;'>";
echo "shop-project-main/<br>";
echo "├── connect.php<br>";
echo "├── setup_database.php<br>";
echo "├── test_connection.php<br>";
echo "├── index.php (Login/Register)<br>";
echo "├── homepage.php (Dashboard)<br>";
echo "├── shop.php (Products)<br>";
echo "├── admin.php (Admin Panel)<br>";
echo "└── uploads/ (Images)<br>";
echo "</div>";

echo "<br><h2>❓ Need Help?</h2>";
echo "If you encounter issues:<br>";
echo "• Check XAMPP error logs<br>";
echo "• Verify all services are running<br>";
echo "• Run the test connection script<br>";
echo "• Check file permissions<br>";

echo "</div>";
?>
