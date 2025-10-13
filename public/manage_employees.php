<?php
require_once '../config/db.php';
require_admin_login();
$page_title = 'Manage Employees';

$action = $_GET['action'] ?? 'list';
$employee_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_employee'])) {
        $employee_id = trim($_POST['employee_id']);
        $nic = trim($_POST['nic']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $department_id = $_POST['department_id'];
        $position = trim($_POST['position']);
        $hire_date = $_POST['hire_date'];
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO employees (employee_id, nic, first_name, last_name, email, phone, department_id, position, hire_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$employee_id, $nic, $first_name, $last_name, $email, $phone, $department_id, $position, $hire_date]);
            
            flash_message('Employee added successfully!');
            redirect('manage_employees.php');
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = 'Employee ID, NIC, or Email already exists.';
            } else {
                $error = 'Error adding employee. Please try again.';
            }
        }
    }
    
    if (isset($_POST['update_employee'])) {
        $id = $_POST['id'];
        $employee_id = trim($_POST['employee_id']);
        $nic = trim($_POST['nic']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $department_id = $_POST['department_id'];
        $position = trim($_POST['position']);
        $hire_date = $_POST['hire_date'];
        $status = $_POST['status'];
        
        try {
            $stmt = $pdo->prepare("
                UPDATE employees 
                SET employee_id = ?, nic = ?, first_name = ?, last_name = ?, email = ?, phone = ?, 
                    department_id = ?, position = ?, hire_date = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$employee_id, $nic, $first_name, $last_name, $email, $phone, $department_id, $position, $hire_date, $status, $id]);
            
            flash_message('Employee updated successfully!');
            redirect('manage_employees.php');
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = 'Employee ID, NIC, or Email already exists.';
            } else {
                $error = 'Error updating employee. Please try again.';
            }
        }
    }
}

// Handle delete
if ($action === 'delete' && $employee_id) {
    try {
        $stmt = $pdo->prepare("UPDATE employees SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$employee_id]);
        
        flash_message('Employee deactivated successfully!');
        redirect('manage_employees.php');
    } catch (PDOException $e) {
        flash_message('Error deactivating employee.', 'error');
        redirect('manage_employees.php');
    }
}

// Get departments for dropdown
$stmt = $pdo->query("SELECT * FROM departments ORDER BY name");
$departments = $stmt->fetchAll();

// Get employee for edit
$employee = null;
if ($action === 'edit' && $employee_id) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        flash_message('Employee not found.', 'error');
        redirect('manage_employees.php');
    }
}

// Get all employees for listing
if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT e.*, d.name as department_name 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        ORDER BY e.employee_id
    ");
    $employees = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <i class="fas fa-users"></i> Manage Employees
        </h1>
        <div>
            <?php if ($action !== 'list'): ?>
                <a href="manage_employees.php" class="btn btn-info">
                    <i class="fas fa-list"></i> Back to List
                </a>
            <?php endif; ?>
            
            <?php if ($action === 'list'): ?>
                <a href="manage_employees.php?action=add" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Employee
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($action === 'list'): ?>
        <!-- Employee List -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>NIC</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($emp['employee_id']); ?></td>
                        <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($emp['nic']); ?></td>
                        <td><?php echo htmlspecialchars($emp['department_name'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($emp['position']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $emp['status']; ?>">
                                <?php echo ucfirst($emp['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="manage_employees.php?action=edit&id=<?php echo $emp['id']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php if ($emp['status'] === 'active'): ?>
                            <a href="manage_employees.php?action=delete&id=<?php echo $emp['id']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to deactivate this employee?')">
                                <i class="fas fa-ban"></i> Deactivate
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    <?php elseif ($action === 'add'): ?>
        <!-- Add Employee Form -->
        <form method="POST" action="">
            <div class="grid grid-2">
                <div class="form-group">
                    <label for="employee_id" class="form-label">Employee ID *</label>
                    <input type="text" id="employee_id" name="employee_id" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['employee_id'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nic" class="form-label">NIC *</label>
                    <input type="text" id="nic" name="nic" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['nic'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="department_id" class="form-label">Department</label>
                    <select id="department_id" name="department_id" class="form-select">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" 
                                <?php echo ($_POST['department_id'] ?? '') == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="position" class="form-label">Position</label>
                    <input type="text" id="position" name="position" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['position'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="hire_date" class="form-label">Hire Date</label>
                    <input type="date" id="hire_date" name="hire_date" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['hire_date'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" name="add_employee" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Employee
                </button>
                <a href="manage_employees.php" class="btn btn-warning">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
        
    <?php elseif ($action === 'edit' && $employee): ?>
        <!-- Edit Employee Form -->
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label for="employee_id" class="form-label">Employee ID *</label>
                    <input type="text" id="employee_id" name="employee_id" class="form-input" 
                           value="<?php echo htmlspecialchars($employee['employee_id']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nic" class="form-label">NIC *</label>
                    <input type="text" id="nic" name="nic" class="form-input" 
                           value="<?php echo htmlspecialchars($employee['nic']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-input" 
                           value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-input" 
                           value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?php echo htmlspecialchars($employee['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-input" 
                           value="<?php echo htmlspecialchars($employee['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="department_id" class="form-label">Department</label>
                    <select id="department_id" name="department_id" class="form-select">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" 
                                <?php echo $employee['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="position" class="form-label">Position</label>
                    <input type="text" id="position" name="position" class="form-input" 
                           value="<?php echo htmlspecialchars($employee['position']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="hire_date" class="form-label">Hire Date</label>
                    <input type="date" id="hire_date" name="hire_date" class="form-input" 
                           value="<?php echo htmlspecialchars($employee['hire_date']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" <?php echo $employee['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $employee['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" name="update_employee" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Employee
                </button>
                <a href="manage_employees.php" class="btn btn-warning">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>