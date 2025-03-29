<?php
@include 'config.php';

session_start();

if(!isset($_SESSION['user_name'])){
   header('location:index.php');
}

// Get the user's name from the session
$user_name = $_SESSION['user_name'];

// Handle form submission
if (isset($_POST['submit'])) {
   $client_name = $_POST['client_name'];
   $client_address = $_POST['client_address'];
   $client_contact = $_POST['client_contact'];
   $client_email = $_POST['client_email'];

   // Insert the data into the database
   $insert_query = "INSERT INTO clients (name, address, contact, email) VALUES ('$client_name', '$client_address', '$client_contact', '$client_email')";

   if (mysqli_query($conn, $insert_query)) {
      $success_msg = "Client added successfully!";
   } else {
      $error_msg = "Error: " . mysqli_error($conn);
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSWDO Gloria - Add Beneficiary</title>
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
        
        /* Form Styles */
        .form-title {
            color: var(--primary-color);
            font-weight: 600;
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
            <li class="active"><a href="add_client.php"><i class="fas fa-user-plus"></i> Add Beneficiary</a></li>
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
                <li class="breadcrumb-item active">Add Beneficiary</li>
            </ol>
        </nav>
        
        <!-- Add Beneficiary Form -->
        <div class="content">
            <div class="row mb-4">
                <div class="col-md-8">
                    <h2 class="form-title">Add New Beneficiary</h2>
                    <p class="text-muted">Enter the beneficiary details below</p>
                </div>
                <div class="col-md-4 text-end">
                    <img src="/useradmin/img/mswdologo.png" alt="MSWDO Logo" class="img-fluid" style="max-width: 100px;">
                </div>
            </div>
            
            <?php if (isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <form action="" method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="client_name" name="client_name" placeholder="Full Name" required>
                            <label for="client_name">Beneficiary Full Name</label>
                            <div class="invalid-feedback">Please enter beneficiary name.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="client_email" name="client_email" placeholder="Email Address" required>
                            <label for="client_email">Email Address</label>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="client_contact" name="client_contact" placeholder="Contact Number" required>
                            <label for="client_contact">Contact Number</label>
                            <div class="invalid-feedback">Please enter contact number.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="client_gender" name="client_gender" required>
                                <option value="" selected disabled>Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            <label for="client_gender">Gender</label>
                            <div class="invalid-feedback">Please select gender.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="client_birthday" name="client_birthday" required>
                            <label for="client_birthday">Date of Birth</label>
                            <div class="invalid-feedback">Please select date of birth.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="client_civil_status" name="client_civil_status" required>
                                <option value="" selected disabled>Select status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Separated">Separated</option>
                            </select>
                            <label for="client_civil_status">Civil Status</label>
                            <div class="invalid-feedback">Please select civil status.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="client_address" name="client_address" placeholder="Address" style="height: 100px" required></textarea>
                            <label for="client_address">Complete Address</label>
                            <div class="invalid-feedback">Please enter address.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="client_program" name="client_program">
                                <option value="" selected disabled>Select program (optional)</option>
                                <option value="Educational Assistance">Educational Assistance</option>
                                <option value="Medical Assistance">Medical Assistance</option>
                                <option value="Food Assistance">Food Assistance</option>
                                <option value="Financial Assistance">Financial Assistance</option>
                                <option value="Senior Citizen Program">Senior Citizen Program</option>
                            </select>
                            <label for="client_program">Program Enrollment (Optional)</label>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='user_page.php'">
                        <i class="fas fa-times me-2"></i> Cancel
                    </button>
                    <button type="submit" name="submit" class="btn btn-success">
                        <i class="fas fa-user-plus me-2"></i> Add Beneficiary
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="mt-5 text-center text-muted">
            <p>&copy; 2023 MSWDO of Gloria. All rights reserved.</p>
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
            
            // Initial check
            checkWidth();
            
            // Check on resize
            window.addEventListener("resize", checkWidth);
            
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
        });
    </script>
</body>
</html>