<?php
@include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:index.php');
    exit();
}

// Get the user's information from the session
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Unknown User";

// Fetch user details from the database
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
    // If there's an error with the database query, we'll use the session name
}

// Check if program ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('location:programs.php');
    exit();
}

$program_id = $_GET['id'];

// Fetch program details from the database
$program = null;
try {
    $sql = "SELECT * FROM programs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $program = $result->fetch_assoc();
    } else {
        // Program not found, redirect to programs page
        header('location:programs.php');
        exit();
    }
    
    $stmt->close();
} catch (Exception $e) {
    // Handle database errors
    header('location:programs.php');
    exit();
}

// Format dates
$formatted_date = date('F d, Y', strtotime($program['date']));
$created_at = date('F d, Y', strtotime($program['created_at']));
$updated_at = !empty($program['updated_at']) ? date('F d, Y', strtotime($program['updated_at'])) : 'N/A';
$completion_date = !empty($program['completion_date']) ? date('F d, Y', strtotime($program['completion_date'])) : 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($program['title']); ?> - MSWDO BMS</title>
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
            margin-bottom: 20px;
        }
        
        /* Program Details Styles */
        .program-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .program-title-section {
            flex: 1;
            min-width: 300px;
        }
        
        .program-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .program-category {
            display: inline-block;
            padding: 5px 15px;
            background-color: rgba(12, 92, 47, 0.1);
            color: var(--secondary-color);
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .program-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .status-upcoming {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .status-ongoing {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .status-completed {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
        
        .program-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .program-meta-item {
            display: flex;
            align-items: center;
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        .program-meta-item i {
            margin-right: 8px;
            color: var(--secondary-color);
        }
        
        .program-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-back {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
            color: white;
        }
        
        .program-content-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .program-description {
            color: #495057;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 20px;
            white-space: pre-line;
        }
        
        .program-details-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .program-details-item {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: 15px;
        }
        
        .program-details-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .program-details-label {
            width: 180px;
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .program-details-value {
            flex: 1;
            color: #6c757d;
        }
        
        .program-image-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .program-image {
            width: 100%;
            max-width: 500px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .program-icon {
            font-size: 8rem;
            color: #e9ecef;
            margin: 20px 0;
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
            
            .program-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .program-title {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand h1 {
                display: none;
            }
            
            .program-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .program-details-item {
                flex-direction: column;
            }
            
            .program-details-label {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .program-title {
                font-size: 1.5rem;
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
            <li class="active"><a href="programs.php"><i class="fas fa-calendar-alt"></i> Programs</a></li>
            <li><a href="file_application.php"><i class="fas fa-file-alt"></i> File Application</a></li>
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
                        <img src="/useradmin/img/prof.jpg" alt="User">
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
                <li class="breadcrumb-item"><a href="programs.php">Programs</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($program['title']); ?></li>
            </ol>
        </nav>
        
        <div class="content">
            <div class="program-header">
                <div class="program-title-section">
                    <h1 class="program-title"><?php echo htmlspecialchars($program['title']); ?></h1>
                    <div>
                        <span class="program-category"><?php echo htmlspecialchars($program['category']); ?></span>
                        <span class="program-status status-<?php echo strtolower($program['status']); ?>">
                            <?php echo ucfirst($program['status']); ?>
                        </span>
                    </div>
                    <div class="program-meta mt-3">
                        <div class="program-meta-item">
                            <i class="fas fa-calendar-day"></i>
                            <span><?php echo $formatted_date; ?></span>
                        </div>
                        <div class="program-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($program['location']); ?></span>
                        </div>
                        <div class="program-meta-item">
                            <i class="fas fa-user"></i>
                            <span>Posted by: MSWDO Staff</span>
                        </div>
                    </div>
                </div>
                <div class="program-actions">
                    <a href="programs.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Programs
                    </a>
                </div>
            </div>
            
            <div class="program-image-container">
                <i class="fas fa-calendar-alt program-icon"></i>
            </div>
            
            <div class="program-content-section">
                <h2 class="section-title">Program Description</h2>
                <div class="program-description">
                    <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                </div>
            </div>
            
            <div class="program-content-section">
                <h2 class="section-title">Program Details</h2>
                <ul class="program-details-list">
                    <li class="program-details-item">
                        <div class="program-details-label">Program ID</div>
                        <div class="program-details-value">#<?php echo $program['id']; ?></div>
                    </li>
                    <li class="program-details-item">
                        <div class="program-details-label">Category</div>
                        <div class="program-details-value"><?php echo htmlspecialchars($program['category']); ?></div>
                    </li>
                    <li class="program-details-item">
                        <div class="program-details-label">Status</div>
                        <div class="program-details-value">
                            <span class="program-status status-<?php echo strtolower($program['status']); ?>">
                                <?php echo ucfirst($program['status']); ?>
                            </span>
                        </div>
                    </li>
                    <li class="program-details-item">
                        <div class="program-details-label">Date</div>
                        <div class="program-details-value"><?php echo $formatted_date; ?></div>
                    </li>
                    <li class="program-details-item">
                        <div class="program-details-label">Location</div>
                        <div class="program-details-value"><?php echo htmlspecialchars($program['location']); ?></div>
                    </li>
                    <?php if ($program['status'] == 'completed' && !empty($program['completion_date'])): ?>
                    <li class="program-details-item">
                        <div class="program-details-label">Completion Date</div>
                        <div class="program-details-value"><?php echo $completion_date; ?></div>
                    </li>
                    <?php endif; ?>
                    <li class="program-details-item">
                        <div class="program-details-label">Created On</div>
                        <div class="program-details-value"><?php echo $created_at; ?></div>
                    </li>
                    <?php if (!empty($program['updated_at']) && $program['updated_at'] != $program['created_at']): ?>
                    <li class="program-details-item">
                        <div class="program-details-label">Last Updated</div>
                        <div class="program-details-value"><?php echo $updated_at; ?></div>
                    </li>
                    <?php endif; ?>
                    <li class="program-details-item">
                        <div class="program-details-label">Posted By</div>
                        <div class="program-details-value">MSWDO Staff</div>
                    </li>
                </ul>
            </div>
            
            <?php if ($program['status'] == 'upcoming' || $program['status'] == 'ongoing'): ?>
            <div class="program-content-section">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    This program is currently <?php echo strtolower($program['status']); ?>. Check back later for updates or contact the MSWDO office for more information.
                </div>
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

