<?php
require_once '../config/db.php';
$page_title = 'Mark Attendance';

$employee = null;
$attendance_marked = false;
$error = '';
$success = '';

// Handle search (from form or URL parameter)
$search_term = '';
if (isset($_POST['search'])) {
  $search_term = trim($_POST['search_term'] ?? '');
} elseif (isset($_GET['search'])) {
  $search_term = trim($_GET['search'] ?? '');
}

if (!empty($search_term)) {
  if (empty($search_term)) {
    $error = 'Please enter Employee ID or NIC to search.';
  } else {
    $stmt = $pdo->prepare("
            SELECT e.*, d.name as department_name 
            FROM employees e 
            LEFT JOIN departments d ON e.department_id = d.id 
            WHERE (e.employee_id = ? OR e.nic = ?) AND e.status = 'active'
        ");
    $stmt->execute([$search_term, $search_term]);
    $employee = $stmt->fetch();

    if (!$employee) {
      $error = 'Employee not found. Please check Employee ID or NIC.';
    } else {
      // Check if attendance already marked today
      $today = date('Y-m-d');
      $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?");
      $stmt->execute([$employee['id'], $today]);
      $existing_attendance = $stmt->fetch();

      if ($existing_attendance) {
        $attendance_marked = true;
        $success = 'Attendance already marked today as: ' . ucfirst($existing_attendance['status']);
      }
    }
  }
}

// Handle attendance marking  
if (isset($_POST['mark_attendance'])) {
  // Re-fetch employee if not already loaded
  if (!$employee && !empty($_POST['search_term'])) {
    $search_term_for_attendance = trim($_POST['search_term']);
    $stmt = $pdo->prepare("
      SELECT e.*, d.name as department_name 
      FROM employees e 
      LEFT JOIN departments d ON e.department_id = d.id 
      WHERE (e.employee_id = ? OR e.nic = ?) AND e.status = 'active'
    ");
    $stmt->execute([$search_term_for_attendance, $search_term_for_attendance]);
    $employee = $stmt->fetch();
  }

  if ($employee) {
    $attendance_status = $_POST['attendance_status'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    $today = date('Y-m-d');
    $current_time = date('H:i:s');

    if (empty($attendance_status)) {
      $error = 'Please select attendance status.';
    } else {
      try {
        // Check if attendance already exists
        $stmt = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND attendance_date = ?");
        $stmt->execute([$employee['id'], $today]);
        $existing = $stmt->fetch();

        if ($existing) {
          // Update existing attendance
          $stmt = $pdo->prepare("
                    UPDATE attendance 
                    SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE employee_id = ? AND attendance_date = ?
                ");
          $stmt->execute([$attendance_status, $notes, $employee['id'], $today]);
          flash_message('Attendance updated successfully for ' . $employee['first_name'] . ' ' . $employee['last_name'] . '!', 'success');
        } else {
          // Insert new attendance
          $check_in_time = ($attendance_status != 'absent' && $attendance_status != 'leave') ? $current_time : null;

          $stmt = $pdo->prepare("
                    INSERT INTO attendance (employee_id, attendance_date, status, check_in_time, notes) 
                    VALUES (?, ?, ?, ?, ?)
                ");
          $stmt->execute([$employee['id'], $today, $attendance_status, $check_in_time, $notes]);
          flash_message('Attendance marked successfully for ' . $employee['first_name'] . ' ' . $employee['last_name'] . '!', 'success');
        }

        // Redirect to prevent form resubmission and show flash message with employee context
        $search_term = urlencode($_POST['search_term'] ?? '');
        redirect('mark_attendance.php?search=' . $search_term);

      } catch (PDOException $e) {
        flash_message('Error marking attendance. Please try again.', 'error');
        $search_term = urlencode($_POST['search_term'] ?? '');
        redirect('mark_attendance.php?search=' . $search_term);
      }
    }
  } else {
    flash_message('Employee not found. Please search again.', 'error');
  }
}

include '../includes/header.php';
?>

<div class="current-date"></div>

<div class="card">
  <div class="card-header">
    <h1 class="card-title">
      <i class="fas fa-check-circle"></i> Mark Attendance
    </h1>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error">
      <i class="fas fa-exclamation-triangle"></i>
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i>
      <?php echo htmlspecialchars($success); ?>
    </div>
  <?php endif; ?>

  <!-- Search Employee -->
  <div class="search-container">
    <h3><i class="fas fa-search"></i> Find Employee</h3>
    <form method="POST" action="" class="search-box">
      <div class="form-group">
        <label for="search_term" class="form-label">Enter Employee ID or NIC</label>
        <input type="text" id="search_term" name="search_term" class="form-input"
          placeholder="e.g., EMP001 or 123456789V"
          value="<?php echo htmlspecialchars($_POST['search_term'] ?? $_GET['search'] ?? ''); ?>" required>
      </div>
      <button type="submit" name="search" class="btn btn-primary">
        <i class="fas fa-search"></i> Search Employee
      </button>
    </form>
  </div>

  <?php if ($employee): ?>
    <!-- Employee Details -->
    <div class="card" style="background-color: #f8f9fa;">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user"></i> Employee Details</h3>
      </div>

      <div class="grid grid-2">
        <div>
          <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($employee['employee_id']); ?></p>
          <p><strong>Name:</strong>
            <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></p>
          <p><strong>NIC:</strong> <?php echo htmlspecialchars($employee['nic']); ?></p>
        </div>
        <div>
          <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department_name']); ?></p>
          <p><strong>Position:</strong> <?php echo htmlspecialchars($employee['position']); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
        </div>
      </div>
    </div>

    <!-- Mark Attendance Form -->
    <?php if (!$attendance_marked): ?>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-clock"></i> Mark Today's Attendance</h3>
        </div>

        <form method="POST" action="">
          <input type="hidden" name="search_term"
            value="<?php echo htmlspecialchars($_POST['search_term'] ?? $_GET['search'] ?? ''); ?>">

          <div class="form-group">
            <label for="attendance_status" class="form-label">Attendance Status</label>
            <select id="attendance_status" name="attendance_status" class="form-select" required>
              <option value="">Select Status</option>
              <option value="present">Present</option>
              <option value="late">Late</option>
              <option value="halfday">Half Day</option>
              <option value="absent">Absent</option>
              <option value="leave">Leave</option>
            </select>
          </div>

          <div class="form-group">
            <label for="notes" class="form-label">Notes (Optional)</label>
            <textarea id="notes" name="notes" class="form-textarea"
              placeholder="Add any additional notes about attendance..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
          </div>

          <div class="form-group">
            <button type="submit" name="mark_attendance" class="btn btn-success">
              <i class="fas fa-check"></i> Mark Attendance
            </button>
            <a href="mark_attendance.php" class="btn btn-warning">
              <i class="fas fa-search"></i> Search Another Employee
            </a>
          </div>
        </form>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-info-circle"></i> Attendance Status</h3>
        </div>
        <p>Attendance has been marked for today. If you need to make changes, please contact your administrator.</p>
        <a href="mark_attendance.php" class="btn btn-primary">
          <i class="fas fa-search"></i> Search Another Employee
        </a>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php if ($employee): ?>
  <!-- Employee Attendance History -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-history"></i>
        Attendance History - <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
      </h3>
    </div>

    <?php
    // Fetch employee's attendance history for the last 30 days
    $stmt = $pdo->prepare("
    SELECT 
      attendance_date,
      status,
      check_in_time,
      check_out_time,
      notes,
      created_at
    FROM attendance 
    WHERE employee_id = ? 
    ORDER BY attendance_date DESC 
    LIMIT 30
  ");
    $stmt->execute([$employee['id']]);
    $attendance_history = $stmt->fetchAll();

    // Calculate attendance statistics
    $stats = [
      'present' => 0,
      'late' => 0,
      'halfday' => 0,
      'absent' => 0,
      'leave' => 0
    ];

    foreach ($attendance_history as $record) {
      if (isset($stats[$record['status']])) {
        $stats[$record['status']]++;
      }
    }

    $total_days = count($attendance_history);
    $working_days = $stats['present'] + $stats['late'] + $stats['halfday'];
    $attendance_percentage = $total_days > 0 ? round(($working_days / $total_days) * 100, 1) : 0;
    ?>

    <?php if ($total_days > 0): ?>
      <!-- Attendance Summary -->
      <div class="attendance-summary">
        <div class="summary-grid">
          <div class="summary-card">
            <div class="summary-number"><?php echo $total_days; ?></div>
            <div class="summary-label">Total Days</div>
          </div>
          <div class="summary-card">
            <div class="summary-number"><?php echo $working_days; ?></div>
            <div class="summary-label">Working Days</div>
          </div>
          <div class="summary-card">
            <div class="summary-number"><?php echo $stats['present']; ?></div>
            <div class="summary-label">Present</div>
          </div>
          <div class="summary-card">
            <div class="summary-number"><?php echo $stats['late']; ?></div>
            <div class="summary-label">Late</div>
          </div>
          <div class="summary-card">
            <div class="summary-number"><?php echo $stats['halfday']; ?></div>
            <div class="summary-label">Half Day</div>
          </div>
          <div class="summary-card">
            <div class="summary-number"><?php echo $stats['absent']; ?></div>
            <div class="summary-label">Absent</div>
          </div>
          <div class="summary-card">
            <div class="summary-number"><?php echo $stats['leave']; ?></div>
            <div class="summary-label">Leave</div>
          </div>
          <div class="summary-card">
            <div class="summary-number"><?php echo $attendance_percentage; ?>%</div>
            <div class="summary-label">Attendance Rate</div>
          </div>
        </div>
      </div>

      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Status</th>
              <th>Check In</th>
              <th>Check Out</th>
              <th>Notes</th>
              <th>Recorded</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($attendance_history as $record): ?>
              <tr>
                <td>
                  <strong><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></strong><br>
                  <small><?php echo date('l', strtotime($record['attendance_date'])); ?></small>
                </td>
                <td>
                  <span class="status-badge status-<?php echo $record['status']; ?>">
                    <?php echo ucfirst($record['status']); ?>
                  </span>
                </td>
                <td>
                  <?php
                  if ($record['check_in_time']) {
                    echo date('h:i A', strtotime($record['check_in_time']));
                  } else {
                    echo '<span class="text-muted">--</span>';
                  }
                  ?>
                </td>
                <td>
                  <?php
                  if ($record['check_out_time']) {
                    echo date('h:i A', strtotime($record['check_out_time']));
                  } else {
                    echo '<span class="text-muted">--</span>';
                  }
                  ?>
                </td>
                <td>
                  <?php
                  if (!empty($record['notes'])) {
                    echo '<small>' . htmlspecialchars($record['notes']) . '</small>';
                  } else {
                    echo '<span class="text-muted">--</span>';
                  }
                  ?>
                </td>
                <td>
                  <small><?php echo date('M d, h:i A', strtotime($record['created_at'])); ?></small>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="card-footer">
        <p class="text-muted">
          <i class="fas fa-info-circle"></i>
          Showing last <?php echo count($attendance_history); ?> attendance records
        </p>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <p>No attendance records found for this employee.</p>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-info-circle"></i> Instructions</h3>
  </div>
  <div class="grid grid-2">
    <div>
      <h4>How to Mark Attendance:</h4>
      <ol style="margin-left: 2rem; margin-top: 1rem;">
        <li>Enter your Employee ID or NIC in the search box</li>
        <li>Click "Search Employee" to find your record</li>
        <li>Select your attendance status for today</li>
        <li>Add any notes if necessary</li>
        <li>Click "Mark Attendance" to submit</li>
      </ol>
    </div>
    <div>
      <h4>Attendance Status Options:</h4>
      <ul style="margin-left: 2rem; margin-top: 1rem;">
        <li><strong>Present:</strong> On time and working full day</li>
        <li><strong>Late:</strong> Arrived late but working full day</li>
        <li><strong>Half Day:</strong> Working only half day</li>
        <li><strong>Absent:</strong> Not present at work</li>
        <li><strong>Leave:</strong> On approved leave</li>
      </ul>
    </div>
  </div>
</div>

<script>
  // Auto-submit search form if URL has search parameter
  document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get('search');
    const searchInput = document.getElementById('search_term');

    if (searchTerm && searchInput && searchInput.value === searchTerm) {
      // Only auto-submit if we don't already have employee data displayed
      const employeeSection = document.querySelector('.card[style*="background-color: #f8f9fa"]');
      if (!employeeSection) {
        document.querySelector('form.search-box').submit();
      }
    }
  });
</script>

<?php include '../includes/footer.php'; ?>

