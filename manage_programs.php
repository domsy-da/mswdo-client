<?php
@include 'config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('location:index.php');
    exit();
}

// Get the admin's information from the session
$admin_id = $_SESSION['user_id'];
$admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Admin";

// Fetch admin details from the database
try {
    $sql = "SELECT name FROM user_form WHERE id = ? AND user_type = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Update the admin_name from the database
        $admin_name = $row['name'];
    }
    $stmt->close();
} catch (Exception $e) {
    // If there's an error with the database query, we'll use the session name
}

// Initialize messages
$success_msg = "";
$error_msg = "";

// Debug information - uncomment to see what's happening
//echo "Admin ID from session: " . $admin_id . "<br>";
//echo "Admin type from session: " . $_SESSION['user_type'] . "<br>";

// Check if the admin ID exists in the user_form table
//$check_query = "SELECT id FROM user_form WHERE id = ?";
//$check_stmt = $conn->prepare($check_query);
//$check_stmt->bind_param("i", $admin_id);
//$check_stmt->execute();
//$check_result = $check_stmt->get_result();

//if ($check_result->num_rows == 0) {
//    echo "Warning: Admin ID " . $admin_id . " does not exist in user_form table.<br>";
    
    // Try to find any admin user
//    $find_admin_query = "SELECT id FROM user_form WHERE user_type = 'admin' LIMIT 1";
//    $find_admin_result = $conn->query($find_admin_query);
    
//    if ($find_admin_result && $find_admin_result->num_rows > 0) {
//        $admin_row = $find_admin_result->fetch_assoc();
//        $admin_id = $admin_row['id'];
//        echo "Using alternative admin ID: " . $admin_id . "<br>";
//    } else {
//        echo "No admin users found in the database.<br>";
        
        // As a last resort, try to find any user
//        $find_any_user_query = "SELECT id FROM user_form LIMIT 1";
//        $find_any_user_result = $conn->query($find_any_user_query);
        
//        if ($find_any_user_result && $find_any_user_result->num_rows > 0) {
//            $user_row = $find_any_user_result->fetch_assoc();
//            $admin_id = $user_row['id'];
//            echo "Using any available user ID: " . $admin_id . "<br>";
//        } else {
//            echo "No users found in the database at all.<br>";
//            $error_msg = "Error: No valid users found in the system. Please create a user account first.";
//        }
//    }
//}
//$check_stmt->close();

// Check the structure of the programs table
//$table_check_query = "SHOW CREATE TABLE programs";
//$table_check_result = $conn->query($table_check_query);
//if ($table_check_result) {
//    $table_row = $table_check_result->fetch_assoc();
//    echo "<pre>Table structure: " . htmlspecialchars($table_row['Create Table']) . "</pre>";
//}

// Handle Add Program
if (isset($_POST['add_program'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    // Check if the admin ID exists in the user_form table
    $check_admin_query = "SELECT id FROM user_form WHERE id = ?";
    $check_admin_stmt = $conn->prepare($check_admin_query);
    $check_admin_stmt->bind_param("i", $admin_id);
    $check_admin_stmt->execute();
    $check_admin_result = $check_admin_stmt->get_result();
    
    if ($check_admin_result->num_rows == 0) {
        // If admin ID doesn't exist, find any valid user
        $find_user_query = "SELECT id FROM user_form LIMIT 1";
        $find_user_result = $conn->query($find_user_query);
        if ($find_user_result && $find_user_result->num_rows > 0) {
            $user_row = $find_user_result->fetch_assoc();
            $admin_id = $user_row['id'];
        } else {
            $error_msg = "Error: No valid users found in the system. Please create a user account first.";
            $check_admin_stmt->close();
            // Skip the insert operation
            goto skip_insert;
        }
    }
    $check_admin_stmt->close();
    
    // Insert new program
    $insert_query = "INSERT INTO programs (title, date, location, description, status, category, created_at, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ssssssi", $title, $date, $location, $description, $status, $category, $admin_id);
    
    if ($insert_stmt->execute()) {
        $success_msg = "Program added successfully!";
    } else {
        $error_msg = "Error adding program: " . $conn->error;
    }
    $insert_stmt->close();
}
skip_insert:

// Handle Edit Program
if (isset($_POST['edit_program'])) {
    $program_id = $_POST['program_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    $update_query = "UPDATE programs SET title = ?, date = ?, location = ?, description = ?, 
                status = ?, category = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssssssi", $title, $date, $location, $description, $status, $category, $program_id);
    
    if ($update_stmt->execute()) {
        $success_msg = "Program updated successfully!";
    } else {
        $error_msg = "Error updating program: " . $conn->error;
    }
    $update_stmt->close();
}

// Handle Delete Program
if (isset($_POST['delete_program'])) {
    $program_id = $_POST['program_id'];
    
    $delete_query = "DELETE FROM programs WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $program_id);
    
    if ($delete_stmt->execute()) {
        $success_msg = "Program deleted successfully!";
    } else {
        $error_msg = "Error deleting program: " . $conn->error;
    }
    $delete_stmt->close();
}

// Handle Mark Program as Completed
if (isset($_POST['mark_completed'])) {
    $program_id = $_POST['program_id'];
    
    $update_query = "UPDATE programs SET status = 'completed', completion_date = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $program_id);
    
    if ($update_stmt->execute()) {
        $success_msg = "Program marked as completed successfully!";
    } else {
        $error_msg = "Error updating program status: " . $conn->error;
    }
    $update_stmt->close();
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
    $error_msg = "Error fetching programs: " . $e->getMessage();
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

// Count successful programs (just count completed programs for now)
$successful_count = 0;
$successful_query = "SELECT COUNT(*) as count FROM programs WHERE status = 'completed'";
$successful_result = $conn->query($successful_query);
if ($successful_result && $row = $successful_result->fetch_assoc()) {
    $successful_count = $row['count'];
}

// Count programs by status
$upcoming_count = 0;
$ongoing_count = 0;
$completed_count = 0;

$status_counts_query = "SELECT status, COUNT(*) as count FROM programs GROUP BY status";
$status_counts_result = $conn->query($status_counts_query);
if ($status_counts_result) {
    while ($row = $status_counts_result->fetch_assoc()) {
        if ($row['status'] == 'upcoming') {
            $upcoming_count = $row['count'];
        } elseif ($row['status'] == 'ongoing') {
            $ongoing_count = $row['count'];
        } elseif ($row['status'] == 'completed') {
            $completed_count = $row['count'];
        }
    }
}

// Total programs count
$total_programs = $upcoming_count + $ongoing_count + $completed_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programs - MSWDO BMS</title>
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
            position: relative;
            min-height: 100vh;
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
            margin-bottom: 20px;
        }
        
        /* Program Management Styles */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin: 0;
        }
        
        .filter-container {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-input {
            padding: 10px 15px 10px 40px;
            border-radius: 8px;
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
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .filter-select {
            padding: 10px 15px;
            border-radius: 8px;
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
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            margin-bottom: 0;
        }
        
        .table th {
            background-color: rgba(0, 0, 0, 0.03);
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .table td, .table th {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
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
        
        .status-successful {
            background-color: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .btn-view {
            background-color: rgba(115, 103, 240, 0.1);
            color: #7367f0;
            border: 1px solid rgba(115, 103, 240, 0.2);
        }
        
        .btn-view:hover {
            background-color: #7367f0;
            color: white;
        }
        
        .btn-edit {
            background-color: rgba(40, 199, 111, 0.1);
            color: #28c76f;
            border: 1px solid rgba(40, 199, 111, 0.2);
        }
        
        .btn-edit:hover {
            background-color: #28c76f;
            color: white;
        }
        
        .btn-delete {
            background-color: rgba(234, 84, 85, 0.1);
            color: #ea5455;
            border: 1px solid rgba(234, 84, 85, 0.2);
        }
        
        .btn-delete:hover {
            background-color: #ea5455;
            color: white;
        }
        
        .btn-complete {
            background-color: rgba(0, 123, 255, 0.1);
            color: #007bff;
            border: 1px solid rgba(0, 123, 255, 0.2);
        }
        
        .btn-complete:hover {
            background-color: #007bff;
            color: white;
        }
        
        /* Modal Styles */
        .modal-header {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark-text);
        }
        
        .form-control {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(12, 92, 47, 0.1);
        }
        
        .form-select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(12, 92, 47, 0.1);
        }
        
        /* Stats Card */
        .stats-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            background-color: #007bff;
        }
        
        .stats-icon.blue {
            background-color: #4361ee;
        }
        
        .stats-icon.green {
            background-color: var(--secondary-color);
        }
        
        .stats-icon.orange {
            background-color: #ff9f43;
        }
        
        .stats-icon.red {
            background-color: #ea5455;
        }
        
        .stats-icon.purple {
            background-color: #7367f0;
        }
        
        .stats-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark-text);
        }
        
        .stats-info p {
            color: #6c757d;
            margin: 0;
            font-size: 0.9rem;
        }
        
        /* Stats Container */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
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
                box-shadow: none;
            }
            
            .sidebar.show {
                margin-left: 0;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            }
            
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            
            .navbar {
                left: 0;
                width: 100%;
            }
            
            .navbar-brand h1 {
                font-size: 1rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-container {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-input {
                width: 100%;
            }
            
            .search-box {
                width: 100%;
            }
            
            .stats-container {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .action-btn {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
            
            .table td, .table th {
                padding: 10px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand h1 {
                display: none;
            }
            
            .content {
                padding: 15px;
            }
            
            .breadcrumb {
                padding: 10px;
            }
            
            .user-profile span {
                display: none;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }
        
        /* Fix for sidebar scrolling on mobile */
        @media (max-height: 600px) {
            .sidebar {
                overflow-y: auto;
                height: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-title">
            <i class="fas fa-user-shield"></i>
            MSWDO Admin
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin_services.php"><i class="fas fa-hands-helping"></i> Services</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li class="active"><a href="manage_programs.php"><i class="fas fa-calendar-alt"></i> Programs</a></li>
            <li><a href="applicationhistory_admin.php"><i class="fas fa-history"></i> Application History</a></li>
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
                <h1>Municipal Social Welfare & Development Office - Gloria (Admin)</h1>
            </div>
            <div class="ms-auto d-flex align-items-center">
                <div class="dropdown">
                    <div class="user-profile" id="user-profile" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="img\adminprof.png" alt="Admin">
                        <span><?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?></span>
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
                <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Manage Programs</li>
            </ol>
        </nav>
        
        <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Stats Card -->
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stats-info">
                <h3><?php echo $successful_count; ?></h3>
                <p>Successful Programs</p>
            </div>
        </div>
        
        <div class="content">
            <div class="page-header">
                <h2 class="page-title">Program Management</h2>
                <div class="filter-container">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <form action="" method="get">
                            <input type="text" class="search-input" name="search" placeholder="Search programs..." value="<?php echo htmlspecialchars($search_query); ?>">
                        </form>
                    </div>
                    <form action="" method="get" class="d-flex gap-2">
                        <select class="filter-select" name="status" onchange="this.form.submit()">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="upcoming" <?php echo $status_filter == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="ongoing" <?php echo $status_filter == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
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
                    </form>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                        <i class="fas fa-calendar-plus me-2"></i> Add New Program
                    </button>
                </div>
            </div>
            
            <?php if (empty($programs)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="empty-title">No Programs Found</h3>
                <p class="empty-description">There are no programs matching your search criteria. Try adjusting your filters or add a new program.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="programsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $program): ?>
                        <tr>
                            <td><?php echo $program['id']; ?></td>
                            <td><?php echo htmlspecialchars($program['title']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($program['date'])); ?></td>
                            <td><?php echo htmlspecialchars($program['location']); ?></td>
                            <td><?php echo htmlspecialchars($program['category']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($program['status']); ?>">
                                    <?php echo ucfirst($program['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="action-btn btn-edit" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editProgramModal" 
                                        data-id="<?php echo $program['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($program['title']); ?>"
                                        data-date="<?php echo $program['date']; ?>"
                                        data-location="<?php echo htmlspecialchars($program['location']); ?>"
                                        data-description="<?php echo htmlspecialchars($program['description']); ?>"
                                        data-status="<?php echo $program['status']; ?>"
                                        data-category="<?php echo htmlspecialchars($program['category']); ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                
                                <?php if ($program['status'] != 'completed'): ?>
                                <button type="button" class="action-btn btn-complete" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#completeModal" 
                                        data-id="<?php echo $program['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($program['title']); ?>">
                                    <i class="fas fa-check-circle"></i> Mark Complete
                                </button>
                                <?php endif; ?>
                                
                                <button type="button" class="action-btn btn-delete" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteProgramModal" 
                                        data-id="<?php echo $program['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($program['title']); ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Program Modal -->
    <div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProgramModalLabel">Add New Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Program Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Program Date</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Health">Health</option>
                                        <option value="Education">Education</option>
                                        <option value="Livelihood">Livelihood</option>
                                        <option value="Social Protection">Social Protection</option>
                                        <option value="Disaster Response">Disaster Response</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="upcoming">Upcoming</option>
                                        <option value="ongoing">Ongoing</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_program" class="btn btn-primary">Add Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Program Modal -->
    <div class="modal fade" id="editProgramModal" tabindex="-1" aria-labelledby="editProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProgramModalLabel">Edit Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="edit_program_id" name="program_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_title">Program Title</label>
                                    <input type="text" class="form-control" id="edit_title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_date">Program Date</label>
                                    <input type="date" class="form-control" id="edit_date" name="date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_location">Location</label>
                                    <input type="text" class="form-control" id="edit_location" name="location" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_category">Category</label>
                                    <select class="form-select" id="edit_category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Health">Health</option>
                                        <option value="Education">Education</option>
                                        <option value="Livelihood">Livelihood</option>
                                        <option value="Social Protection">Social Protection</option>
                                        <option value="Disaster Response">Disaster Response</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_status">Status</label>
                                    <select class="form-select" id="edit_status" name="status" required>
                                        <option value="upcoming">Upcoming</option>
                                        <option value="ongoing">Ongoing</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_program" class="btn btn-success">Update Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Program Modal -->
    <div class="modal fade" id="deleteProgramModal" tabindex="-1" aria-labelledby="deleteProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteProgramModalLabel">Delete Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="delete_program_id" name="program_id">
                        <p>Are you sure you want to delete this program? This action cannot be undone.</p>
                        <p><strong>Program:</strong> <span id="delete_program_title"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_program" class="btn btn-danger">Delete Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Program Modal -->
    <div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completeModalLabel">Mark Program as Completed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="complete_program_id" name="program_id">
                        <p>Are you sure you want to mark this program as completed?</p>
                        <p><strong>Program:</strong> <span id="complete_program_title"></span></p>
                        
                        <p class="mt-3">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Completed programs will be displayed on the dashboard.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="mark_completed" class="btn btn-primary">Mark as Completed</button>
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
            
            // Initial check
            checkWidth();
            
            // Check on resize
            window.addEventListener("resize", checkWidth);
            
            // Edit Program Modal
            const editProgramModal = document.getElementById('editProgramModal');
            if (editProgramModal) {
                editProgramModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const programId = button.getAttribute('data-id');
                    const programTitle = button.getAttribute('data-title');
                    const programDate = button.getAttribute('data-date');
                    const programLocation = button.getAttribute('data-location');
                    const programDescription = button.getAttribute('data-description');
                    const programStatus = button.getAttribute('data-status');
                    const programCategory = button.getAttribute('data-category');
                    
                    const modal = this;
                    modal.querySelector('#edit_program_id').value = programId;
                    modal.querySelector('#edit_title').value = programTitle;
                    modal.querySelector('#edit_date').value = programDate;
                    modal.querySelector('#edit_location').value = programLocation;
                    modal.querySelector('#edit_description').value = programDescription;
                    modal.querySelector('#edit_status').value = programStatus;
                    modal.querySelector('#edit_category').value = programCategory;
                });
            }
            
            // Delete Program Modal
            const deleteProgramModal = document.getElementById('deleteProgramModal');
            if (deleteProgramModal) {
                deleteProgramModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const programId = button.getAttribute('data-id');
                    const programTitle = button.getAttribute('data-title');
                    
                    const modal = this;
                    modal.querySelector('#delete_program_id').value = programId;
                    modal.querySelector('#delete_program_title').textContent = programTitle;
                });
            }
            
            // Complete Program Modal
            const completeModal = document.getElementById('completeModal');
            if (completeModal) {
                completeModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const programId = button.getAttribute('data-id');
                    const programTitle = button.getAttribute('data-title');
                    
                    const modal = this;
                    modal.querySelector('#complete_program_id').value = programId;
                    modal.querySelector('#complete_program_title').textContent = programTitle;
                });
            }
        });
    </script>
</body>
</html>

