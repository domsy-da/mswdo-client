<?php
@include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:index.php');
    exit();
}

// Check if application ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('location:admin_services.php');
    exit();
}

$application_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Unknown User";
$is_admin = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';

// Fetch user details
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

// Fetch application details
$application = null;
$error_msg = '';

try {
    $sql = "SELECT * FROM application_requests WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $error_msg = "Application not found.";
    } else {
        $application = $result->fetch_assoc();
        
        // If not admin, check if the application belongs to the logged-in user
        if (!$is_admin && $application['user_id'] != $user_id) {
            header('location:user_page.php');
            exit();
        }
    }
    $stmt->close();
} catch (Exception $e) {
    $error_msg = "Error fetching application details: " . $e->getMessage();
}

// Fetch applicant name if admin is viewing
$applicant_name = "";
if ($is_admin && $application && isset($application['user_id'])) {
    try {
        $sql = "SELECT name FROM user_form WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $application['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $applicant_name = $row['name'];
        }
        $stmt->close();
    } catch (Exception $e) {
        // Ignore error
    }
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
        
        /* Application View Styles */
        .application-header {
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid var(--secondary-color);
        }
        
        .application-header h2 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .application-header p {
            margin-bottom: 5px;
            color: var(--dark-text);
        }
        
        .status-badge {
            padding: 5px 15px;
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
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent-color);
            display: inline-block;
        }
        
        .info-section {
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-row {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .info-value {
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
        
        @media (max-width: 576px) {
            .navbar-brand h1 {
                display: none;
            }
            
            .application-header {
                padding: 15px;
            }
            
            .info-section {
                padding: 15px;
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
            <?php if ($is_admin): ?>
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin_services.php"><i class="fas fa-hands-helping"></i> Services</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_programs.php"><i class="fas fa-calendar-alt"></i> Programs</a></li>
            <?php else: ?>
            <li><a href="user_page.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="programs.php"><i class="fas fa-calendar-alt"></i> Programs</a></li>
            <li><a href="file_application.php"><i class="fas fa-file-alt"></i> File Application</a></li>
            <?php endif; ?>
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
                    <img src="img\adminprof.png" alt="Admin">
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
                <li class="breadcrumb-item"><a href="<?php echo $is_admin ? 'admin_dashboard.php' : 'user_page.php'; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo $is_admin ? 'admin_services.php' : 'user_page.php'; ?>">
                    <?php echo $is_admin ? 'Services' : 'My Applications'; ?>
                </a></li>
                <li class="breadcrumb-item active">View Application</li>
            </ol>
        </nav>
        
        <div class="content">
            <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
            </div>
            <?php elseif ($application): ?>
            
            <div class="application-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2>Application #<?php echo $application['id']; ?></h2>
                        <p><strong>Request Purpose:</strong> <?php echo htmlspecialchars($application['request_purpose']); ?></p>
                        <p><strong>Date Submitted:</strong> <?php echo date('F d, Y', strtotime($application['application_date'])); ?></p>
                        <?php if ($is_admin && !empty($applicant_name)): ?>
                        <p><strong>Submitted By:</strong> <?php echo htmlspecialchars($applicant_name); ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                            <?php echo $application['status']; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Client Information Section -->
            <h4 class="section-title"><i class="fas fa-user me-2"></i>Client Information</h4>
            <div class="info-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Name of Client</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['client_name']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Age</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['client_age']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Gender/Sex</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['client_gender']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Civil Status</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['client_civil_status']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Birthday</div>
                            <div class="info-value"><?php echo date('F d, Y', strtotime($application['client_birthday'])); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Birthplace</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['client_birthplace']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Educational Attainment</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['client_education']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['client_address']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Patient Information Section -->
            <h4 class="section-title"><i class="fas fa-user-injured me-2"></i>Patient Information</h4>
            <div class="info-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Request Type</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['request_type']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Relation to Patient</div>
                            <div class="info-value">
                                <?php 
                                    echo htmlspecialchars($application['relation_to_patient']);
                                    if ($application['relation_to_patient'] === 'Other' && !empty($application['relation_other'])) {
                                        echo ' (' . htmlspecialchars($application['relation_other']) . ')';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Name of Patient</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['patient_name'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Patient Age</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['patient_age'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Patient Gender</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['patient_gender'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Patient Civil Status</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['patient_civil_status'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Patient Birthday</div>
                            <div class="info-value">
                                <?php echo !empty($application['patient_birthday']) ? date('F d, Y', strtotime($application['patient_birthday'])) : 'N/A'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Patient Birthplace</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['patient_birthplace'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Patient Education</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['patient_education'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Patient Occupation</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['patient_occupation'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Patient Religion</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['patient_religion'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Patient Address</div>
                            <div class="info-value">
                                <?php 
                                    if ($application['same_as_client_address']) {
                                        echo htmlspecialchars($application['client_address']) . ' (Same as Client)';
                                    } else {
                                        echo htmlspecialchars($application['patient_address'] ?: 'N/A');
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information Section -->
            <h4 class="section-title"><i class="fas fa-info-circle me-2"></i>Additional Information</h4>
            <div class="info-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Amount</div>
                            <div class="info-value">
                                <?php echo !empty($application['amount']) ? '₱' . number_format($application['amount'], 2) : 'N/A'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Diagnosis</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['diagnosis'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">ID Type</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['id_type'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($is_admin): ?>
            <!-- Admin Remarks Section (Only visible to admin) -->
            <h4 class="section-title"><i class="fas fa-comment-alt me-2"></i>Admin Remarks</h4>
            <div class="info-section">
                <div class="row">
                    <div class="col-12">
                        <div class="info-row">
                            <div class="info-label">Remarks</div>
                            <div class="info-value">
                                <?php echo !empty($application['admin_remarks']) ? nl2br(htmlspecialchars($application['admin_remarks'])) : 'No remarks added.'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Admin Actions -->
            <div class="d-flex justify-content-end mt-4">
                <a href="update_application.php?id=<?php echo $application['id']; ?>" class="btn btn-primary me-2">
                    <i class="fas fa-edit me-2"></i> Update Status
                </a>
                <a href="admin_services.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Services
                </a>
            </div>
            <?php else: ?>
            <!-- User Actions -->
            <div class="d-flex justify-content-end mt-4">
                <a href="user_page.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                </a>
            </div>
            <?php endif; ?>
            
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
                contentWrapper.classList.toggle("full-width");
            });
            
            // Handle responsive behavior
            function checkWidth() {
                if (window.innerWidth < 992) {
                    sidebar.classList.add("collapsed");
                    contentWrapper.classList.add("full-width");
                } else {
                    sidebar.classList.remove("collapsed");
                    contentWrapper.classList.remove("full-width");
                }
            }
            
            checkWidth();
            window.addEventListener("resize", checkWidth);
        });
    </script>
</body>
</html>

