<?php
@include 'config.php';

if(isset($_POST['submit'])){

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
         header('location:login_form.php');
      }
   }

};
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>MSWDO Gloria - Register</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
   <style>
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: Arial, sans-serif;
      }

      body {
         background-color: #f5f5f5;
         display: flex;
         justify-content: center;
         align-items: center;
         min-height: 100vh;
      }

      .register-container {
         width: 100%;
         max-width: 500px;
         padding: 30px;
      }

      .logo-container {
         text-align: center;
         margin-bottom: 30px;
      }

      .logo-container img {
         height: 80px;
         margin-bottom: 15px;
      }

      .logo-container h1 {
         color: #0c5c2f;
         font-size: 1.5rem;
         margin-bottom: 5px;
      }

      .logo-container p {
         color: #6c757d;
         font-size: 0.9rem;
      }

      .register-form {
         background-color: white;
         border-radius: 8px;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
         padding: 30px;
      }

      .register-form h3 {
         color: #0c5c2f;
         font-size: 1.5rem;
         margin-bottom: 20px;
         text-align: center;
         text-transform: capitalize;
      }

      .form-group {
         margin-bottom: 20px;
      }

      .form-group label {
         display: block;
         margin-bottom: 8px;
         font-weight: bold;
         color: #495057;
      }

      .form-control {
         width: 100%;
         padding: 12px 15px;
         border: 1px solid #ced4da;
         border-radius: 4px;
         font-size: 1rem;
         transition: border-color 0.3s;
      }

      .form-control:focus {
         border-color: #0c5c2f;
         outline: none;
         box-shadow: 0 0 0 3px rgba(12, 92, 47, 0.1);
      }

      .error-msg {
         display: block;
         background-color: #f8d7da;
         color: #721c24;
         padding: 10px;
         border-radius: 4px;
         margin-bottom: 20px;
         text-align: center;
      }

      .form-btn {
         width: 100%;
         padding: 12px;
         background-color: #0c5c2f;
         color: white;
         border: none;
         border-radius: 4px;
         font-size: 1rem;
         cursor: pointer;
         transition: background-color 0.3s;
         text-transform: capitalize;
         font-weight: bold;
         margin-top: 10px;
      }

      .form-btn:hover {
         background-color: #0a4a26;
      }

      .login-link {
         margin-top: 20px;
         text-align: center;
         color: #6c757d;
      }

      .login-link a {
         color: #0c5c2f;
         text-decoration: none;
         font-weight: bold;
      }

      .login-link a:hover {
         text-decoration: underline;
      }

      .input-group {
         position: relative;
         margin-bottom: 20px;
      }

      .input-group i.icon-left {
         position: absolute;
         left: 15px;
         top: 50%;
         transform: translateY(-50%);
         color: #6c757d;
      }

      .input-group i.toggle-password {
         position: absolute;
         right: 15px;
         top: 50%;
         transform: translateY(-50%);
         color: #6c757d;
         cursor: pointer;
         z-index: 10;
      }

      .input-group input {
         width: 100%;
         padding: 12px 15px 12px 45px;
         border: 1px solid #ced4da;
         border-radius: 4px;
         font-size: 1rem;
         transition: border-color 0.3s;
      }

      .input-group input:focus {
         border-color: #0c5c2f;
         outline: none;
         box-shadow: 0 0 0 3px rgba(12, 92, 47, 0.1);
      }

      .password-input {
         padding-right: 45px !important;
      }

      .footer {
         text-align: center;
         margin-top: 30px;
         color: #6c757d;
         font-size: 0.8rem;
      }
   </style>
</head>
<body>
   
   <div class="register-container">
      <div class="logo-container">
         <img src="/useradmin/img/mswdologo.png" alt="MSWDO Logo">
         <h1>Municipal Social Welfare & Development Gloria</h1>
         <p>Create a new account</p>
      </div>

      <div class="register-form">
         <h3>Register</h3>
         
         <?php
         if(isset($error)){
            foreach($error as $error){
               echo '<span class="error-msg">'.$error.'</span>';
            };
         };
         ?>
         
         <form action="" method="post">
            <div class="input-group">
               <i class="fas fa-user icon-left"></i>
               <input type="text" name="name" required placeholder="Full Name">
            </div>
            
            <div class="input-group">
               <i class="fas fa-envelope icon-left"></i>
               <input type="email" name="email" required placeholder="Email">
            </div>
            
            <div class="input-group">
               <i class="fas fa-lock icon-left"></i>
               <input type="password" name="password" id="password" class="password-input" required placeholder="Password">
               <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            
            <div class="input-group">
               <i class="fas fa-check-circle icon-left"></i>
               <input type="password" name="cpassword" id="cpassword" class="password-input" required placeholder="Confirm Password">
               <i class="fas fa-eye toggle-password" id="toggleCPassword"></i>
            </div>
            
            <!-- Hidden input for user type -->
            <input type="hidden" name="user_type" value="user">
            
            <button type="submit" name="submit" class="form-btn">Register Now</button>
            
            <div class="login-link">
               <p>Already have an account? <a href="login_form.php">Login Now</a></p>
            </div>
         </form>
      </div>
      
      <div class="footer">
         &copy; 2025 MSWDO of Gloria. All rights reserved.
      </div>
   </div>

   <script>
      // Toggle password visibility
      document.getElementById('togglePassword').addEventListener('click', function() {
         const passwordInput = document.getElementById('password');
         const icon = this;
         
         // Toggle the password field type
         if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
         } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
         }
      });
      
      // Toggle confirm password visibility
      document.getElementById('toggleCPassword').addEventListener('click', function() {
         const passwordInput = document.getElementById('cpassword');
         const icon = this;
         
         // Toggle the password field type
         if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
         } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
         }
      });
   </script>
</body>
</html>