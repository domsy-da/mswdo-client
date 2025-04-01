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

// Check if application ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('location:application_history.php');
    exit();
}

$application_id = $_GET['id'];
$application = null;

// Fetch application details
try {
    $sql = "SELECT * FROM application_requests WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $application_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $application = $result->fetch_assoc();
    } else {
        // Application not found or doesn't belong to this user
        header('location:application_history.php');
        exit();
    }
    
    $stmt->close();
} catch (Exception $e) {
    // Handle error
    header('location:application_history.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application - MSWDO BMS</title>
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
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
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
        
        /* Application Details */
        .application-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .application-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin: 0;
        }
        
        .detail-section {
            margin-bottom: 30px;
        }
        
        .detail-section h4 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
            display: inline-block;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .detail-label {
            width: 200px;
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .detail-value {
            flex: 1;
            color: #6c757d;
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
        
        @media (max-width: 768px) {
            .detail-label {
                width: 150px;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand h1 {
                display: none;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .detail-value {
                width: 100%;
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
            <li class="breadcrumb-item"><a href="application_history.php">Application History</a></li>
            <li class="breadcrumb-item active">View Application</li>
        </ol>
    </nav>
    
    <div class="content">
        <div class="application-header">
            <h2 class="application-title">Application Details</h2>
            <span class="status-badge status-<?php echo strtolower($application['status'] ?? 'pending'); ?>">
                <?php echo $application['status'] ?? 'Pending'; ?>
            </span>
        </div>
        
        <div class="detail-section">
            <h4><i class="fas fa-info-circle me-2"></i>Basic Information</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Application ID:</div>
                        <div class="detail-value">#<?php echo $application['id']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Service Type:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['request_purpose']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Date Applied:</div>
                        <div class="detail-value"><?php echo date('F d, Y', strtotime($application['application_date'])); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Amount:</div>
                        <div class="detail-value">₱<?php echo number_format($application['amount'], 2); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Request Type:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['request_type']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Diagnosis:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['diagnosis'] ?: 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="detail-section">
            <h4><i class="fas fa-user me-2"></i>Client Information</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Client Name:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['client_name']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Age:</div>
                        <div class="detail-value"><?php echo $application['client_age']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Gender:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['client_gender']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Civil Status:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['client_civil_status']); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Birthday:</div>
                        <div class="detail-value"><?php echo date('F d, Y', strtotime($application['client_birthday'])); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Birthplace:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['client_birthplace']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Education:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['client_education']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Address:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['client_address']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($application['patient_name'])): ?>
        <div class="detail-section">
            <h4><i class="fas fa-user-injured me-2"></i>Patient Information</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Patient Name:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['patient_name']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Relation:</div>
                        <div class="detail-value">
                            <?php 
                            echo htmlspecialchars($application['relation_to_patient']);
                            if ($application['relation_to_patient'] == 'Other') {
                                echo ' (' . htmlspecialchars($application['relation_other']) . ')';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Age:</div>
                        <div class="detail-value"><?php echo $application['patient_age']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Gender:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['patient_gender']); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Birthday:</div>
                        <div class="detail-value"><?php echo !empty($application['patient_birthday']) ? date('F d, Y', strtotime($application['patient_birthday'])) : 'N/A'; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Civil Status:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['patient_civil_status'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Occupation:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['patient_occupation'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Address:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['patient_address'] ?: 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <a href="application_history.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to History
            </a>
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
        
        // Check width on load
        checkWidth();
        
        // Check width on resize
        window.addEventListener("resize", checkWidth);
    });
</script>
</body>
</html>