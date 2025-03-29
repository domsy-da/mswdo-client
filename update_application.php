<?php
@include 'config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('location:index.php');
    exit();
}

// Get the admin's information from the session
$admin_id = $_SESSION['user_id'];
$admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Admin";

// Fetch admin details from the database
try {
    $sql = "SELECT name FROM user_form WHERE id = ? AND user_type = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Update the admin_name from the database
        $admin_name = $row['name'];
    }
    $stmt->close();
} catch (Exception $e) {
    // If there's an error with the database query, we'll use the session name
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('location:admin_services.php');
    exit();
}

$application_id = $_GET['id'];
$success_message = '';
$error_message = '';

// Fetch application details
try {
    $sql = "SELECT * FROM application_requests WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('location:admin_services.php');
        exit();
    }
    
    $application = $result->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    $error_message = "Error fetching application details: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $admin_remarks = $_POST['admin_remarks']; // We'll still collect this but not save it to DB
    
    try {
        // Begin transaction to ensure data consistency
        $conn->begin_transaction();
        
        // Update application status - FIXED: removed admin_remarks from the query
        $sql = "UPDATE application_requests SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $application_id);
        $stmt->execute();
        $stmt->close();
        
        // Commit the transaction
        $conn->commit();
        
        $success_message = "Application status updated successfully!";
        
        // Refresh application data
        $sql = "SELECT * FROM application_requests WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $application_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $application = $result->fetch_assoc();
        $stmt->close();
        
    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        $conn->rollback();
        $error_message = "Error updating application: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Application - MSWDO BMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #1a2e36;
            --secondary-color: #0c5c2f;
            --accent-color: #4cd964;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --light-text: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background-color: var(--primary-color);
            color: var(--light-text);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar.collapsed {
            margin-left: -280px;
        }
        
        .sidebar-title {
            padding: 20px;
            font-size: 1.4rem;
            font-weight: bold;
            background-color: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-title i {
            font-size: 1.8rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .sidebar-menu li {
            transition: all 0.3s ease;
        }
        
        .sidebar-menu li a {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            color: var(--light-text);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: var(--accent-color);
        }
        
        .sidebar-menu li.active a {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: var(--accent-color);
            font-weight: 600;
        }
        
        .sidebar-menu li i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        /* Navbar Styles */
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 1rem;
            height: 70px;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar-brand img {
            height: 45px;
        }
        
        .navbar-brand h1 {
            font-size: 1.2rem;
            margin-bottom: 0;
            color: var(--dark-text);
            font-weight: 600;
        }
        
        .menu-toggle {
            background: none;
            border: none;
            color: var(--dark-text);
            font-size: 1.5rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .menu-toggle:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 5px 15px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .user-profile:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-color);
        }
        
        .user-profile span {
            font-weight: 500;
        }
        
        /* Content Styles */
        .content-wrapper {
            margin-left: 280px;
            padding: 20px;
            transition: all 0.3s ease;
            min-height: calc(100vh - 70px);
            margin-top: 70px;
        }
        
        .content-wrapper.full-width {
            margin-left: 0;
        }
        
        .breadcrumb {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .breadcrumb-item a {
            color: var(--dark-text);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            background-color: var(--accent-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .form-container {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
        }
        
        .form-title {
            color: #1a2e36;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0c5c2f;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .action-buttons {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                margin-left: -280px;
                box-shadow: none;
            }
            
            .sidebar.show {
                margin-left: 0;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            }
            
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            
            .navbar {
                left: 0;
                width: 100%;
            }
            
            .navbar-brand h1 {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .table td, .table th {
                padding: 10px;
            }
            
            .action-btn {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand h1 {
                display: none;
            }
            
            .content {
                padding: 15px;
            }
            
            .breadcrumb {
                padding: 10px;
            }
            
            .user-profile span {
                display: none;
            }
        }
        
        /* Fix for sidebar scrolling on mobile */
        @media (max-height: 600px) {
            .sidebar {
                overflow-y: auto;
                height: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-title">
            <i class="fas fa-shield-alt"></i>
            MSWDO Admin
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="active"><a href="admin_services.php"><i class="fas fa-hands-helping"></i> Services</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_programs.php"><i class="fas fa-calendar-alt"></i> Programs</a></li>
        </ul>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <button id="menu-toggle" class="menu-toggle me-2">
                <i class="fas fa-bars"></i>
            </button>
            <div class="navbar-brand">
                <img src="/useradmin/img/mswdologo.png" alt="Logo">
                <h1>Municipal Social Welfare & Development Office - Gloria (Admin)</h1>
            </div>
            <div class="ms-auto d-flex align-items-center">
                <div class="dropdown">
                    <div class="user-profile" id="user-profile" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="img\adminprof.png" alt="Admin">
                        <span><?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?></span>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="user-profile">
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="content-wrapper" id="content-wrapper">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
                <li class="breadcrumb-item"><a href="admin_services.php">Services</a></li>
                <li class="breadcrumb-item"><a href="view_application.php?id=<?php echo $application_id; ?>">View Application</a></li>
                <li class="breadcrumb-item active">Update Application</li>
            </ol>
        </nav>
        
        <div class="content">
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="form-container">
                <h2 class="form-title">Update Application Status</h2>
                
                <div class="mb-4">
                    <h5>Application Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Client Name:</strong> <?php echo htmlspecialchars($application['client_name']); ?></p>
                            <p><strong>Request Purpose:</strong> <?php echo htmlspecialchars($application['request_purpose']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Application Date:</strong> <?php echo date('M d, Y', strtotime($application['application_date'])); ?></p>
                            <p><strong>Current Status:</strong> 
                                <span class="badge <?php echo $application['status'] === 'Approved' ? 'bg-success' : ($application['status'] === 'Pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php echo $application['status']; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="status" class="form-label">Update Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Pending" <?php echo $application['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo $application['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo $application['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_remarks" class="form-label">Admin Remarks </label>
                        <textarea class="form-control" id="admin_remarks" name="admin_remarks" rows="4"></textarea>
                       
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="view_application.php?id=<?php echo $application_id; ?>" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const menuToggle = document.getElementById("menu-toggle");
        const sidebar = document.getElementById("sidebar");
        const contentWrapper = document.getElementById("content-wrapper");
        
        // Toggle sidebar
        menuToggle.addEventListener("click", function() {
            sidebar.classList.toggle("collapsed");
            sidebar.classList.toggle("show");
            contentWrapper.classList.toggle("full-width");
        });
        
        // Handle responsive behavior
        function checkWidth() {
            if (window.innerWidth < 992) {
                sidebar.classList.add("collapsed");
                contentWrapper.classList.add("full-width");
            } else {
                sidebar.classList.remove("collapsed");
                sidebar.classList.remove("show");
                contentWrapper.classList.remove("full-width");
            }
        }
        
        checkWidth();
        window.addEventListener("resize", checkWidth);
    });
    </script>
</body>
</html>

