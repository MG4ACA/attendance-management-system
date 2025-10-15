<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Employee Attendance Management System</title>
  <link rel="stylesheet" href="../assets/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-logo">
        <h2><i class="fas fa-user-clock"></i> Attendance System</h2>
      </div>
      <ul class="nav-menu">
        <li class="nav-item">
          <a href="index.php" class="nav-link">
            <i class="fas fa-home"></i> Home
          </a>
        </li>
        <li class="nav-item">
          <a href="mark_attendance.php" class="nav-link">
            <i class="fas fa-check-circle"></i> Mark Attendance
          </a>
        </li>
        <?php if (isset($_SESSION['admin_id'])): ?>
          <li class="nav-item">
            <a href="manage_employees.php" class="nav-link">
              <i class="fas fa-users"></i> Manage Employees
            </a>
          </li>
          <li class="nav-item">
            <a href="attendance_report.php" class="nav-link">
              <i class="fas fa-chart-bar"></i> Reports
            </a>
          </li>
          <li class="nav-item">
            <a href="admin_logout.php" class="nav-link">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a href="admin_login.php" class="nav-link">
              <i class="fas fa-sign-in-alt"></i> Admin Login
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <main class="main-content">
    <?php
    $flash = get_flash_message();
    if ($flash):
      ?>
      <div class="alert alert-<?php echo $flash['type']; ?>" id="flash-alert">
        <i class="fas fa-info-circle"></i>
        <?php echo htmlspecialchars($flash['message']); ?>
      </div>
      <script>
        // Show toast notification for flash messages
        document.addEventListener('DOMContentLoaded', function () {
          const flashAlert = document.getElementById('flash-alert');
          if (flashAlert) {
            showToast('<?php echo addslashes($flash['message']); ?>', '<?php echo $flash['type']; ?>');
            // Hide the regular alert after showing toast
            setTimeout(() => {
              flashAlert.style.display = 'none';
            }, 100);
          }
        });
      </script>
    <?php endif; ?>

