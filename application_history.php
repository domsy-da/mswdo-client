<?php
@include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Unknown User";

// Get user name from database
try {
    $sql = "SELECT name FROM user_form WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_name = $row['name'];
    }
    $stmt->close();
} catch (Exception $e) {
    // Handle error silently
}

// Check for new successful applications
$new_success_applications = [];
try {
    $sql = "SELECT id, request_purpose, client_name, application_date 
            FROM application_requests 
            WHERE user_id = ? AND status = 'Success' AND viewed = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $new_success_applications[] = $row;
    }
    
    // Mark notifications as viewed
    if (!empty($new_success_applications)) {
        $sql = "UPDATE application_requests SET viewed = 1 
                WHERE user_id = ? AND status = 'Success' AND viewed = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    $stmt->close();
} catch (Exception $e) {
    // Handle error silently
}

// Fetch user's applications
$applications = [];
try {
    $sql = "SELECT id, request_purpose, client_name, application_date, status, amount 
            FROM application_requests 
            WHERE user_id = ? 
            ORDER BY application_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
    
    $stmt->close();
} catch (Exception $e) {
    // Handle error silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application History - MSWDO BMS</title>
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
        }
        
        /* Status Badges */
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background-color: rgba(255, 159, 67, 0.1);
            color: #ff9f43;
        }
        
        .status-approved {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .status-rejected {
            background-color: rgba(234, 84, 85, 0.1);
            color: #ea5455;
        }
        
        .status-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        /* Card Styles */
        .stat-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
        }
        
        .stat-icon.blue {
            background-color: #4361ee;
        }
        
        .stat-icon.green {
            background-color: var(--secondary-color);
        }
        
        .stat-icon.orange {
            background-color: #ff9f43;
        }
        
        .stat-icon.red {
            background-color: #ea5455;
        }
        
        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark-text);
        }
        
        .stat-info p {
            color: #6c757d;
            margin: 0;
            font-size: 0.9rem;
        }
        
        /* Success Notification */
        .success-notification {
            background-color: rgba(25, 135, 84, 0.1);
            border-left: 4px solid #198754;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .success-notification-title {
            color: #198754;
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success-notification-content {
            margin-left: 30px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                margin-left: -280px;
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .content-wrapper {
                margin-left: 0;
            }
            
            .navbar-brand h1 {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand h1 {
                display: none;
            }
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-title">
        <i class="fas fa-hands-helping"></i>
        MSWDO Gloria
    </div>
    <ul class="sidebar-menu">
        <li><a href="user_page.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="programs.php"><i class="fas fa-calendar-alt"></i> Programs</a></li>
        <li><a href="file_application.php"><i class="fas fa-file-alt"></i> File Application</a></li>
        <li class="active"><a href="application_history.php"><i class="fas fa-history"></i> Application History</a></li>
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
            <h1>Municipal Social Welfare & Development Office - Gloria</h1>
        </div>
        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <div class="user-profile" id="user-profile" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="img/prof.jpg" alt="User">
                    <span><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></span>
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
            <li class="breadcrumb-item"><a href="user_page.php">Home</a></li>
            <li class="breadcrumb-item active">Application History</li>
        </ol>
    </nav>
    
    <div class="content">
        <h2 class="mb-4 text-center fw-bold text-primary">Your Application History</h2>
        
        <!-- Success Notifications -->
        <?php if (!empty($new_success_applications)): ?>
            <?php foreach ($new_success_applications as $success_app): ?>
                <div class="success-notification">
                    <div class="success-notification-title">
                        <i class="fas fa-check-circle"></i> Application Successful!
                    </div>
                    <div class="success-notification-content">
                        <p>Your application for <strong><?php echo htmlspecialchars($success_app['request_purpose']); ?></strong> 
                        submitted on <?php echo date('M d, Y', strtotime($success_app['application_date'])); ?> 
                        has been successfully processed.</p>
                        <a href="view_user_application.php?id=<?php echo $success_app['id']; ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Status Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($applications, function($app) { return $app['status'] == 'Pending'; })); ?></h3>
                        <p>Pending Applications</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($applications, function($app) { return $app['status'] == 'Approved'; })); ?></h3>
                        <p>Approved Applications</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($applications, function($app) { return $app['status'] == 'Success'; })); ?></h3>
                        <p>Successful Applications</p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (empty($applications)): ?>
        <div class="text-center py-5">
            <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
            <h4>No Applications Found</h4>
            <p class="text-muted">You haven't submitted any applications yet.</p>
            <a href="file_application.php" class="btn btn-primary mt-3">
                <i class="fas fa-plus me-2"></i> File New Application
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Type</th>
                        <th>Client Name</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $application): ?>
                    <tr>
                        <td>#<?php echo $application['id']; ?></td>
                        <td><?php echo htmlspecialchars($application['request_purpose']); ?></td>
                        <td><?php echo htmlspecialchars($application['client_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($application['application_date'])); ?></td>
                        <td>₱<?php echo number_format($application['amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($application['status'] ?? 'pending'); ?>">
                                <?php echo $application['status'] ?? 'Pending'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="view_user_application.php?id=<?php echo $application['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
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
        
        // Check width on load
        checkWidth();
        
        // Check width on resize
        window.addEventListener("resize", checkWidth);
    });
</script>
</body>
</html>