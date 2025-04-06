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

// Count total applications for the badge
$total_applications = 0;
try {
  $count_sql = "SELECT COUNT(*) as count FROM application_requests WHERE status != 'Success'";
  $count_result = $conn->query($count_sql);
  if ($count_row = $count_result->fetch_assoc()) {
      $total_applications = $count_row['count'];
  }
} catch (Exception $e) {
  // Handle error silently
}

// Initialize messages
$success_msg = "";
$error_msg = "";

// Set default filter values
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Build the SQL query based on filters
$sql_conditions = ["status != 'Success'"]; // Exclude successful applications
$sql_params = [];
$param_types = "";

if ($status_filter != 'all') {
  $sql_conditions[] = "status = ?";
  $sql_params[] = $status_filter;
  $param_types .= "s";
}

if (!empty($search_query)) {
  $sql_conditions[] = "(client_name LIKE ? OR request_purpose LIKE ? OR id LIKE ?)";
  $search_term = "%$search_query%";
  $sql_params[] = $search_term;
  $sql_params[] = $search_term;
  $sql_params[] = $search_term;
  $param_types .= "sss";
}

$sql = "SELECT id, client_name, request_purpose, application_date, status, amount 
      FROM application_requests";

if (!empty($sql_conditions)) {
  $sql .= " WHERE " . implode(" AND ", $sql_conditions);
}

$sql .= " ORDER BY application_date DESC";

// Fetch applications based on filters
$applications = [];
try {
  $stmt = null;
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
          $applications[] = $row;
      }
  }
  
  // Close the statement if it exists
  if ($stmt !== null) {
      $stmt->close();
  }
} catch (Exception $e) {
  $error_msg = "Error fetching applications: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Application History - MSWDO BMS</title>
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
      
      .badge-count {
          background-color: var(--accent-color);
          color: white;
          font-size: 0.7rem;
          font-weight: 600;
          padding: 2px 6px;
          border-radius: 10px;
          margin-left: 8px;
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
      
      /* Services Page Styles */
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
      
      .status-processing {
          background-color: rgba(115, 103, 240, 0.1);
          color: #7367f0;
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
      
      .pagination-container {
          display: flex;
          justify-content: center;
          margin-top: 20px;
      }
      
      .pagination {
          display: flex;
          list-style: none;
          padding: 0;
          margin: 0;
      }
      
      .pagination li {
          margin: 0 5px;
      }
      
      .pagination li a {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 35px;
          height: 35px;
          border-radius: 5px;
          background-color: #fff;
          color: var(--dark-text);
          text-decoration: none;
          border: 1px solid var(--border-color);
          transition: all 0.3s ease;
      }
      
      .pagination li.active a {
          background-color: var(--secondary-color);
          color: white;
          border-color: var(--secondary-color);
      }
      
      .pagination li a:hover {
          background-color: var(--light-bg);
      }
      
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
      }
      
      @media (max-width: 768px) {
          .filter-container {
              flex-direction: column;
              align-items: flex-start;
          }
          
          .filter-select {
              width: 100%;
          }
          
          .action-btn {
              padding: 4px 8px;
              font-size: 0.8rem;
          }
          
          .table td, .table th {
              padding: 10px;
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
          
          .pagination li a {
              width: 30px;
              height: 30px;
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
          <li><a href="manage_programs.php"><i class="fas fa-calendar-alt"></i> Programs</a></li>
          <li class="active"><a href="applicationhistory_admin.php"><i class="fas fa-history"></i> Application History <span class="badge-count"><?php echo $total_applications; ?></span></a></li>
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
              <li class="breadcrumb-item active">Application History</li>
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
      
      <div class="content">
          <div class="page-header">
              <h2 class="page-title">Application History</h2>
              <div class="filter-container">
                  <div class="search-box">
                      <i class="fas fa-search search-icon"></i>
                      <form action="" method="get">
                          <input type="text" class="search-input" name="search" placeholder="Search by name, service..." value="<?php echo htmlspecialchars($search_query); ?>">
                      </form>
                  </div>
                  <form action="" method="get" class="d-flex">
                      <select class="filter-select" name="status" onchange="this.form.submit()">
                          <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                          <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                          <option value="Processing" <?php echo $status_filter == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                          <option value="Approved" <?php echo $status_filter == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                          <option value="Rejected" <?php echo $status_filter == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                      </select>
                      <?php if (!empty($search_query)): ?>
                          <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                      <?php endif; ?>
                  </form>
              </div>
          </div>
          
          <?php if (empty($applications)): ?>
          <div class="empty-state">
              <div class="empty-icon">
                  <i class="fas fa-file-alt"></i>
              </div>
              <h3 class="empty-title">No Applications Found</h3>
              <p class="empty-description">There are no service applications matching your search criteria. Try adjusting your filters or search terms.</p>
          </div>
          <?php else: ?>
          <div class="table-responsive">
              <table class="table table-hover">
                  <thead>
                      <tr>
                          <th>ID</th>
                          <th>Client Name</th>
                          <th>Service Type</th>
                          <th>Date</th>
                          <th>Amount</th>
                          <th>Status</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ($applications as $application): ?>
                      <tr>
                          <td>#<?php echo $application['id']; ?></td>
                          <td><?php echo htmlspecialchars($application['client_name']); ?></td>
                          <td><?php echo htmlspecialchars($application['request_purpose']); ?></td>
                          <td><?php echo date('M d, Y', strtotime($application['application_date'])); ?></td>
                          <td>₱<?php echo number_format($application['amount'], 2); ?></td>
                          <td>
                              <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                                  <?php echo $application['status']; ?>
                              </span>
                          </td>
                          <td>
                              <a href="admin_view_application.php?id=<?php echo $application['id']; ?>" class="action-btn btn-view">
                                  <i class="fas fa-eye"></i> View
                              </a>
                          </td>
                      </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
          </div>
          
          <!-- Pagination -->
          <div class="pagination-container">
              <ul class="pagination">
                  <li><a href="#"><i class="fas fa-chevron-left"></i></a></li>
                  <li class="active"><a href="#">1</a></li>
                  <li><a href="#">2</a></li>
                  <li><a href="#">3</a></li>
                  <li><a href="#"><i class="fas fa-chevron-right"></i></a></li>
              </ul>
          </div>
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
          
          // Check width on page load
          checkWidth();
          
          // Check width on window resize
          window.addEventListener("resize", checkWidth);
      });
  </script>
</body>
</html>