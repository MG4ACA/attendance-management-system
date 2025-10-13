<?php
require_once '../config/db.php';
$page_title = 'Dashboard';

// Get today's attendance statistics
$today = date('Y-m-d');

// Total employees
$stmt = $pdo->query("SELECT COUNT(*) as total FROM employees WHERE status = 'active'");
$total_employees = $stmt->fetch()['total'];

// Today's attendance counts
$stmt = $pdo->prepare("
    SELECT 
        status, 
        COUNT(*) as count 
    FROM attendance 
    WHERE attendance_date = ? 
    GROUP BY status
");
$stmt->execute([$today]);
$attendance_stats = $stmt->fetchAll();

$stats = [
    'present' => 0,
    'absent' => 0,
    'late' => 0,
    'halfday' => 0,
    'leave' => 0
];

foreach ($attendance_stats as $stat) {
    $stats[$stat['status']] = $stat['count'];
}

$total_marked = array_sum($stats);
$not_marked = $total_employees - $total_marked;

include '../includes/header.php';
?>

<div class="current-date"></div>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <i class="fas fa-tachometer-alt"></i> Dashboard - Employee Attendance Management System
        </h1>
    </div>
    
    <div class="grid grid-2">
        <div>
            <h3><i class="fas fa-info-circle"></i> System Overview</h3>
            <p>Welcome to the Employee Attendance Management System for our IT firm. This system helps track daily attendance, manage employee records, and generate comprehensive reports.</p>
            
            <h4><i class="fas fa-users"></i> Key Features:</h4>
            <ul style="margin-left: 2rem; margin-top: 1rem;">
                <li>Easy attendance marking for employees</li>
                <li>Comprehensive employee management</li>
                <li>Detailed attendance reports</li>
                <li>Department-wise attendance tracking</li>
                <li>Multiple attendance statuses (Present, Absent, Late, Half Day, Leave)</li>
            </ul>
        </div>
        
        <div>
            <h3><i class="fas fa-calendar-day"></i> Today's Quick Stats</h3>
            <div class="stats-container">
                <div class="stat-card total">
                    <div class="stat-number"><?php echo $total_employees; ?></div>
                    <div class="stat-label">Total Employees</div>
                </div>
                <div class="stat-card present">
                    <div class="stat-number"><?php echo $stats['present']; ?></div>
                    <div class="stat-label">Present</div>
                </div>
                <div class="stat-card absent">
                    <div class="stat-number"><?php echo $stats['absent'] + $not_marked; ?></div>
                    <div class="stat-label">Absent/Not Marked</div>
                </div>
                <div class="stat-card late">
                    <div class="stat-number"><?php echo $stats['late']; ?></div>
                    <div class="stat-label">Late</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-user-check"></i> For Employees</h2>
        </div>
        <p>Mark your daily attendance quickly and easily.</p>
        <div style="margin-top: 1rem;">
            <a href="mark_attendance.php" class="btn btn-primary">
                <i class="fas fa-check-circle"></i> Mark Attendance
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-user-cog"></i> For Administrators</h2>
        </div>
        <p>Manage employees and view comprehensive attendance reports.</p>
        <div style="margin-top: 1rem;">
            <?php if (is_admin_logged_in()): ?>
                <a href="manage_employees.php" class="btn btn-success">
                    <i class="fas fa-users"></i> Manage Employees
                </a>
                <a href="attendance_report.php" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> View Reports
                </a>
            <?php else: ?>
                <a href="admin_login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Admin Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($total_marked > 0): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-chart-pie"></i> Today's Attendance Summary (<?php echo date('F j, Y'); ?>)</h2>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="status-badge status-present">Present</span></td>
                    <td><?php echo $stats['present']; ?></td>
                    <td><?php echo $total_employees > 0 ? round(($stats['present'] / $total_employees) * 100, 1) : 0; ?>%</td>
                </tr>
                <tr>
                    <td><span class="status-badge status-late">Late</span></td>
                    <td><?php echo $stats['late']; ?></td>
                    <td><?php echo $total_employees > 0 ? round(($stats['late'] / $total_employees) * 100, 1) : 0; ?>%</td>
                </tr>
                <tr>
                    <td><span class="status-badge status-halfday">Half Day</span></td>
                    <td><?php echo $stats['halfday']; ?></td>
                    <td><?php echo $total_employees > 0 ? round(($stats['halfday'] / $total_employees) * 100, 1) : 0; ?>%</td>
                </tr>
                <tr>
                    <td><span class="status-badge status-leave">Leave</span></td>
                    <td><?php echo $stats['leave']; ?></td>
                    <td><?php echo $total_employees > 0 ? round(($stats['leave'] / $total_employees) * 100, 1) : 0; ?>%</td>
                </tr>
                <tr>
                    <td><span class="status-badge status-absent">Not Marked</span></td>
                    <td><?php echo $not_marked; ?></td>
                    <td><?php echo $total_employees > 0 ? round(($not_marked / $total_employees) * 100, 1) : 0; ?>%</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>