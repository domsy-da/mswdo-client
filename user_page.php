<?php
@include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
header('location:index.php');
exit();
}


$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Unknown User";


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

}

// Get count of pending applications for this user
$pending_applications = 0;
try {
    $sql = "SELECT COUNT(*) as count FROM application_requests WHERE user_id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $pending_applications = $row['count'];
    }
    $stmt->close();
} catch (Exception $e) {
    // Handle error silently
}

// Get count of upcoming programs
$upcoming_programs = 0;
try {
    $sql = "SELECT COUNT(*) as count FROM programs WHERE status = 'upcoming'";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $upcoming_programs = $row['count'];
    }
} catch (Exception $e) {
    // Handle error silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MSWDO BMS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

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
    
    /* Sidebar Styles*/
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
    }
    
    /* Dashboard Styles */
    .welcome-card {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding: 30px;
        text-align: center;
        margin-bottom: 30px;
        border-top: 5px solid var(--secondary-color);
        transition: all 0.3s ease;
    }
    
    .welcome-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .welcome-card h2 {
        color: var(--secondary-color);
        margin-bottom: 15px;
        font-weight: 600;
    }
    
    .welcome-card p {
        color: #6c757d;
        margin-bottom: 20px;
        font-size: 1.1rem;
    }
    
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .stat-card {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: all 0.3s ease;
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
    
    .quick-actions {
        margin-top: 30px;
    }
    
    .quick-actions h3 {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--dark-text);
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        text-decoration: none;
        color: var(--dark-text);
        border: 1px solid var(--border-color);
    }
    
    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-color: var(--secondary-color);
        color: var(--secondary-color);
    }
    
    .action-btn i {
        font-size: 2rem;
        margin-bottom: 10px;
        color: var(--secondary-color);
    }
    
    .action-btn span {
        font-weight: 500;
        text-align: center;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 1200px) {
        .stats-container {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }
        
        .action-buttons {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        }
    }
    
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
        
        .welcome-card h2 {
            font-size: 1.5rem;
        }
        
        .welcome-card p {
            font-size: 1rem;
        }
    }
    
    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
        
        .stat-card {
            padding: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
        
        .stat-info h3 {
            font-size: 1.5rem;
        }
        
        .action-buttons {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
        
        .action-btn {
            padding: 15px;
        }
        
        .action-btn i {
            font-size: 1.8rem;
        }
    }
    
    @media (max-width: 576px) {
        .navbar-brand h1 {
            display: none;
        }
        
        .stats-container {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            grid-template-columns: 1fr 1fr;
        }
        
        .content {
            padding: 15px;
        }
        
        .welcome-card {
            padding: 20px;
        }
        
        .user-profile span {
            display: none;
        }
    }
    
    @media (max-width: 400px) {
        .action-buttons {
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
        <li class="active"><a href="user_page.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="programs.php"><i class="fas fa-calendar-alt"></i> Programs</a></li>
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
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
    
    <div class="content">
        <div class="welcome-card">
            <h2>Welcome to MSWDO Beneficiary Management System, <?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>!</h2>
           
        </div>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pending_applications; ?></h3>
                    <p>Pending Applications</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $upcoming_programs; ?></h3>
                    <p>Upcoming Programs</p>
                </div>
            </div>
            
            <!-- Add this inside the stats-container div, after the Upcoming Programs card -->
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stat-info">
                    <?php
                    // Get count of all applications for this user
                    $all_applications = 0;
                    try {
                        $sql = "SELECT COUNT(*) as count FROM application_requests WHERE user_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $all_applications = $row['count'];
                        }
                        $stmt->close();
                    } catch (Exception $e) {
                        // Handle error silently
                    }
                    ?>
                    <h3><?php echo $all_applications; ?></h3>
                    <p>Application History</p>
                </div>
            </div>
            
            </div>
        </div>
        
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="#" class="action-btn" data-bs-toggle="modal" data-bs-target="#fileApplicationModal">
                    <i class="fas fa-file-alt"></i>
                    <span>File Application</span>
                </a>
                <a href="programs.php" class="action-btn">
                    <i class="fas fa-calendar-alt"></i>
                    <span>View Programs</span>
                </a>
                <a href="application_history.php" class="action-btn">
                    <i class="fas fa-history"></i>
                    <span>Application History</span>
                </a>

            </div>
        </div>
    </div>
</div>

<!-- File Application Modal -->
<div class="modal fade" id="fileApplicationModal" tabindex="-1" aria-labelledby="fileApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileApplicationModalLabel">Request Assistance Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="file_application.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="modal-body">
                    <!-- Client Information Section -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-user me-2"></i>Client Information</h4>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="request_purpose" class="form-label">Request Purpose <span class="text-danger">*</span></label>
                                <select class="form-select" id="request_purpose" name="request_purpose" required>
                                    <option value="">-- Select Request Type --</option>
                                    <option value="Medical Assistance">Medical Assistance</option>
                                    <option value="Burial Assistance">Burial Assistance</option>
                                    <option value="Financial Assistance">Financial Assistance</option>
                                    <option value="Educational Assistance">Educational Assistance</option>
                                    <option value="Food Assistance">Food Assistance</option>
                                </select>
                                <div class="invalid-feedback">Please select a request purpose.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="application_date" class="form-label">Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="application_date" name="application_date" required>
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <div class="invalid-feedback">Please select a date.</div>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="client_name" class="form-label">Name of Client <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="client_name" name="client_name" required>
                                <div class="invalid-feedback">Please enter client name.</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="client_age" class="form-label">Age <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="client_age" name="client_age" required>
                                <div class="invalid-feedback">Please enter age.</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="client_gender" class="form-label">Gender/Sex <span class="text-danger">*</span></label>
                                <select class="form-select" id="client_gender" name="client_gender" required>
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback">Please select gender.</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="client_civil_status" class="form-label">Civil Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="client_civil_status" name="client_civil_status" required>
                                    <option value="">Select</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Divorced">Divorced</option>
                                    <option value="Separated">Separated</option>
                                </select>
                                <div class="invalid-feedback">Please select civil status.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="client_birthday" class="form-label">Birthday <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="client_birthday" name="client_birthday" required>
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <div class="invalid-feedback">Please select birthday.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="client_birthplace" class="form-label">Birthplace <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="client_birthplace" name="client_birthplace" required>
                                <div class="invalid-feedback">Please enter birthplace.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="client_education" class="form-label">Educational Attainment <span class="text-danger">*</span></label>
                                <select class="form-select" id="client_education" name="client_education" required>
                                    <option value="">Select</option>
                                    <option value="Elementary">Elementary</option>
                                    <option value="High School">High School</option>
                                    <option value="Vocational">Vocational</option>
                                    <option value="College">College</option>
                                    <option value="Post Graduate">Post Graduate</option>
                                    <option value="None">None</option>
                                </select>
                                <div class="invalid-feedback">Please select educational attainment.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="client_address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="client_address" name="client_address" required>
                                <div class="invalid-feedback">Please enter address.</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Patient Information Section -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-user-injured me-2"></i>Patient Information</h4>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="request_type" class="form-label">Request Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="request_type" name="request_type" required>
                                    <option value="">-- Select Request Type --</option>
                                    <option value="Medical Assistance">Medical Assistance</option>
                                    <option value="Burial Assistance">Burial Assistance</option>
                                    <option value="Financial Assistance">Financial Assistance</option>
                                    <option value="Educational Assistance">Educational Assistance</option>
                                    <option value="Food Assistance">Food Assistance</option>
                                </select>
                                <div class="invalid-feedback">Please select a request type.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="relation_to_patient" class="form-label">Relation to patient</label>
                                <select class="form-select" id="relation_to_patient" name="relation_to_patient">
                                    <option value="">--please choose--</option>
                                    <option value="Self">Self</option>
                                    <option value="Spouse">Spouse</option>
                                    <option value="Child">Child</option>
                                    <option value="Parent">Parent</option>
                                    <option value="Sibling">Sibling</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6" id="relation_other_container" style="display: none;">
                                <label for="relation_other" class="form-label">Specify relation</label>
                                <input type="text" class="form-control" id="relation_other" name="relation_other">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="patient_name" class="form-label">Name of Patient</label>
                                <input type="text" class="form-control" id="patient_name" name="patient_name">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="patient_birthday" class="form-label">Birthday</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="patient_birthday" name="patient_birthday">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="patient_age" class="form-label">Age</label>
                                <input type="number" class="form-control" id="patient_age" name="patient_age">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="patient_gender" class="form-label">Gender/Sex</label>
                                <select class="form-select" id="patient_gender" name="patient_gender">
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="patient_civil_status" class="form-label">Civil Status</label>
                                <select class="form-select" id="patient_civil_status" name="patient_civil_status">
                                    <option value="">Select</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Divorced">Divorced</option>
                                    <option value="Separated">Separated</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="patient_birthplace" class="form-label">Birthplace</label>
                                <input type="text" class="form-control" id="patient_birthplace" name="patient_birthplace">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="patient_education" class="form-label">Educational Attainment</label>
                                <select class="form-select" id="patient_education" name="patient_education">
                                    <option value="">Select</option>
                                    <option value="Elementary">Elementary</option>
                                    <option value="High School">High School</option>
                                    <option value="Vocational">Vocational</option>
                                    <option value="College">College</option>
                                    <option value="Post Graduate">Post Graduate</option>
                                    <option value="None">None</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="patient_occupation" class="form-label">Occupation</label>
                                <input type="text" class="form-control" id="patient_occupation" name="patient_occupation">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="patient_religion" class="form-label">Religion</label>
                                <input type="text" class="form-control" id="patient_religion" name="patient_religion">
                            </div>
                            
                            <div class="col-md-12">
                                <label for="patient_address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="patient_address" name="patient_address">
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="same_address" name="same_address">
                                    <label class="form-check-label" for="same_address">Same as Client Address</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Information Section -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-info-circle me-2"></i>Additional Information</h4>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="diagnosis" class="form-label">Diagnosis</label>
                                <input type="text" class="form-control" id="diagnosis" name="diagnosis">
                            </div>
                            
                            <div class="col-md-12">
                                <label for="id_type" class="form-label">ID Type to be attached</label>
                                <select class="form-select" id="id_type" name="id_type">
                                    <option value="">Select an ID Type</option>
                                    <option value="National ID">National ID</option>
                                    <option value="Driver's License">Driver's License</option>
                                    <option value="Passport">Passport</option>
                                    <option value="Voter's ID">Voter's ID</option>
                                    <option value="SSS ID">SSS ID</option>
                                    <option value="PhilHealth ID">PhilHealth ID</option>
                                    <option value="Barangay ID">Barangay ID</option>
                                </select>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="d-flex gap-3 mt-3">
                                    <button type="button" class="btn btn-success flex-grow-1" id="scan-btn">
                                        <i class="fas fa-camera me-2"></i> Scan
                                    </button>
                                    <button type="button" class="btn btn-primary flex-grow-1" id="upload-btn">
                                        <i class="fas fa-upload me-2"></i> Upload ID
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-2"></i> Submit Application
                    </button>
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

// Set today's date in the application date field when modal is shown
document.getElementById('fileApplicationModal').addEventListener('show.bs.modal', function () {
    const today = new Date();
    const formattedDate = today.toISOString().substr(0, 10);
    document.getElementById("application_date").value = formattedDate;
});

// Handle relation to patient dropdown
document.getElementById('relation_to_patient').addEventListener('change', function() {
    if (this.value === "Other") {
        document.getElementById('relation_other_container').style.display = "block";
    } else {
        document.getElementById('relation_other_container').style.display = "none";
    }
});

// Handle same address checkbox
document.getElementById('same_address').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('patient_address').value = document.getElementById('client_address').value;
        document.getElementById('patient_address').disabled = true;
    } else {
        document.getElementById('patient_address').disabled = false;
    }
});

// Sync request purpose and request type
document.getElementById('request_purpose').addEventListener('change', function() {
    document.getElementById('request_type').value = this.value;
});

// Handle scan button
document.getElementById('scan-btn').addEventListener('click', function() {
    alert("Scan functionality would be implemented here.");
});

// Handle upload button
document.getElementById('upload-btn').addEventListener('click', function() {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.style.display = 'none';
    
    document.body.appendChild(fileInput);
    fileInput.click();
    
    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            const fileName = fileInput.files[0].name;
            alert("Selected file: " + fileName);
        }
        document.body.removeChild(fileInput);
    });
});

// Form validation
const forms = document.querySelectorAll('.needs-validation');
Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
});
</script>
</body>
</html>