<?php
session_start();
include("connect.php");

// 1. Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// 2. STRICT SECURITY CHECK: Check if user is an ADMIN
// If the session role is NOT set or is NOT 'admin', redirect them immediately.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

// User is confirmed as Admin
$email = $_SESSION['email'];
$userFirstName = $_SESSION['firstName'];
$userLastName = $_SESSION['lastName'];

// Create projects table if it doesn't exist
$createProjectsTable = "CREATE TABLE IF NOT EXISTS projects (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    technologies TEXT NOT NULL,
    live_link VARCHAR(255),
    github_link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createProjectsTable)) {
    $error = "Error creating projects table: " . mysqli_error($conn);
}

// Handle project actions (add, edit, delete)
$message = "";
$error = "";

// Add new project
if(isset($_POST['add_project'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $technologies = $_POST['technologies'];
    $live_link = $_POST['live_link'];
    $github_link = $_POST['github_link'];
    
    // Default image if none uploaded
    $image = "https://via.placeholder.com/600x400";
    
    // Check if image URL is provided
    if(isset($_POST['image_url']) && !empty($_POST['image_url'])) {
        $image = $_POST['image_url'];
    }
    
    $insertQuery = "INSERT INTO projects (title, description, image, category, technologies, live_link, github_link) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("sssssss", $title, $description, $image, $category, $technologies, $live_link, $github_link);
    
    if($stmt->execute()) {
        $message = "Project added successfully!";
    } else {
        $error = "Error adding project: " . $conn->error;
    }
}

// Delete project
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $deleteQuery = "DELETE FROM projects WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $message = "Project deleted successfully!";
    } else {
        $error = "Error deleting project: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f50057;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 4px 24px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            display: flex;
        }
        
        .sidebar {
            width: 260px;
            background: var(--white);
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            padding: 20px 0;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        
        .logo h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .menu-items {
            list-style: none;
        }
        
        .menu-item {
            padding: 15px 30px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #666;
            text-decoration: none;
        }
        
        .menu-item i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(108, 99, 255, 0.1);
            color: var(--primary-color);
            border-right: 3px solid var(--primary-color);
        }
        
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
        
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .project-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .project-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }
        
        .project-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .project-info {
            padding: 15px;
        }
        
        .project-actions {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2>Portfolio Admin</h2>
        </div>
        <ul class="menu-items">
            <li class="menu-item active" data-tab="dashboard">
                <i class="fas fa-home"></i> Dashboard
            </li>
            <li class="menu-item" data-tab="add-project">
                <i class="fas fa-plus-circle"></i> Add Project
            </li>
            <li class="menu-item" data-tab="messages">
                <i class="fas fa-envelope"></i> Messages
            </li>
            <a href="admin-products.php" class="menu-item">
                <i class="fas fa-shopping-bag"></i> Shop Admin
            </a>
            <a href="homepage.php" class="menu-item">
                <i class="fas fa-arrow-left"></i> Back to Site
            </a>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>Portfolio Management</h2>
            <div class="user-profile">
                <div class="avatar">
                    <?php echo substr($userFirstName, 0, 1); ?>
                </div>
                <div>
                    <h4><?php echo $userFirstName . ' ' . $userLastName; ?></h4>
                    <small>Administrator</small>
                </div>
            </div>
        </div>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div id="dashboard" class="content-section active">
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3>All Projects</h3>
                </div>
                
                <div class="project-list">
                    <?php
                    $projects = mysqli_query($conn, "SELECT * FROM projects ORDER BY id DESC");
                    if(mysqli_num_rows($projects) > 0):
                        while($p = mysqli_fetch_assoc($projects)):
                    ?>
                        <div class="project-card">
                            <img src="<?php echo $p['image']; ?>" class="project-img" alt="Project Image">
                            <div class="project-info">
                                <h4><?php echo $p['title']; ?></h4>
                                <span style="background:#eee; padding:2px 8px; border-radius:4px; font-size:0.8rem;"><?php echo $p['category']; ?></span>
                                <p style="font-size:0.9rem; color:#666; margin-top:10px;"><?php echo substr($p['description'], 0, 100) . '...'; ?></p>
                            </div>
                            <div class="project-actions">
                                <a href="?delete=<?php echo $p['id']; ?>" class="btn-delete" onclick="return confirm('Delete this project?')">Delete</a>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                        echo "<p>No projects found.</p>";
                    endif;
                    ?>
                </div>
            </div>
        </div>
        
        <div id="add-project" class="content-section">
            <div class="card">
                <h3>Add New Project</h3>
                <form method="post">
                    <div class="form-group">
                        <label>Project Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Category (e.g., Web Dev, App, Design)</label>
                        <input type="text" name="category" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" style="height:100px" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Technologies Used (comma separated)</label>
                        <input type="text" name="technologies" class="form-control" placeholder="HTML, CSS, PHP..." required>
                    </div>
                    <div class="form-group">
                        <label>Image URL</label>
                        <input type="text" name="image_url" class="form-control" placeholder="https://..." required>
                    </div>
                    <div style="display:flex; gap:20px;">
                        <div class="form-group" style="flex:1">
                            <label>Live Link</label>
                            <input type="text" name="live_link" class="form-control">
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>GitHub Link</label>
                            <input type="text" name="github_link" class="form-control">
                        </div>
                    </div>
                    <button type="submit" name="add_project" class="btn btn-primary">Save Project</button>
                </form>
            </div>
        </div>
        
        <div id="messages" class="content-section">
            <div class="card">
                <h3>Contact Messages</h3>
                <table>
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:10px;">Name</th>
                            <th style="text-align:left; padding:10px;">Email</th>
                            <th style="text-align:left; padding:10px;">Subject</th>
                            <th style="text-align:left; padding:10px;">Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Check if contacts table exists first
                        $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'contacts'");
                        if(mysqli_num_rows($checkTable) > 0) {
                            $msgs = mysqli_query($conn, "SELECT * FROM contacts ORDER BY id DESC");
                            while($m = mysqli_fetch_assoc($msgs)) {
                                echo "<tr>
                                    <td style='padding:10px; border-bottom:1px solid #eee;'>{$m['name']}</td>
                                    <td style='padding:10px; border-bottom:1px solid #eee;'>{$m['email']}</td>
                                    <td style='padding:10px; border-bottom:1px solid #eee;'>{$m['subject']}</td>
                                    <td style='padding:10px; border-bottom:1px solid #eee;'>{$m['message']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='padding:20px; text-align:center;'>No messages table found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // Simple Tab Switching Logic
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.menu-item[data-tab]');
            const contentSections = document.querySelectorAll('.content-section');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remove active class from all items and sections
                    menuItems.forEach(i => i.classList.remove('active'));
                    contentSections.forEach(s => s.classList.remove('active'));
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    // Show corresponding section
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>