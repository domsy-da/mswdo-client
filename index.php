<?php
session_start();
@include 'config.php';
@include 'mailer.php';

// Initialize error array
$error = array();
$success_msg = '';

// Function to generate OTP
function generateOTP($length = 6) {
    $characters = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $otp;
}

// Function to send OTP via email
function sendLoginOTP($email, $otp, $name) {
    $subject = "Your Login OTP Verification Code - MSWDO Gloria";
    
    $html_message = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>
        <h2 style='color: #0c5c2f;'>MSWDO Gloria - Login Verification</h2>
        <p>Hello $name,</p>
        <p>You are attempting to log in to your MSWDO Gloria account. Please use the following verification code to complete your login:</p>
        <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
            $otp
        </div>
        <p>This code will expire in 10 minutes.</p>
        <p>If you did not request this verification, please ignore this email or contact us immediately if you believe your account has been compromised.</p>
        <p>Best regards,<br>MSWDO Gloria Team</p>
    </div>
    ";
    
    $plain_message = "Hello $name,\n\nYour OTP verification code for login is: $otp\n\nThis code will expire in 10 minutes.\n\nBest regards,\nMSWDO Gloria Team";
    
    $result = sendEmail($email, $name, $subject, $html_message, $plain_message);
    
    return $result['success'];
}

// Step 1: Handle email submission for OTP
if (isset($_POST['request_otp'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Invalid email format!';
    } else {
        // Check if user exists
        $select = "SELECT * FROM user_form WHERE email = ?";
        $stmt = $conn->prepare($select);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Generate OTP and store in session
            $otp = generateOTP();
            $_SESSION['login_otp'] = $otp;
            $_SESSION['login_otp_expiry'] = time() + 600; // 10 minutes expiry
            $_SESSION['login_email'] = $email;
            $_SESSION['login_user_id'] = $row['id'];
            $_SESSION['login_user_name'] = $row['name'];
            $_SESSION['login_user_type'] = $row['user_type'];
            
            // Send OTP to user's email
            if (sendLoginOTP($email, $otp, $row['name'])) {
                $_SESSION['show_otp_login_form'] = true;
                $success_msg = "OTP has been sent to your email address.";
            } else {
                $error[] = 'Failed to send OTP. Please check your email address and try again.';
            }
        } else {
            $error[] = 'No account found with this email address!';
        }
        $stmt->close();
    }
}

// Step 2: Verify OTP and complete login
if (isset($_POST['verify_login_otp'])) {
    $entered_otp = mysqli_real_escape_string($conn, $_POST['otp']);
    
    // Verify OTP and check expiry
    if (isset($_SESSION['login_otp']) && isset($_SESSION['login_otp_expiry'])) {
        if (time() > $_SESSION['login_otp_expiry']) {
            $error[] = 'OTP has expired. Please request a new one.';
        } elseif ($_SESSION['login_otp'] == $entered_otp) {
            // OTP is correct, complete login
            $_SESSION['user_id'] = $_SESSION['login_user_id'];
            $_SESSION['user_name'] = $_SESSION['login_user_name'];
            $_SESSION['user_type'] = $_SESSION['login_user_type'];
            
            // Clear login session data
            unset($_SESSION['login_otp']);
            unset($_SESSION['login_otp_expiry']);
            unset($_SESSION['login_email']);
            unset($_SESSION['login_user_id']);
            unset($_SESSION['login_user_name']);
            unset($_SESSION['login_user_type']);
            unset($_SESSION['show_otp_login_form']);
            
            // Redirect based on user type
            if ($_SESSION['user_type'] == 'admin') {
                header('location: admin_dashboard.php');
            } else {
                header('location: user_page.php');
            }
            exit();
        } else {
            $error[] = 'Invalid OTP. Please try again.';
        }
    } else {
        $error[] = 'Session expired. Please start login again.';
    }
}

// Process resend OTP
if (isset($_GET['resend_login_otp']) && isset($_SESSION['login_email'])) {
    $otp = generateOTP();
    $_SESSION['login_otp'] = $otp;
    $_SESSION['login_otp_expiry'] = time() + 600;
    
    if (sendLoginOTP($_SESSION['login_email'], $otp, $_SESSION['login_user_name'])) {
        $_SESSION['show_otp_login_form'] = true;
        $success_msg = 'New OTP sent to your email.';
    } else {
        $error[] = 'Failed to resend OTP. Please try again.';
    }
}

// Handle regular login form
if(isset($_POST['submit'])){
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = md5($_POST['password']);

   $select = "SELECT * FROM user_form WHERE email = ? AND password = ?";
   $stmt = $conn->prepare($select);
   $stmt->bind_param("ss", $email, $pass);
   $stmt->execute();
   $result = $stmt->get_result();

   if($result->num_rows > 0){
      $row = $result->fetch_assoc();
      $_SESSION['user_id'] = $row['id'];
      $_SESSION['user_name'] = $row['name'];
      $_SESSION['user_type'] = $row['user_type'];

      if($row['user_type'] == 'admin'){
         header('location:admin_dashboard.php');
      }elseif($row['user_type'] == 'user'){
         header('location:user_page.php');
      }
   }else{
      $error[] = 'Incorrect email or password!';
   }
   $stmt->close();
}

// Handle user registration
if(isset($_POST['register'])){
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, $_POST['password']);
   $cpass = mysqli_real_escape_string($conn, $_POST['cpassword']);
   $user_type = 'user';

   // Check if email already exists
   $select = "SELECT * FROM user_form WHERE email = ?";
   $stmt = $conn->prepare($select);
   $stmt->bind_param("s", $email);
   $stmt->execute();
   $result = $stmt->get_result();

   if($result->num_rows > 0){
      $error[] = 'User already exists!';
   }else{
      if($pass != $cpass){
         $error[] = 'Passwords do not match!';
      }else{
         // Generate OTP for email verification
         $verification_otp = generateOTP();
         $hashed_password = password_hash($pass, PASSWORD_BCRYPT);
         
         // Store user data and OTP in session for verification
         $_SESSION['register_name'] = $name;
         $_SESSION['register_email'] = $email;
         $_SESSION['register_password'] = $hashed_password;
         $_SESSION['register_otp'] = $verification_otp;
         $_SESSION['register_otp_expiry'] = time() + 600; // 10 minutes expiry
         
         // Send verification OTP
         $subject = "Email Verification - MSWDO Gloria";
         
         $html_message = "
         <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>
             <h2 style='color: #0c5c2f;'>MSWDO Gloria - Email Verification</h2>
             <p>Hello $name,</p>
             <p>Thank you for registering with MSWDO Gloria. Please use the following verification code to complete your registration:</p>
             <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
                 $verification_otp
             </div>
             <p>This code will expire in 10 minutes.</p>
             <p>If you did not request this verification, please ignore this email.</p>
             <p>Best regards,<br>MSWDO Gloria Team</p>
         </div>
         ";
         
         $plain_message = "Hello $name,\n\nYour verification code is: $verification_otp\n\nThis code will expire in 10 minutes.\n\nBest regards,\nMSWDO Gloria Team";
         
         $result = sendEmail($email, $name, $subject, $html_message, $plain_message);
         
         if($result['success']){
            $_SESSION['show_verification_form'] = true;
            $success_msg = "Verification code has been sent to your email.";
         }else{
            $error[] = 'Failed to send verification email. Please try again.';
         }
      }
   }
   $stmt->close();
}

// Handle OTP verification for registration
if(isset($_POST['verify_registration'])){
   $entered_otp = mysqli_real_escape_string($conn, $_POST['verification_otp']);
   
   if(isset($_SESSION['register_otp']) && isset($_SESSION['register_otp_expiry'])){
      if(time() > $_SESSION['register_otp_expiry']){
         $error[] = 'Verification code has expired. Please register again.';
         unset($_SESSION['show_verification_form']);
         unset($_SESSION['register_name']);
         unset($_SESSION['register_email']);
         unset($_SESSION['register_password']);
         unset($_SESSION['register_otp']);
         unset($_SESSION['register_otp_expiry']);
      }elseif($_SESSION['register_otp'] == $entered_otp){
         // OTP is correct, complete registration
         $name = $_SESSION['register_name'];
         $email = $_SESSION['register_email'];
         $password = $_SESSION['register_password'];
         $user_type = 'user';
         $is_verified = 1;
         
         $insert = "INSERT INTO user_form(name, email, password, user_type, is_verified) VALUES(?,?,?,?,?)";
         $stmt = $conn->prepare($insert);
         $stmt->bind_param("ssssi", $name, $email, $password, $user_type, $is_verified);
         $stmt->execute();
         $stmt->close();
         
         // Clear registration session data
         unset($_SESSION['show_verification_form']);
         unset($_SESSION['register_name']);
         unset($_SESSION['register_email']);
         unset($_SESSION['register_password']);
         unset($_SESSION['register_otp']);
         unset($_SESSION['register_otp_expiry']);
         
         $success_msg = 'Registration successful! You can now login.';
      }else{
         $error[] = 'Invalid verification code. Please try again.';
      }
   }else{
      $error[] = 'Session expired. Please register again.';
      unset($_SESSION['show_verification_form']);
   }
}

// Process resend verification OTP
if(isset($_GET['resend_verification_otp']) && isset($_SESSION['register_email'])){
   $verification_otp = generateOTP();
   $_SESSION['register_otp'] = $verification_otp;
   $_SESSION['register_otp_expiry'] = time() + 600;
   
   $name = $_SESSION['register_name'];
   $email = $_SESSION['register_email'];
   
   $subject = "Email Verification - MSWDO Gloria";
   
   $html_message = "
   <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>
       <h2 style='color: #0c5c2f;'>MSWDO Gloria - Email Verification</h2>
       <p>Hello $name,</p>
       <p>Here is your new verification code to complete your registration:</p>
       <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
           $verification_otp
       </div>
       <p>This code will expire in 10 minutes.</p>
       <p>If you did not request this verification, please ignore this email.</p>
       <p>Best regards,<br>MSWDO Gloria Team</p>
   </div>
   ";
   
   $plain_message = "Hello $name,\n\nYour new verification code is: $verification_otp\n\nThis code will expire in 10 minutes.\n\nBest regards,\nMSWDO Gloria Team";
   
   $result = sendEmail($email, $name, $subject, $html_message, $plain_message);
   
   if($result['success']){
      $_SESSION['show_verification_form'] = true;
      $success_msg = 'New verification code sent to your email.';
   }else{
      $error[] = 'Failed to resend verification code. Please try again.';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MSWDO Gloria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #0c5c2f;
            --secondary-color: #1a2e36;
            --accent-color: #4cd964;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            width: 100%;
            max-width: 450px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .logo img {
            height: 60px;
            margin-right: 15px;
        }
        
        .logo-text h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .logo-text p {
            font-size: 0.9rem;
            color: #6c757d;
            margin: 0;
        }
        
        .form-title {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .form-title h2 {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .form-title p {
            color: #6c757d;
            margin: 0;
        }
        
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
        
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(12, 92, 47, 0.1);
            background-color: #fff;
        }
        
        .otp-input {
            text-align: center;
            letter-spacing: 0.5em;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .otp-message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .otp-email {
            font-weight: 600;
            color: var(--primary-color);
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
        
        .success-msg {
            display: block;
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            border-left: 4px solid #28a745;
        }
        
        .auth-options {
            display: flex;
            margin-bottom: 20px;
        }
        
        .auth-option {
            flex: 1;
            text-align: center;
            padding: 10px;
            cursor: pointer;
            border-bottom: 2px solid #dee2e6;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .auth-option.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        #register-form {
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="/useradmin/img/mswdologo.png" alt="MSWDO Logo">
            <div class="logo-text">
                <h1>MSWDO Gloria</h1>
                <p>Municipal Social Welfare & Development Office</p>
            </div>
        </div>
        
        <?php if (!isset($_SESSION['show_otp_login_form']) && !isset($_SESSION['show_verification_form'])): ?>
        <div class="auth-options">
            <div class="auth-option active" id="login-option">Login</div>
            <div class="auth-option" id="register-option">Register</div>
        </div>
        <?php endif; ?>
        
        <div class="form-title">
            <?php if (isset($_SESSION['show_otp_login_form'])): ?>
                <h2>OTP Verification</h2>
                <p>Enter the verification code sent to your email</p>
            <?php elseif (isset($_SESSION['show_verification_form'])): ?>
                <h2>Email Verification</h2>
                <p>Enter the verification code sent to your email</p>
            <?php else: ?>
                <h2>Welcome Back</h2>
                <p>Please login to your account</p>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($error)): ?>
            <?php foreach ($error as $err): ?>
                <span class="error-msg"><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($err); ?></span>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($success_msg)): ?>
            <span class="success-msg"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_msg); ?></span>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['show_otp_login_form']) && $_SESSION['show_otp_login_form'] === true): ?>
            <!-- OTP Verification Form -->
            <form action="" method="post">
                <div class="form-group">
                    <i class="fas fa-key icon-left"></i>
                    <input type="text" name="otp" class="form-control otp-input" required placeholder="Enter OTP" maxlength="6">
                </div>
                
                <p class="otp-message">
                    Please enter the 6-digit verification code sent to <span class="otp-email"><?php echo htmlspecialchars($_SESSION['login_email']); ?></span>
                </p>
                
                <button type="submit" name="verify_login_otp" class="form-btn">
                    <i class="fas fa-sign-in-alt me-2"></i> Verify & Login
                </button>
                
                <div class="form-link">
                    <p>Didn't receive the code? <a href="?resend_login_otp=true">Resend OTP</a></p>
                </div>
                
                <div class="form-link">
                    <p><a href="index.php"><i class="fas fa-arrow-left me-1"></i> Back to Login</a></p>
                </div>
            </form>
        <?php elseif (isset($_SESSION['show_verification_form']) && $_SESSION['show_verification_form'] === true): ?>
            <!-- Registration Verification Form -->
            <form action="" method="post">
                <div class="form-group">
                    <i class="fas fa-key icon-left"></i>
                    <input type="text" name="verification_otp" class="form-control otp-input" required placeholder="Enter Code" maxlength="6">
                </div>
                
                <p class="otp-message">
                    Please enter the 6-digit verification code sent to <span class="otp-email"><?php echo htmlspecialchars($_SESSION['register_email']); ?></span>
                </p>
                
                <button type="submit" name="verify_registration" class="form-btn">
                    <i class="fas fa-user-check me-2"></i> Verify & Complete Registration
                </button>
                
                <div class="form-link">
                    <p>Didn't receive the code? <a href="?resend_verification_otp=true">Resend Code</a></p>
                </div>
                
                <div class="form-link">
                    <p><a href="index.php"><i class="fas fa-arrow-left me-1"></i> Back to Login</a></p>
                </div>
            </form>
        <?php else: ?>
            <!-- Login Form -->
            <form action="" method="post" id="login-form">
                <div class="form-group">
                    <i class="fas fa-envelope icon-left"></i>
                    <input type="email" name="email" class="form-control" required placeholder="Email">
                </div>
                
                <div class="form-group">
                    <i class="fas fa-lock icon-left"></i>
                    <input type="password" name="password" class="form-control" required placeholder="Password">
                </div>
                
                <button type="submit" name="submit" class="form-btn">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>
                
                <div class="form-link">
                    <p>Login with OTP? <a href="#" id="otp-login-link">Click here</a></p>
                </div>
                
                <div class="form-link">
                    <p>Don't have an account? <a href="#" id="switch-to-register">Register now</a></p>
                </div>
            </form>
            
            <!-- Register Form -->
            <form action="" method="post" id="register-form">
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
                    <input type="password" name="password" class="form-control" required placeholder="Password">
                </div>
                
                <div class="form-group">
                    <i class="fas fa-lock icon-left"></i>
                    <input type="password" name="cpassword" class="form-control" required placeholder="Confirm Password">
                </div>
                
                <button type="submit" name="register" class="form-btn">
                    <i class="fas fa-user-plus me-2"></i> Register
                </button>
                
                <div class="form-link">
                    <p>Already have an account? <a href="#" id="switch-to-login">Login now</a></p>
                </div>
            </form>
            
            <!-- OTP Login Form -->
            <form action="" method="post" id="otp-login-form" style="display: none;">
                <div class="form-group">
                    <i class="fas fa-envelope icon-left"></i>
                    <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                </div>
                
                <button type="submit" name="request_otp" class="form-btn">
                    <i class="fas fa-paper-plane me-2"></i> Send OTP
                </button>
                
                <div class="form-link">
                    <p><a href="#" id="back-to-login"><i class="fas fa-arrow-left me-1"></i> Back to Login</a></p>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginOption = document.getElementById('login-option');
            const registerOption = document.getElementById('register-option');
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            const otpLoginForm = document.getElementById('otp-login-form');
            const switchToRegister = document.getElementById('switch-to-register');
            const switchToLogin = document.getElementById('switch-to-login');
            const otpLoginLink = document.getElementById('otp-login-link');
            const backToLogin = document.getElementById('back-to-login');
            
            if (loginOption && registerOption) {
                loginOption.addEventListener('click', function() {
                    loginOption.classList.add('active');
                    registerOption.classList.remove('active');
                    loginForm.style.display = 'block';
                    registerForm.style.display = 'none';
                    otpLoginForm.style.display = 'none';
                });
                
                registerOption.addEventListener('click', function() {
                    registerOption.classList.add('active');
                    loginOption.classList.remove('active');
                    registerForm.style.display = 'block';
                    loginForm.style.display = 'none';
                    otpLoginForm.style.display = 'none';
                });
            }
            
            if (switchToRegister) {
                switchToRegister.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (registerOption) {
                        registerOption.classList.add('active');
                        loginOption.classList.remove('active');
                    }
                    registerForm.style.display = 'block';
                    loginForm.style.display = 'none';
                    otpLoginForm.style.display = 'none';
                });
            }
            
            if (switchToLogin) {
                switchToLogin.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (loginOption) {
                        loginOption.classList.add('active');
                        registerOption.classList.remove('active');
                    }
                    loginForm.style.display = 'block';
                    registerForm.style.display = 'none';
                    otpLoginForm.style.display = 'none';
                });
            }
            
            if (otpLoginLink) {
                otpLoginLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    loginForm.style.display = 'none';
                    registerForm.style.display = 'none';
                    otpLoginForm.style.display = 'block';
                });
            }
            
            if (backToLogin) {
                backToLogin.addEventListener('click', function(e) {
                    e.preventDefault();
                    loginForm.style.display = 'block';
                    registerForm.style.display = 'none';
                    otpLoginForm.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>

