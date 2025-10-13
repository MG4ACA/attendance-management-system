<?php
require_once '../config/db.php';
require_admin_login();
$page_title = 'Attendance Reports';

// Get filter parameters
$report_type = $_GET['type'] ?? 'daily';
$date_from = $_GET['date_from'] ?? date('Y-m-d');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$department_id = $_GET['department_id'] ?? '';
$employee_id = $_GET['employee_id'] ?? '';

// Set date range based on report type
if ($report_type === 'weekly') {
    $date_from = date('Y-m-d', strtotime('monday this week'));
    $date_to = date('Y-m-d', strtotime('sunday this week'));
} elseif ($report_type === 'monthly') {
    $date_from = date('Y-m-01');
    $date_to = date('Y-m-t');
}

// Get departments for filter
$stmt = $pdo->query("SELECT * FROM departments ORDER BY name");
$departments = $stmt->fetchAll();

// Get employees for filter
$stmt = $pdo->query("SELECT id, employee_id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY employee_id");
$employees = $stmt->fetchAll();

// Build query conditions
$conditions = ["a.attendance_date BETWEEN ? AND ?"];
$params = [$date_from, $date_to];

if (!empty($department_id)) {
    $conditions[] = "e.department_id = ?";
    $params[] = $department_id;
}

if (!empty($employee_id)) {
    $conditions[] = "e.id = ?";
    $params[] = $employee_id;
}

$where_clause = implode(' AND ', $conditions);

// Get attendance records
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        e.employee_id,
        e.first_name,
        e.last_name,
        d.name as department_name
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE $where_clause
    ORDER BY a.attendance_date DESC, e.employee_id
");
$stmt->execute($params);
$attendance_records = $stmt->fetchAll();

// Get summary statistics
$stmt = $pdo->prepare("
    SELECT 
        a.status,
        COUNT(*) as count
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    WHERE $where_clause
    GROUP BY a.status
");
$stmt->execute($params);
$status_counts = $stmt->fetchAll();

$summary = [
    'present' => 0,
    'absent' => 0,
    'late' => 0,
    'halfday' => 0,
    'leave' => 0
];

foreach ($status_counts as $count) {
    $summary[$count['status']] = $count['count'];
}

$total_records = array_sum($summary);

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <i class="fas fa-chart-bar"></i> Attendance Reports
        </h1>
    </div>
    
    <!-- Filters -->
    <form method="GET" action="" class="card" style="background-color: #f8f9fa;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Report Filters</h3>
        </div>
        
        <div class="grid grid-2">
            <div class="form-group">
                <label for="type" class="form-label">Report Type</label>
                <select id="type" name="type" class="form-select" onchange="updateDateRange(this.value)">
                    <option value="daily" <?php echo $report_type === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo $report_type === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                    <option value="monthly" <?php echo $report_type === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="custom" <?php echo $report_type === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="department_id" class="form-label">Department</label>
                <select id="department_id" name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>" 
                            <?php echo $department_id == $dept['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" id="date_from" name="date_from" class="form-input" 
                       value="<?php echo $date_from; ?>">
            </div>
            
            <div class="form-group">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" id="date_to" name="date_to" class="form-input" 
                       value="<?php echo $date_to; ?>">
            </div>
            
            <div class="form-group">
                <label for="employee_id" class="form-label">Employee</label>
                <select id="employee_id" name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo $emp['id']; ?>" 
                            <?php echo $employee_id == $emp['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($emp['employee_id'] . ' - ' . $emp['first_name'] . ' ' . $emp['last_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Generate Report
            </button>
            <button type="button" onclick="printReport()" class="btn btn-info">
                <i class="fas fa-print"></i> Print Report
            </button>
            <button type="button" onclick="exportCSV()" class="btn btn-success">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>
    </form>
    
    <!-- Summary Statistics -->
    <?php if ($total_records > 0): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i> 
                Summary Statistics (<?php echo date('M j, Y', strtotime($date_from)); ?> 
                <?php echo $date_from !== $date_to ? ' - ' . date('M j, Y', strtotime($date_to)) : ''; ?>)
            </h3>
        </div>
        
        <div class="stats-container">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $total_records; ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card present">
                <div class="stat-number"><?php echo $summary['present']; ?></div>
                <div class="stat-label">Present</div>
            </div>
            <div class="stat-card late">
                <div class="stat-number"><?php echo $summary['late']; ?></div>
                <div class="stat-label">Late</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $summary['halfday']; ?></div>
                <div class="stat-label">Half Day</div>
            </div>
            <div class="stat-card absent">
                <div class="stat-number"><?php echo $summary['absent']; ?></div>
                <div class="stat-label">Absent</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $summary['leave']; ?></div>
                <div class="stat-label">Leave</div>
            </div>
        </div>
    </div>
    
    <!-- Attendance Records -->
    <div class="card" id="report-content">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Attendance Records</h3>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?php echo date('M j, Y', strtotime($record['attendance_date'])); ?></td>
                        <td><?php echo htmlspecialchars($record['employee_id']); ?></td>
                        <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['department_name'] ?: 'N/A'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $record['status']; ?>">
                                <?php echo ucfirst($record['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $record['check_in_time'] ? date('H:i', strtotime($record['check_in_time'])) : '-'; ?></td>
                        <td><?php echo $record['check_out_time'] ? date('H:i', strtotime($record['check_out_time'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($record['notes'] ?: '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php else: ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> No Records Found</h3>
        </div>
        <p>No attendance records found for the selected criteria. Try adjusting the filters above.</p>
    </div>
    <?php endif; ?>
</div>

<script>
function updateDateRange(type) {
    const today = new Date();
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    
    if (type === 'weekly') {
        // Get Monday of current week
        const monday = new Date(today);
        monday.setDate(today.getDate() - today.getDay() + 1);
        
        // Get Sunday of current week
        const sunday = new Date(monday);
        sunday.setDate(monday.getDate() + 6);
        
        dateFrom.value = monday.toISOString().split('T')[0];
        dateTo.value = sunday.toISOString().split('T')[0];
    } else if (type === 'monthly') {
        // First day of current month
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        
        // Last day of current month
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        dateFrom.value = firstDay.toISOString().split('T')[0];
        dateTo.value = lastDay.toISOString().split('T')[0];
    } else if (type === 'daily') {
        dateFrom.value = today.toISOString().split('T')[0];
        dateTo.value = today.toISOString().split('T')[0];
    }
}

function printReport() {
    window.print();
}

function exportCSV() {
    const table = document.querySelector('.table');
    const rows = table.querySelectorAll('tr');
    let csv = '';
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        const rowData = [];
        
        cols.forEach(col => {
            // Remove status badge classes and get clean text
            const text = col.textContent.replace(/\s+/g, ' ').trim();
            rowData.push('"' + text + '"');
        });
        
        csv += rowData.join(',') + '\n';
    });
    
    // Create download link
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'attendance_report_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>

<?php include '../includes/footer.php'; ?>