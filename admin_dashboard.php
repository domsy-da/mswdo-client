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

// Fetch counts for dashboard cards
$total_users = 0;
$pending_services = 0;
$completed_services = 0;
$successful_programs = 0;
$upcoming_programs = 0;

try {
  // Count total users
  $sql = "SELECT COUNT(*) as count FROM user_form WHERE user_type = 'user'";
  $result = $conn->query($sql);
  if ($row = $result->fetch_assoc()) {
      $total_users = $row['count'];
  }
  
  // Count pending services
  $sql = "SELECT COUNT(*) as count FROM application_requests WHERE status = 'Pending'";
  $result = $conn->query($sql);
  if ($row = $result->fetch_assoc()) {
      $pending_services = $row['count'];
  }
  
  // Count completed services
  $sql = "SELECT COUNT(*) as count FROM application_requests WHERE status = 'Approved'";
  $result = $conn->query($sql);
  if ($row = $result->fetch_assoc()) {
      $completed_services = $row['count'];
  }
  
  // Count successful programs
  $sql = "SELECT COUNT(*) as count FROM programs WHERE status = 'completed'";
  $result = $conn->query($sql);
  if ($row = $result->fetch_assoc()) {
      $successful_programs = $row['count'];
  }
  
  // Count upcoming programs
  $sql = "SELECT COUNT(*) as count FROM programs WHERE status = 'upcoming'";
  $result = $conn->query($sql);
  if ($row = $result->fetch_assoc()) {
      $upcoming_programs = $row['count'];
  }
} catch (Exception $e) {
  // Handle database errors
}

// Fetch recent applications
$recent_applications = [];
try {
  $sql = "SELECT id, client_name, request_purpose, application_date, status 
          FROM application_requests 
          ORDER BY created_at DESC LIMIT 5";
  $result = $conn->query($sql);
  while ($row = $result->fetch_assoc()) {
      $recent_applications[] = $row;
  }
} catch (Exception $e) {
  // Handle database errors
}

// Fetch upcoming programs
$upcoming_program_list = [];
try {
  $sql = "SELECT id, title, date, location, status 
          FROM programs 
          WHERE status = 'upcoming' 
          ORDER BY date ASC LIMIT 5";
  $result = $conn->query($sql);
  while ($row = $result->fetch_assoc()) {
      $upcoming_program_list[] = $row;
  }
} catch (Exception $e) {
  // Handle database errors
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - MSWDO BMS</title>
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
      
      .stat-icon.purple {
          background-color: #7367f0;
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
      
      .section-title {
          font-size: 1.3rem;
          font-weight: 600;
          margin-bottom: 20px;
          color: var(--dark-text);
          padding-bottom: 10px;
          border-bottom: 1px solid var(--border-color);
          display: flex;
          justify-content: space-between;
          align-items: center;
      }
      
      .section-title a {
          font-size: 0.9rem;
          color: var(--secondary-color);
          text-decoration: none;
          display: flex;
          align-items: center;
          gap: 5px;
      }
      
      .section-title a:hover {
          text-decoration: underline;
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
      
      .status-upcoming {
          background-color: rgba(115, 103, 240, 0.1);
          color: #7367f0;
      }
      
      .status-completed {
          background-color: rgba(0, 207, 232, 0.1);
          color: #00cfe8;
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
      
      .quick-actions {
          margin-top: 30px;
      }
      
      .action-buttons {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
          gap: 15px;
      }
      
      .action-btn-card {
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
      
      .action-btn-card:hover {
          transform: translateY(-3px);
          box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
          border-color: var(--secondary-color);
          color: var(--secondary-color);
      }
      
      .action-btn-card i {
          font-size: 2rem;
          margin-bottom: 10px;
          color: var(--secondary-color);
      }
      
      .action-btn-card span {
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
          
          .welcome-card {
              padding: 20px;
          }
          
          .welcome-card h2 {
              font-size: 1.5rem;
          }
          
          .welcome-card p {
              font-size: 1rem;
          }
          
          .stats-container {
              grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
          }
      }
      
      @media (max-width: 768px) {
          .stats-container {
              grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
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
          
          .action-btn-card {
              padding: 15px;
          }
          
          .action-btn-card i {
              font-size: 1.8rem;
          }
          
          .table td, .table th {
              padding: 10px;
          }
          
          .action-btn {
              padding: 4px 8px;
              font-size: 0.8rem;
          }
      }
      
      @media (max-width: 576px) {
          .navbar-brand h1 {
              display: none;
          }
          
          .content {
              padding: 15px;
          }
          
          .welcome-card {
              padding: 15px;
          }
          
          .stats-container {
              grid-template-columns: 1fr;
          }
          
          .action-buttons {
              grid-template-columns: 1fr 1fr;
          }
          
          .breadcrumb {
              padding: 10px;
          }
          
          .user-profile span {
              display: none;
          }
          
          .section-title {
              flex-direction: column;
              align-items: flex-start;
              gap: 10px;
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
          <li class="active"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a href="admin_services.php"><i class="fas fa-hands-helping"></i> Services</a></li>
          <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
          <li><a href="manage_programs.php"><i class="fas fa-calendar-alt"></i> Programs</a></li>
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
              <li class="breadcrumb-item active">Dashboard</li>
          </ol>
      </nav>
      
      <div class="content">
          <div class="welcome-card">
              <h2>Welcome to MSWDO Admin Dashboard, <?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?>!</h2>
              <p>Manage users, services, programs, and monitor system activities all in one place.</p>
          </div>
          
          <div class="stats-container">
              <div class="stat-card">
                  <div class="stat-icon blue">
                      <i class="fas fa-users"></i>
                  </div>
                  <div class="stat-info">
                      <h3><?php echo $total_users; ?></h3>
                      <p>Total Users</p>
                  </div>
              </div>
              
              <div class="stat-card">
                  <div class="stat-icon orange">
                      <i class="fas fa-clock"></i>
                  </div>
                  <div class="stat-info">
                      <h3><?php echo $pending_services; ?></h3>
                      <p>Pending Services</p>
                  </div>
              </div>
              
              <div class="stat-card">
                  <div class="stat-icon green">
                      <i class="fas fa-check-circle"></i>
                  </div>
                  <div class="stat-info">
                      <h3><?php echo $completed_services; ?></h3>
                      <p>Completed Services</p>
                  </div>
              </div>
              
              <div class="stat-card">
                  <div class="stat-icon purple">
                      <i class="fas fa-calendar-check"></i>
                  </div>
                  <div class="stat-info">
                      <h3><?php echo $successful_programs; ?></h3>
                      <p>Successful Programs</p>
                  </div>
              </div>
              
              <div class="stat-card">
                  <div class="stat-icon red">
                      <i class="fas fa-calendar-alt"></i>
                  </div>
                  <div class="stat-info">
                      <h3><?php echo $upcoming_programs; ?></h3>
                      <p>Upcoming Programs</p>
                  </div>
              </div>
          </div>
      </div>
      
      <div class="content">
          <h3 class="section-title">
              <span>Recent Service Applications</span>
              <a href="admin_services.php">View All <i class="fas fa-arrow-right"></i></a>
          </h3>
          
          <div class="table-responsive">
              <table class="table table-hover">
                  <thead>
                      <tr>
                          <th>ID</th>
                          <th>Client Name</th>
                          <th>Service Type</th>
                          <th>Date</th>
                          <th>Status</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (empty($recent_applications)): ?>
                      <tr>
                          <td colspan="6" class="text-center">No recent applications found</td>
                      </tr>
                      <?php else: ?>
                          <?php foreach ($recent_applications as $application): ?>
                          <tr>
                              <td>#<?php echo $application['id']; ?></td>
                              <td><?php echo htmlspecialchars($application['client_name']); ?></td>
                              <td><?php echo htmlspecialchars($application['request_purpose']); ?></td>
                              <td><?php echo date('M d, Y', strtotime($application['application_date'])); ?></td>
                              <td>
                                  <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                                      <?php echo $application['status']; ?>
                                  </span>
                              </td>
                              <td>
                                  <a href="view_application.php?id=<?php echo $application['id']; ?>" class="action-btn btn-view">
                                      <i class="fas fa-eye"></i> View
                                  </a>
                                  <a href="update_application.php?id=<?php echo $application['id']; ?>" class="action-btn btn-edit">
                                      <i class="fas fa-edit"></i> Update
                                  </a>
                              </td>
                          </tr>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
              </table>
          </div>
      </div>
      
      <div class="content">
          <h3 class="section-title">
              <span>Upcoming Programs</span>
              <a href="manage_programs.php">View All <i class="fas fa-arrow-right"></i></a>
          </h3>
          
          <div class="table-responsive">
              <table class="table table-hover">
                  <thead>
                      <tr>
                          <th>ID</th>
                          <th>Program Title</th>
                          <th>Date</th>
                          <th>Location</th>
                          <th>Status</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (empty($upcoming_program_list)): ?>
                      <tr>
                          <td colspan="6" class="text-center">No upcoming programs found</td>
                      </tr>
                      <?php else: ?>
                          <?php foreach ($upcoming_program_list as $program): ?>
                          <tr>
                              <td>#<?php echo $program['id']; ?></td>
                              <td><?php echo htmlspecialchars($program['title']); ?></td>
                              <td><?php echo date('M d, Y', strtotime($program['date'])); ?></td>
                              <td><?php echo htmlspecialchars($program['location']); ?></td>
                              <td>
                                  <span class="status-badge status-<?php echo strtolower($program['status']); ?>">
                                      <?php echo ucfirst($program['status']); ?>
                                  </span>
                              </td>
                              <td>
                                  <a href="view_program.php?id=<?php echo $program['id']; ?>" class="action-btn btn-view">
                                      <i class="fas fa-eye"></i> View
                                  </a>
                                  <a href="update_program.php?id=<?php echo $program['id']; ?>" class="action-btn btn-edit">
                                      <i class="fas fa-edit"></i> Edit
                                  </a>
                              </td>
                          </tr>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
              </table>
          </div>
      </div>
      
      <div class="content">
          <h3 class="section-title">Quick Actions</h3>
          <div class="action-buttons">
              <a href="admin_services.php" class="action-btn-card">
                  <i class="fas fa-hands-helping"></i>
                  <span>Manage Services</span>
              </a>
              <a href="manage_users.php" class="action-btn-card">
                  <i class="fas fa-users"></i>
                  <span>Manage Users</span>
              </a>
              <a href="#" class="action-btn-card" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                  <i class="fas fa-calendar-plus"></i>
                  <span>Create Program</span>
              </a>
          </div>
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
                <form action="manage_programs.php" method="post">
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
</script>
</body>
</html>

