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

// Set default filter values
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Build the SQL query based on filters
$sql_conditions = [];
$sql_params = [];
$param_types = "";

if ($status_filter != 'all') {
    $sql_conditions[] = "status = ?";
    $sql_params[] = $status_filter;
    $param_types .= "s";
}

if ($category_filter != 'all') {
    $sql_conditions[] = "category = ?";
    $sql_params[] = $category_filter;
    $param_types .= "s";
}

if (!empty($search_query)) {
    $sql_conditions[] = "(title LIKE ? OR location LIKE ? OR description LIKE ?)";
    $search_term = "%$search_query%";
    $sql_params[] = $search_term;
    $sql_params[] = $search_term;
    $sql_params[] = $search_term;
    $param_types .= "sss";
}

$sql = "SELECT * FROM programs";

if (!empty($sql_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $sql_conditions);
}

$sql .= " ORDER BY date DESC";

// Fetch programs based on filters
$programs = [];
try {
    if (!empty($sql_params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($param_types, ...$sql_params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $programs[] = $row;
        }
    }
} catch (Exception $e) {
    // Handle database errors
}

// Get program categories for filter dropdown
$categories = [];
$category_query = "SELECT DISTINCT category FROM programs ORDER BY category";
$category_result = $conn->query($category_query);
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs - MSWDO BMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <style>
            :root {
            --primary-color: rgb(7, 54, 27);
            --secondary-color:rgb(7, 54, 27);
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
        
        /* Programs Styles */
        .programs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .programs-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin: 0;
        }
        
        .programs-filter {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .filter-label {
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .filter-select {
            padding: 8px 15px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background-color: #fff;
            color: var(--dark-text);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(12, 92, 47, 0.1);
        }
        
        .search-box {
            position: relative;
        }
        
        .search-input {
            padding: 8px 15px 8px 40px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background-color: #fff;
            color: var(--dark-text);
            font-size: 0.9rem;
            width: 250px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(12, 92, 47, 0.1);
            width: 300px;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .program-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .program-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: var(--secondary-color);
        }
        
        .program-image {
            height: 180px;
            overflow: hidden;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .program-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .program-card:hover .program-image img {
            transform: scale(1.05);
        }
        
        .program-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .program-category {
            display: inline-block;
            padding: 5px 12px;
            background-color: rgba(12, 92, 47, 0.1);
            color: var(--secondary-color);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .program-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .program-details {
            margin-bottom: 15px;
            flex-grow: 1;
        }
        
        .program-detail {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .program-detail i {
            width: 20px;
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .program-description {
            color: #6c757d;
            margin-bottom: 20px;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .program-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }
        
        .program-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
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
        
        .program-action {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-view {
            padding: 8px 15px;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-view:hover {
            background-color: #0a4a26;
            transform: translateY(-2px);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .empty-icon {
            font-size: 4rem;
            color: #e9ecef;
            margin-bottom: 20px;
        }
        
        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
        }
        
        .empty-description {
            color: #6c757d;
            max-width: 500px;
            margin: 0 auto 20px;
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
            
            .programs-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .programs-filter {
                flex-wrap: wrap;
                width: 100%;
            }
            
            .search-input, .search-input:focus {
                width: 100%;
            }
            
            .search-box {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand h1 {
                display: none;
            }
            
            .programs-grid {
                grid-template-columns: 1fr;
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
            <li><a href="application_history.php"><i class="fas fa-history"></i> Application History</a></li>
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
                <li class="breadcrumb-item active">Programs</li>
            </ol>
        </nav>
        
        <div class="content">
            <div class="programs-header">
                <h2 class="programs-title">Scheduled Programs</h2>
                <div class="programs-filter">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <form action="" method="get">
                            <input type="text" name="search" class="search-input" placeholder="Search programs..." value="<?php echo htmlspecialchars($search_query); ?>">
                        </form>
                    </div>
                    <div>
                        <span class="filter-label">Filter by:</span>
                        <form action="" method="get" class="d-inline">
                            <select class="filter-select" name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Programs</option>
                                <option value="upcoming" <?php echo $status_filter == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="ongoing" <?php echo $status_filter == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <?php if (!empty($search_query)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                            <?php endif; ?>
                            <?php if ($category_filter != 'all'): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
                            <?php endif; ?>
                        </form>
                    </div>
                    <div>
                        <span class="filter-label">Category:</span>
                        <form action="" method="get" class="d-inline">
                            <select class="filter-select" name="category" onchange="this.form.submit()">
                                <option value="all" <?php echo $category_filter == 'all' ? 'selected' : ''; ?>>All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $category_filter == $category ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($search_query)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                            <?php endif; ?>
                            <?php if ($status_filter != 'all'): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php if (empty($programs)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3 class="empty-title">No Programs Available</h3>
                <p class="empty-description">There are currently no scheduled programs. Please check back later as the administrator will add programs soon.</p>
            </div>
            <?php else: ?>
            <div class="programs-grid">
                <?php foreach ($programs as $program): ?>
                <div class="program-card">
                    <div class="program-image">
                        <i class="fas fa-calendar-alt fa-4x text-secondary"></i>
                    </div>
                    <div class="program-content">
                        <span class="program-category"><?php echo htmlspecialchars($program['category']); ?></span>
                        <h3 class="program-title"><?php echo htmlspecialchars($program['title']); ?></h3>
                        <div class="program-details">
                            <div class="program-detail">
                                <i class="fas fa-calendar-day"></i>
                                <span><?php echo date('M d, Y', strtotime($program['date'])); ?></span>
                            </div>
                            
                            <div class="program-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($program['location']); ?></span>
                            </div>
                        </div>
                        <p class="program-description">
                            <?php 
                            $desc = htmlspecialchars($program['description']);
                            echo (strlen($desc) > 150) ? substr($desc, 0, 150) . '...' : $desc; 
                            ?>
                        </p>
                        <div class="program-footer">
                            <span class="program-status status-<?php echo strtolower($program['status']); ?>">
                                <?php echo ucfirst($program['status']); ?>
                            </span>
                            <div class="program-action">
                                <a href="view_program.php?id=<?php echo $program['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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

