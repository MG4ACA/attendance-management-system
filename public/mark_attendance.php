<?php
require_once '../config/db.php';
$page_title = 'Mark Attendance';

$employee = null;
$attendance_marked = false;
$error = '';
$success = '';

// Handle search
if (isset($_POST['search'])) {
    $search_term = trim($_POST['search_term'] ?? '');
    
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
if (isset($_POST['mark_attendance']) && $employee) {
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
                $success = 'Attendance updated successfully!';
            } else {
                // Insert new attendance
                $check_in_time = ($attendance_status != 'absent' && $attendance_status != 'leave') ? $current_time : null;
                
                $stmt = $pdo->prepare("
                    INSERT INTO attendance (employee_id, attendance_date, status, check_in_time, notes) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$employee['id'], $today, $attendance_status, $check_in_time, $notes]);
                $success = 'Attendance marked successfully!';
            }
            
            $attendance_marked = true;
            
        } catch (PDOException $e) {
            $error = 'Error marking attendance. Please try again.';
        }
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
                <input 
                    type="text" 
                    id="search_term" 
                    name="search_term" 
                    class="form-input" 
                    placeholder="e.g., EMP001 or 123456789V"
                    value="<?php echo htmlspecialchars($_POST['search_term'] ?? ''); ?>"
                    required
                >
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
                <p><strong>Name:</strong> <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></p>
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
            <input type="hidden" name="search_term" value="<?php echo htmlspecialchars($_POST['search_term'] ?? ''); ?>">
            
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
                <textarea 
                    id="notes" 
                    name="notes" 
                    class="form-textarea" 
                    placeholder="Add any additional notes about attendance..."
                ><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
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

<?php include '../includes/footer.php'; ?>