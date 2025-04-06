<?php

$host = "localhost";
$username = "root"; 
$password = ""; 
$database = "user_db"; 

// Create connection
$conn = mysqli_connect('localhost','root','','user_db');

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


mysqli_set_charset($conn, "utf8mb4");


date_default_timezone_set('Asia/Manila'); 
?>

