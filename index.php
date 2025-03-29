<?php
session_start(); // Start the session
@include 'config.php';

// Initialize error array
$error = array();

// Process registration form
if(isset($_POST['register_submit'])){
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = md5($_POST['password']);
   $cpass = md5($_POST['cpassword']);
   $user_type = 'user'; // Set user type to 'user' only

   $select = " SELECT * FROM user_form WHERE email = '$email' && password = '$pass' ";

   $result = mysqli_query($conn, $select);

   if(mysqli_num_rows($result) > 0){
      $error[] = 'user already exist!';
   }else{
      if($pass != $cpass){
         $error[] = 'password not matched!';
      }else{
         $insert = "INSERT INTO user_form(name, email, password, user_type) VALUES('$name','$email','$pass','$user_type')";
         mysqli_query($conn, $insert);
         header('location:index.php');
         exit(); // Added exit after redirect
      }
   }
}

// Process login form
if(isset($_POST['login_submit'])){
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = $_POST['password']; // Get the raw password for admin check
   
   // Check for hardcoded admin credentials
   if(($email === 'Admin' || $email === 'Admin@gmail.com') && $pass === 'password'){
      // Set admin session variables
      $_SESSION['user_id'] = 1; // Using 1 as the admin ID
      $_SESSION['user_name'] = 'Administrator';
      $_SESSION['user_type'] = 'admin';
      
      // Redirect to admin dashboard
      header('location:admin_dashboard.php');
      exit();
   }
   
   // If not admin, proceed with normal user login
   $md5_pass = md5($pass);
   $select = " SELECT * FROM user_form WHERE email = '$email' && password = '$md5_pass' ";

   $result = mysqli_query($conn, $select);

   if(mysqli_num_rows($result) > 0){
      $row = mysqli_fetch_array($result);

      if($row['user_type'] == 'admin'){
         $_SESSION['admin_name'] = $row['name'];
         $_SESSION['user_id'] = $row['id']; // Add this line to set user_id
         header('location:admin_page.php');
         exit(); // Added exit after redirect
      }elseif($row['user_type'] == 'user'){
         $_SESSION['user_name'] = $row['name'];
         $_SESSION['user_id'] = $row['id']; // Add this line to set user_id
         header('location:user_page.php');
         exit(); // Added exit after redirect
      }
   }else{
      $error[] = 'incorrect email or password!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSWDO Gloria - Welcome</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0c5c2f;
            --secondary-color: #1a2e36;
            --accent-color: #4cd964;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --light-text: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header Styles */
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 100;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 60px;
            margin-right: 15px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .logo-text h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .logo-text p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Navigation */
        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Hero Section */
        .hero {
           
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            color: white;
            padding: 100px 20px;
            min-height: 600px;
            display: flex;
            align-items: center;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(12, 92, 47, 0.9) 0%, rgba(26, 46, 54, 0.85) 100%);
            z-index: 1;
        }

        .hero-container {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr;
            gap: 40px;
        }

        @media (min-width: 992px) {
            .hero-container {
                grid-template-columns: 1fr 1fr;
                align-items: center;
            }
        }

        .hero-content {
            text-align: center;
        }

        @media (min-width: 992px) {
            .hero-content {
                text-align: left;
            }
        }

        .hero h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
            max-width: 600px;
        }

        /* Auth Forms Container */
        .auth-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            color: #333;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .auth-container:hover {
            transform: translateY(-5px);
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #e5e5e5;
            margin-bottom: 25px;
        }

        .tab-btn {
            flex: 1;
            background: none;
            border: none;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            color: #6c757d;
            transition: all 0.3s;
            position: relative;
        }

        .tab-btn.active {
            color: var(--primary-color);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 3px 3px 0 0;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group i.icon-left {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .form-group .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
            display: none; /* Initially hide the toggle icons */
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }

        .form-control.password {
            padding-right: 45px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(12, 92, 47, 0.1);
            background-color: #fff;
        }

        .error-msg {
            display: block;
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            border-left: 4px solid #dc3545;
        }

        .form-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(12, 92, 47, 0.1);
        }

        .form-btn:hover {
            background-color: #0a4a26;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(12, 92, 47, 0.15);
        }

        .form-btn:active {
            transform: translateY(0);
        }

        .form-link {
            margin-top: 20px;
            text-align: center;
            color: #6c757d;
        }

        .form-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .form-link a:hover {
            text-decoration: underline;
        }

        /* Admin Login Link */
        .admin-login-link {
            margin-top: 15px;
            text-align: center;
            padding-top: 15px;
            border-top: 1px dashed #dee2e6;
        }

        .admin-login-link a {
            display: inline-flex;
            align-items: center;
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .admin-login-link a:hover {
            color: var(--secondary-color);
        }

        .admin-login-link i {
            margin-right: 5px;
        }

        /* About Section */
        .about {
            padding: 100px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 700;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 3px;
        }

        .section-title p {
            color: #6c757d;
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
        }

        .about-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 40px;
        }

        .about-text {
            flex: 1;
            min-width: 300px;
        }

        .about-text h3 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .about-text p {
            margin-bottom: 20px;
            font-size: 1.05rem;
            color: #555;
        }

        .about-image {
            flex: 1;
            min-width: 300px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .about-image:hover img {
            transform: scale(1.05);
        }

        /* Services Section */
        .services {
            background-color: #f9f9f9;
            padding: 100px 20px;
            position: relative;
        }

        .services::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to bottom, #fff 0%, #f9f9f9 100%);
        }

        .services-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .service-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 30px;
            transition: all 0.3s;
            border-bottom: 4px solid transparent;
        }

        .service-card:hover {
            transform: translateY(-10px);
            border-bottom: 4px solid var(--primary-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .service-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            background-color: rgba(12, 92, 47, 0.1);
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .service-card:hover .service-icon {
            background-color: var(--primary-color);
            color: white;
            transform: rotateY(180deg);
        }

        .service-card h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #333;
            font-weight: 600;
        }

        .service-card p {
            color: #6c757d;
            line-height: 1.7;
        }

        /* Gallery Section */
        .gallery {
            padding: 100px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 50px;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            height: 250px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0) 100%);
            color: white;
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.3s;
            display: flex;
            align-items: flex-end;
            height: 100%;
        }

        .gallery-item:hover .gallery-caption {
            transform: translateY(0);
        }

        /* Contact Section */
        .contact {
            background-color: #f9f9f9;
            padding: 100px 20px;
            position: relative;
        }

        .contact::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to bottom, #fff 0%, #f9f9f9 100%);
        }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .contact-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 40px;
            margin-top: 50px;
        }

        .contact-info {
            flex: 1;
            min-width: 300px;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .contact-info h3 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .contact-info h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 3px;
        }

        .contact-detail {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .contact-icon {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-right: 15px;
            margin-top: 5px;
            width: 40px;
            height: 40px;
            background-color: rgba(12, 92, 47, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .contact-text h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #333;
            font-weight: 600;
        }

        .contact-text p {
            color: #6c757d;
        }

        .contact-map {
            flex: 1;
            min-width: 300px;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .contact-map iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Footer */
        footer {
            background-color: var(--secondary-color);
            color: white;
            padding: 70px 20px 20px;
            position: relative;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 40px;
        }

        .footer-col {
            flex: 1;
            min-width: 250px;
        }

        .footer-col h3 {
            font-size: 1.3rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 15px;
            font-weight: 600;
        }

        .footer-col h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 3px;
        }

        .footer-col p {
            margin-bottom: 20px;
            opacity: 0.8;
            line-height: 1.7;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .footer-links a:hover {
            opacity: 1;
            padding-left: 5px;
            color: var(--accent-color);
        }

        .footer-links a i {
            margin-right: 10px;
            font-size: 0.8rem;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-5px);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 50px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 999;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background-color: var(--secondary-color);
            transform: translateY(-5px);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .hero {
                padding: 80px 20px;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
            
            .about, .gallery, .services, .contact {
                padding: 70px 20px;
            }
            
            .service-card, .gallery-item {
                min-height: auto;
            }
        }

        /* Animation Classes */
        .fade-up {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .fade-up.active {
            opacity: 1;
            transform: translateY(0);
        }

        .delay-1 {
            transition-delay: 0.1s;
        }

        .delay-2 {
            transition-delay: 0.2s;
        }

        .delay-3 {
            transition-delay: 0.3s;
        }

        .delay-4 {
            transition-delay: 0.4s;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="/useradmin/img/mswdologo.png" alt="MSWDO Logo">
                <div class="logo-text">
                    <h1>MSWDO Gloria</h1>
                    <p>Municipal Social Welfare & Development Office</p>
                </div>
            </div>
            <div class="nav-links d-none d-md-flex">
                <a href="#about">About</a>
                <a href="#services">Services</a>
                <a href="#gallery">Gallery</a>
                <a href="#contact">Contact</a>
            </div>
        </div>
    </header>

    <!-- Hero Section with Login/Register Forms -->
    <section class="hero" id="home">
        <div class="hero-container">
            <!-- Left side - Hero content -->
            <div class="hero-content" data-aos="fade-right" data-aos-duration="1000">
                <h2>Empowering Communities, Transforming Lives</h2>
                <p>The Municipal Social Welfare and Development Office of Gloria is committed to providing social protection and promoting the rights and welfare of the poor, vulnerable and disadvantaged individuals and families in our community.</p>
            </div>

            <!-- Right side - Auth forms -->
            <div class="auth-container" data-aos="fade-left" data-aos-duration="1000">
                <!-- Tabs -->
                <div class="tabs">
                    <button class="tab-btn <?php echo (!isset($_GET['register'])) ? 'active' : ''; ?>" onclick="showTab('login')">Login</button>
                    <button class="tab-btn <?php echo (isset($_GET['register'])) ? 'active' : ''; ?>" onclick="showTab('register')">Register</button>
                </div>

                <!-- Login Form -->
                <div id="login-form" style="display: <?php echo (!isset($_GET['register'])) ? 'block' : 'none'; ?>">
                    <?php
                    if(isset($error) && isset($_POST['login_submit'])){
                        foreach($error as $err){
                            echo '<span class="error-msg"><i class="fas fa-exclamation-circle me-2"></i>'.$err.'</span>';
                        };
                    };
                    ?>
                    <form action="" method="post">
                        <div class="form-group">
                            <i class="fas fa-envelope icon-left"></i>
                            <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                        </div>
                        
                        <div class="form-group">
                            <i class="fas fa-lock icon-left"></i>
                            <input type="password" name="password" id="password" class="form-control password" required placeholder="Enter your password">
                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                        
                        <button type="submit" name="login_submit" class="form-btn">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>
                        
                        <div class="form-link">
                            <p>Don't have an account? <a href="javascript:void(0)" onclick="showTab('register')">Register Now</a></p>
                        </div>
             
                     
                    </form>
                </div>

                <!-- Register Form -->
                <div id="register-form" style="display: <?php echo (isset($_GET['register'])) ? 'block' : 'none'; ?>">
                      
                        <?php

                                            
                        if(isset($error) && isset($_POST['register_submit'])){
                            foreach($error as $err){
                                echo'<span class="error-msg">' .$err . '</span>';
                                };
                            };
                            ?>
                    <form action="" method="post">
                        <div class="form-group">
                            <i class="fas fa-user icon-left"></i>
                            <input type="text" name="name" class="form-control" required placeholder="Full Name">
                        </div>
                        
                        <div class="form-group">
                            <i class="fas fa-envelope icon-left"></i>
                            <input type="email" name="email" class="form-control" required placeholder="Email">
                        </div>
                        
                        <div class="form-group">
                            <i class="fas fa-lock icon-left"></i>
                            <input type="password" name="password" id="reg-password" class="form-control password" required placeholder="Password">
                            <i class="fas fa-eye toggle-password" id="toggleRegPassword"></i>
                        </div>
                        
                        <div class="form-group">
                            <i class="fas fa-check-circle icon-left"></i>
                            <input type="password" name="cpassword" id="cpassword" class="form-control password" required placeholder="Confirm Password">
                            <i class="fas fa-eye toggle-password" id="toggleCPassword"></i>
                        </div>
                        
                        <!-- Hidden input for user type -->
                        <input type="hidden" name="user_type" value="user">
                        
                        <button type="submit" name="register_submit" class="form-btn">
                            <i class="fas fa-user-plus me-2"></i> Register
                        </button>
                        
                        <div class="form-link">
                            <p>Already have an account? <a href="javascript:void(0)" onclick="showTab('login')">Login Now</a></p>
                        </div>
                        
                       
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="section-title" data-aos="fade-up">
            <h2>About MSWDO Gloria</h2>
            <p>Learn more about our mission, vision, and the work we do to serve our community.</p>
        </div>
        <div class="about-content">
            <div class="about-text" data-aos="fade-right" data-aos-delay="200">
                <h3>Our Mission</h3>
                <p>The Municipal Social Welfare and Development Office (MSWDO) of Gloria is dedicated to providing effective and efficient delivery of social welfare and development programs and services to promote social protection and poverty reduction in our community.</p>
                
                <h3>Our Vision</h3>
                <p>We envision a community where every individual and family enjoys a better quality of life, with equal access to opportunities, living in a peaceful, healthy, and sustainable environment.</p>
                
                <h3>Our Values</h3>
                <p>Integrity, Compassion, Excellence, Accountability, and Respect guide our work as we serve the people of Gloria.</p>
            </div>
            <div class="about-image" data-aos="fade-left" data-aos-delay="300">
                <img src="dswdbg.png" alt="MSWDO Team">
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="services-container">
            <div class="section-title" data-aos="fade-up">
                <h2>Our Services</h2>
                <p>We offer a wide range of social welfare and development programs to address the needs of our community.</p>
            </div>
            <div class="services-grid">
                <div class="service-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h3>Social Assistance</h3>
                    <p>Financial assistance, food aid, and other forms of support for individuals and families in crisis situations.</p>
                </div>
                <div class="service-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3>Child Protection</h3>
                    <p>Programs and services aimed at protecting children from abuse, neglect, exploitation, and violence.</p>
                </div>
                <div class="service-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h3>Family Development</h3>
                    <p>Services that strengthen family relationships and enhance parenting capabilities.</p>
                </div>
                <div class="service-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-icon">
                        <i class="fas fa-wheelchair"></i>
                    </div>
                    <h3>Disability Affairs</h3>
                    <p>Support services for persons with disabilities to promote their rights and welfare.</p>
                </div>
                <div class="service-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>Educational Assistance</h3>
                    <p>Scholarships and educational support for deserving students from low-income families.</p>
                </div>
                <div class="service-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3>Housing Assistance</h3>
                    <p>Programs that help improve housing conditions for vulnerable families in our community.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery" id="gallery">
        <div class="section-title" data-aos="fade-up">
            <h2>Gallery</h2>
            <p>See our programs and activities in action as we serve the community of Gloria.</p>
        </div>
        <div class="gallery-grid">
            <div class="gallery-item" data-aos="zoom-in" data-aos-delay="100">
                <img src="/placeholder.svg?height=250&width=350" alt="Community Outreach">
                <div class="gallery-caption">Community Outreach Program</div>
            </div>
            <div class="gallery-item" data-aos="zoom-in" data-aos-delay="200">
                <img src="/placeholder.svg?height=250&width=350" alt="Food Distribution">
                <div class="gallery-caption">Food Distribution Activity</div>
            </div>
            <div class="gallery-item" data-aos="zoom-in" data-aos-delay="300">
                <img src="/placeholder.svg?height=250&width=350" alt="Child Development">
                <div class="gallery-caption">Child Development Session</div>
            </div>
            <div class="gallery-item" data-aos="zoom-in" data-aos-delay="100">
                <img src="/placeholder.svg?height=250&width=350" alt="Senior Citizens">
                <div class="gallery-caption">Senior Citizens' Gathering</div>
            </div>
            <div class="gallery-item" data-aos="zoom-in" data-aos-delay="200">
                <img src="/placeholder.svg?height=250&width=350" alt="Livelihood Training">
                <div class="gallery-caption">Livelihood Training Workshop</div>
            </div>
            <div class="gallery-item" data-aos="zoom-in" data-aos-delay="300">
                <img src="/placeholder.svg?height=250&width=350" alt="Medical Mission">
                <div class="gallery-caption">Medical Mission</div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="contact-container">
            <div class="section-title" data-aos="fade-up">
                <h2>Contact Us</h2>
                <p>Get in touch with us for inquiries, assistance, or more information about our programs and services.</p>
            </div>
            <div class="contact-content">
                <div class="contact-info" data-aos="fade-right" data-aos-delay="200">
                    <h3>Get In Touch</h3>
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Address</h4>
                            <p>Municipal Hall, Gloria, Oriental Mindoro, Philippines</p>
                        </div>
                    </div>
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Phone</h4>
                            <p>+63 (XXX) XXX-XXXX</p>
                        </div>
                    </div>
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Email</h4>
                            <p>mswdo.gloria@gmail.com</p>
                        </div>
                    </div>
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Office Hours</h4>
                            <p>Monday to Friday: 8:00 AM - 5:00 PM</p>
                        </div>
                    </div>
                </div>
                <div class="contact-map" data-aos="fade-left" data-aos-delay="300">
                    <!-- Replace with actual Google Maps embed code for Gloria, Oriental Mindoro -->
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123939.95165916862!2d121.41660259726562!3d13.099999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bcf9f93b29c91f%3A0x3b06d7b27da7c4a0!2sGloria%2C%20Oriental%20Mindoro!5e0!3m2!1sen!2sph!4v1648226000000!5m2!1sen!2sph" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-col">
                <h3>MSWDO Gloria</h3>
                <p>The Municipal Social Welfare and Development Office of Gloria is dedicated to serving the community through various social programs and services.</p>
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#about"><i class="fas fa-chevron-right"></i> About Us</a></li>
                    <li><a href="#services"><i class="fas fa-chevron-right"></i> Our Services</a></li>
                    <li><a href="#gallery"><i class="fas fa-chevron-right"></i> Gallery</a></li>
                    <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Contact Information</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Municipal Hall, Gloria, Oriental Mindoro</a></li>
                    <li><a href="tel:+63(XXX)XXX-XXXX"><i class="fas fa-phone-alt"></i> +63 (XXX) XXX-XXXX</a></li>
                    <li><a href="mailto:mswdo.gloria@gmail.com"><i class="fas fa-envelope"></i> mswdo.gloria@gmail.com</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Subscribe</h3>
                <p>Stay updated with our latest programs and activities. Subscribe to our newsletter.</p>
                <form action="#" method="post">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="copyright">
            &copy; 2024 MSWDO Gloria. All rights reserved.
        </div>
    </footer>

    <!-- Back to Top Button -->
    <div class="back-to-top" onclick="scrollToTop()">
        <i class="fas fa-chevron-up"></i>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
        });

        // Function to show tab
        function showTab(tabId) {
            document.getElementById('login-form').style.display = (tabId === 'login') ? 'block' : 'none';
            document.getElementById('register-form').style.display = (tabId === 'register') ? 'block' : 'none';

            // Update active class on tabs
            document.querySelector('.tab-btn.active').classList.remove('active');
            document.querySelector(`.tab-btn[onclick="showTab('${tabId}')"]`).classList.add('active');

            // Update URL to reflect the active tab
            const url = new URL(window.location);
            if (tabId === 'register') {
                url.searchParams.set('register', 'true');
            } else {
                url.searchParams.delete('register');
            }
            window.history.pushState({}, '', url);
        }

        // Function to scroll to top
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show back to top button when scrolling down
        window.addEventListener('scroll', () => {
            const backToTopButton = document.querySelector('.back-to-top');
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });

        // Password toggle functionality
        document.querySelectorAll('.toggle-password').forEach(function(toggleButton) {
            toggleButton.style.display = 'block'; // Show the toggle icons
            toggleButton.addEventListener('click', function() {
                const passwordInput = this.closest('.form-group').querySelector('.form-control.password');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });

        function fillAdminCredentials() {
            document.querySelector('input[name="email"]').value = 'Admin';
            document.querySelector('input[name="password"]').value = 'password';
        }
    </script>
</body>
</html>

