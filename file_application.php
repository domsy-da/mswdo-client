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


$error_msg = '';
$success_msg = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$request_purpose = $_POST['request_purpose'] ?? '';
$client_name = $_POST['client_name'] ?? '';
$client_age = $_POST['client_age'] ?? '';
$client_gender = $_POST['client_gender'] ?? '';
$client_civil_status = $_POST['client_civil_status'] ?? '';
$client_birthday = $_POST['client_birthday'] ?? '';
$client_birthplace = $_POST['client_birthplace'] ?? '';
$client_education = $_POST['client_education'] ?? '';
$client_address = $_POST['client_address'] ?? '';
$application_date = $_POST['application_date'] ?? '';
$request_type = $_POST['request_type'] ?? '';
$patient_name = $_POST['patient_name'] ?? '';
$relation_to_patient = $_POST['relation_to_patient'] ?? '';
$relation_other = $_POST['relation_other'] ?? '';
$patient_birthday = $_POST['patient_birthday'] ?? '';
$patient_age = $_POST['patient_age'] ?? '';
$patient_gender = $_POST['patient_gender'] ?? '';
$patient_civil_status = $_POST['patient_civil_status'] ?? '';
$patient_birthplace = $_POST['patient_birthplace'] ?? '';
$patient_education = $_POST['patient_education'] ?? '';
$patient_occupation = $_POST['patient_occupation'] ?? '';
$patient_religion = $_POST['patient_religion'] ?? '';
$patient_address = $_POST['patient_address'] ?? '';
$same_as_client_address = isset($_POST['same_address']) ? 1 : 0;
$amount = $_POST['amount'] ?? '';
$diagnosis = $_POST['diagnosis'] ?? '';
$id_type = $_POST['id_type'] ?? '';

// Handle file upload (if implemented)
$id_file_path = ''; // This would be set if file upload is implemented

// Validate required fields
if (empty($request_purpose) || empty($client_name) || empty($client_age) || 
    empty($client_gender) || empty($client_civil_status) || empty($client_birthday) || 
    empty($client_birthplace) || empty($client_education) || empty($client_address) || 
    empty($application_date) || empty($request_type)) {
    $error_msg = "Please fill in all required fields.";
} else {
    try {
        // Prepare SQL statement
        $sql = "INSERT INTO application_requests (
            user_id, request_purpose, client_name, client_age, client_gender, 
            client_civil_status, client_birthday, client_birthplace, client_education, 
            client_address, application_date, request_type, patient_name, 
            relation_to_patient, relation_other, patient_birthday, patient_age, 
            patient_gender, patient_civil_status, patient_birthplace, patient_education, 
            patient_occupation, patient_religion, patient_address, same_as_client_address, 
            amount, diagnosis, id_type, id_file_path
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ississssssssssssisssssssidsss",
            $user_id, $request_purpose, $client_name, $client_age, $client_gender, 
            $client_civil_status, $client_birthday, $client_birthplace, $client_education, 
            $client_address, $application_date, $request_type, $patient_name, 
            $relation_to_patient, $relation_other, $patient_birthday, $patient_age, 
            $patient_gender, $patient_civil_status, $patient_birthplace, $patient_education, 
            $patient_occupation, $patient_religion, $patient_address, $same_as_client_address, 
            $amount, $diagnosis, $id_type, $id_file_path
        );
        
        if ($stmt->execute()) {
            $success_msg = "Application submitted successfully!";
            // Redirect to a success page or clear form
            // header("Location: application_success.php");
            // exit();
        } else {
            $error_msg = "Error submitting application: " . $stmt->error;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $error_msg = "Database error: " . $e->getMessage();
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply Application - MSWDO BMS</title>
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
    
    /* Form Styles */
    .form-section {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-section:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }
    
    .section-title {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--accent-color);
        display: inline-block;
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
        <li class="active"><a href="file_application.php"><i class="fas fa-file-alt"></i> File Application</a></li>
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
            <li class="breadcrumb-item active">File Application</li>
        </ol>
    </nav>
    
    <!-- Application Form -->
    <div class="content">
        <h2 class="mb-4 text-center fw-bold text-primary">Request Assistance Form</h2>
        
        <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
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
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-paper-plane me-2"></i> Submit Application
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const menuToggle = document.getElementById("menu-toggle");
        const sidebar = document.getElementById("sidebar");
        const contentWrapper = document.getElementById("content-wrapper");
        const relationSelect = document.getElementById("relation_to_patient");
        const relationOtherContainer = document.getElementById("relation_other_container");
        const sameAddressCheckbox = document.getElementById("same_address");
        const clientAddress = document.getElementById("client_address");
        const patientAddress = document.getElementById("patient_address");
        const scanBtn = document.getElementById("scan-btn");
        const uploadBtn = document.getElementById("upload-btn");
        const requestPurpose = document.getElementById("request_purpose");
        const requestType = document.getElementById("request_type");
        
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
        
       
        relationSelect.addEventListener("change", function() {
            if (this.value === "Other") {
                relationOtherContainer.style.display = "block";
            } else {
                relationOtherContainer.style.display = "none";
            }
        });
        
       
        sameAddressCheckbox.addEventListener("change", function() {
            if (this.checked) {
                patientAddress.value = clientAddress.value;
                patientAddress.disabled = true;
            } else {
                patientAddress.disabled = false;
            }
        });
        
    
        const today = new Date();
        const formattedDate = today.toISOString().substr(0, 10);
        document.getElementById("application_date").value = formattedDate;
        
    
        scanBtn.addEventListener("click", function() {
            alert("Scan functionality would be implemented here.");
           
        });
        

        uploadBtn.addEventListener("click", function() {
    
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
        
        // Sync request purpose and request type
        requestPurpose.addEventListener("change", function() {
            requestType.value = this.value;
        });
        
 
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
    });
</script>
</body>
</html>