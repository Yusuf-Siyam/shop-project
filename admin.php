<?php
session_start();
include("connect.php");

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Get user information
$userFirstName = $userLastName = "";
$email = $_SESSION['email'];
$query = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'");
while($row = mysqli_fetch_array($query)) {
    $userFirstName = $row['firstName'];
    $userLastName = $row['lastName'];
}

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
    
    // Image upload handling
    $image = "https://via.placeholder.com/600x400"; // Default image
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if file is an actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            // Generate unique filename
            $filename = uniqid() . "." . $imageFileType;
            $target_file = $target_dir . $filename;
            
            // Upload file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $target_file;
            }
        }
    }
    
    $insertProject = "INSERT INTO projects (title, description, image, category, technologies, live_link, github_link) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insertProject);
    $stmt->bind_param("sssssss", $title, $description, $image, $category, $technologies, $live_link, $github_link);
    
    if($stmt->execute()) {
        $message = "Project added successfully!";
    } else {
        $error = "Error adding project: " . $stmt->error;
    }
    $stmt->close();
}

// Delete project
if(isset($_GET['delete_project'])) {
    $id = $_GET['delete_project'];
    $deleteProject = "DELETE FROM projects WHERE id = ?";
    
    $stmt = $conn->prepare($deleteProject);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $message = "Project deleted successfully!";
    } else {
        $error = "Error deleting project: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all projects
$projects = [];
$projectsStmt = $conn->prepare("SELECT * FROM projects ORDER BY created_at DESC");
$projectsStmt->execute();
$projectsResult = $projectsStmt->get_result();
if($projectsResult) {
    while($row = $projectsResult->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Fetch all contact messages
$contactMessages = [];
$contactStmt = $conn->prepare("SELECT * FROM contacts ORDER BY created_at DESC");
$contactStmt->execute();
$contactResult = $contactStmt->get_result();
if($contactResult) {
    while($row = $contactResult->fetch_assoc()) {
        $contactMessages[] = $row;
    }
}

// Delete contact message
if(isset($_GET['delete_message'])) {
    $id = $_GET['delete_message'];
    $deleteMessage = "DELETE FROM contacts WHERE id = ?";
    
    $stmt = $conn->prepare($deleteMessage);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $message = "Message deleted successfully!";
    } else {
        $error = "Error deleting message: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Admin CSS -->
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f50057;
            --text-color: #333;
            --bg-color: #f8f9fa;
            --card-bg: #fff;
            --border-color: #e0e0e0;
            --sidebar-width: 250px;
            --header-height: 60px;
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            transition: var(--transition);
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            margin: 0;
            font-size: 24px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            transition: var(--transition);
            cursor: pointer;
            text-decoration: none;
            color: white;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.1);
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: var(--transition);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            color: var(--primary-color);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            background: var(--card-bg);
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        
        .tab.active {
            background: var(--primary-color);
            color: white;
        }
        
        /* Content Sections */
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
        
        /* Card */
        .card {
            background: var(--card-bg);
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Form */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 14px;
        }
        
        textarea.form-control {
            min-height: 100px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: #5b54e0;
        }
        
        .btn-danger {
            background: var(--secondary-color);
            color: white;
        }
        
        .btn-danger:hover {
            background: #d80048;
        }
        
        /* Table */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
        }
        
        tr:hover {
            background-color: rgba(0,0,0,0.02);
        }
        
        .action-btns {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 12px;
        }
        
        .edit-btn {
            background: #17a2b8;
            color: white;
        }
        
        .delete-btn {
            background: #dc3545;
            color: white;
        }
        
        /* Project Card */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .project-card {
            background: var(--card-bg);
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .project-image {
            height: 180px;
            overflow: hidden;
        }
        
        .project-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .project-details {
            padding: 15px;
        }
        
        .project-details h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .project-details p {
            margin-bottom: 10px;
            font-size: 14px;
            color: #666;
        }
        
        .project-category {
            display: inline-block;
            padding: 3px 10px;
            background: var(--primary-color);
            color: white;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .project-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }
        
        /* Message Card */
        .message-card {
            background: var(--card-bg);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .message-sender {
            font-weight: 500;
        }
        
        .message-date {
            font-size: 12px;
            color: #666;
        }
        
        .message-subject {
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .message-content {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 3px;
            margin-bottom: 10px;
        }
        
        .message-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <div class="sidebar-menu">
                <a href="#" class="menu-item active" data-tab="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item" data-tab="projects">
                    <i class="fas fa-project-diagram"></i>
                    <span>Projects</span>
                </a>
                <a href="#" class="menu-item" data-tab="messages">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </a>
                <a href="#" class="menu-item" data-tab="settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="homepage.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>View Site</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Welcome, <?php echo $userFirstName; ?>!</h1>
                <div class="user-info">
                    <div class="avatar"><?php echo substr($userFirstName, 0, 1) . substr($userLastName, 0, 1); ?></div>
                    <span><?php echo $userFirstName . " " . $userLastName; ?></span>
                </div>
            </div>
            
            <?php if(!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Dashboard Section -->
            <div class="content-section active" id="dashboard">
                <h2>Dashboard</h2>
                <div class="card">
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px; padding: 20px; background: #e3f2fd; border-radius: 5px;">
                            <h3><?php echo count($projects); ?></h3>
                            <p>Total Projects</p>
                        </div>
                        <div style="flex: 1; min-width: 200px; padding: 20px; background: #f1f8e9; border-radius: 5px;">
                            <h3><?php echo count($contactMessages); ?></h3>
                            <p>Messages</p>
                        </div>
                    </div>
                </div>
                
                <h3>Recent Projects</h3>
                <div class="projects-grid">
                    <?php 
                    $recentProjects = array_slice($projects, 0, 3);
                    foreach($recentProjects as $project): 
                    ?>
                    <div class="project-card">
                        <div class="project-image">
                            <img src="<?php echo $project['image']; ?>" alt="<?php echo $project['title']; ?>">
                        </div>
                        <div class="project-details">
                            <span class="project-category"><?php echo $project['category']; ?></span>
                            <h3><?php echo $project['title']; ?></h3>
                            <p><?php echo substr($project['description'], 0, 100) . '...'; ?></p>
                            <div class="project-actions">
                                <a href="?tab=projects&edit_project=<?php echo $project['id']; ?>" class="btn action-btn edit-btn">Edit</a>
                                <a href="?delete_project=<?php echo $project['id']; ?>" class="btn action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this project?')">Delete</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <h3>Recent Messages</h3>
                <?php 
                $recentMessages = array_slice($contactMessages, 0, 3);
                foreach($recentMessages as $message): 
                ?>
                <div class="message-card">
                    <div class="message-header">
                        <div class="message-sender"><?php echo $message['name']; ?> (<?php echo $message['email']; ?>)</div>
                        <div class="message-date"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></div>
                    </div>
                    <div class="message-subject"><?php echo $message['subject']; ?></div>
                    <div class="message-content"><?php echo $message['message']; ?></div>
                    <div class="message-actions">
                        <a href="mailto:<?php echo $message['email']; ?>" class="btn action-btn edit-btn">Reply</a>
                        <a href="?delete_message=<?php echo $message['id']; ?>" class="btn action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Projects Section -->
            <div class="content-section" id="projects">
                <h2>Manage Projects</h2>
                <div class="card">
                    <h3>Add New Project</h3>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Project Title</label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="image">Project Image</label>
                            <input type="file" name="image" id="image" class="form-control">
                            <small>Leave empty to use placeholder image</small>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control" required>
                                <option value="web">Web Development</option>
                                <option value="app">App Development</option>
                                <option value="design">Design</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="technologies">Technologies (comma separated)</label>
                            <input type="text" name="technologies" id="technologies" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="live_link">Live Demo Link</label>
                            <input type="url" name="live_link" id="live_link" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="github_link">GitHub Link</label>
                            <input type="url" name="github_link" id="github_link" class="form-control">
                        </div>
                        <button type="submit" name="add_project" class="btn btn-primary">Add Project</button>
                    </form>
                </div>
                
                <h3>All Projects</h3>
                <div class="projects-grid">
                    <?php foreach($projects as $project): ?>
                    <div class="project-card">
                        <div class="project-image">
                            <img src="<?php echo $project['image']; ?>" alt="<?php echo $project['title']; ?>">
                        </div>
                        <div class="project-details">
                            <span class="project-category"><?php echo $project['category']; ?></span>
                            <h3><?php echo $project['title']; ?></h3>
                            <p><?php echo substr($project['description'], 0, 100) . '...'; ?></p>
                            <div class="project-actions">
                                <a href="?tab=projects&edit_project=<?php echo $project['id']; ?>" class="btn action-btn edit-btn">Edit</a>
                                <a href="?delete_project=<?php echo $project['id']; ?>" class="btn action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this project?')">Delete</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Messages Section -->
            <div class="content-section" id="messages">
                <h2>Contact Messages</h2>
                
                <?php if(empty($contactMessages)): ?>
                    <div class="card">
                        <p>No messages yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($contactMessages as $message): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <div class="message-sender"><?php echo $message['name']; ?> (<?php echo $message['email']; ?>)</div>
                            <div class="message-date"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></div>
                        </div>
                        <div class="message-subject"><?php echo $message['subject']; ?></div>
                        <div class="message-content"><?php echo $message['message']; ?></div>
                        <div class="message-actions">
                            <a href="mailto:<?php echo $message['email']; ?>" class="btn action-btn edit-btn">Reply</a>
                            <a href="?delete_message=<?php echo $message['id']; ?>" class="btn action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Settings Section -->
            <div class="content-section" id="settings">
                <h2>Settings</h2>
                <div class="card">
                    <h3>Profile Settings</h3>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo $userFirstName; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo $userLastName; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo $email; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password (leave empty to keep current)</label>
                            <input type="password" name="new_password" id="new_password" class="form-control">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
                
                <div class="card">
                    <h3>Site Settings</h3>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="site_title">Site Title</label>
                            <input type="text" name="site_title" id="site_title" class="form-control" value="My Portfolio">
                        </div>
                        <div class="form-group">
                            <label for="tagline">Tagline</label>
                            <input type="text" name="tagline" id="tagline" class="form-control" value="Web Developer & Designer">
                        </div>
                        <div class="form-group">
                            <label for="about_text">About Me Text</label>
                            <textarea name="about_text" id="about_text" class="form-control" rows="5">I am a passionate web developer with expertise in frontend and backend technologies.</textarea>
                        </div>
                        <button type="submit" name="update_site" class="btn btn-primary">Update Site Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching
        const menuItems = document.querySelectorAll('.menu-item');
        const contentSections = document.querySelectorAll('.content-section');
        
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (!this.getAttribute('data-tab')) return;
                
                e.preventDefault();
                
                const tabId = this.getAttribute('data-tab');
                
                // Update active tab
                menuItems.forEach(item => item.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding content
                contentSections.forEach(section => {
                    if (section.id === tabId) {
                        section.classList.add('active');
                    } else {
                        section.classList.remove('active');
                    }
                });
                
                // Update URL
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabId);
                window.history.pushState({}, '', url);
            });
        });
        
        // Check for tab in URL on page load
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            
            if (tabParam) {
                const tabMenuItem = document.querySelector(`.menu-item[data-tab="${tabParam}"]`);
                if (tabMenuItem) {
                    tabMenuItem.click();
                }
            }
        });
    </script>
</body>
</html> 