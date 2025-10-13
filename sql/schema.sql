-- Employee Attendance Management System Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Create admin table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, password, full_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@company.com');

-- Create departments table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert predefined departments
INSERT INTO departments (name, description) VALUES
('Software Development', 'Handles software development projects'),
('Quality Assurance', 'Ensures software quality and testing'),
('DevOps', 'Manages deployment and infrastructure'),
('UI/UX Design', 'Designs user interfaces and experiences'),
('Project Management', 'Manages projects and coordination'),
('Human Resources', 'Handles employee relations and recruitment'),
('Administration', 'General administrative tasks');

-- Create employees table
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL UNIQUE,
    nic VARCHAR(15) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(15),
    department_id INT,
    position VARCHAR(100),
    hire_date DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Create attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'halfday', 'leave') NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    UNIQUE KEY unique_employee_date (employee_id, attendance_date)
);

-- Insert sample employees for testing
INSERT INTO employees (employee_id, nic, first_name, last_name, email, phone, department_id, position, hire_date) VALUES
('EMP001', '123456789V', 'John', 'Doe', 'john.doe@company.com', '0771234567', 1, 'Senior Developer', '2023-01-15'),
('EMP002', '987654321V', 'Jane', 'Smith', 'jane.smith@company.com', '0777654321', 1, 'Frontend Developer', '2023-02-20'),
('EMP003', '456789123V', 'Mike', 'Johnson', 'mike.johnson@company.com', '0771111222', 2, 'QA Engineer', '2023-03-10'),
('EMP004', '789123456V', 'Sarah', 'Williams', 'sarah.williams@company.com', '0773333444', 4, 'UI/UX Designer', '2023-04-05'),
('EMP005', '321654987V', 'David', 'Brown', 'david.brown@company.com', '0775555666', 3, 'DevOps Engineer', '2023-05-12');

-- Insert some sample attendance records
INSERT INTO attendance (employee_id, attendance_date, status, check_in_time, check_out_time) VALUES
(1, '2024-10-01', 'present', '09:00:00', '17:30:00'),
(2, '2024-10-01', 'present', '09:15:00', '17:45:00'),
(3, '2024-10-01', 'late', '09:45:00', '17:30:00'),
(4, '2024-10-01', 'present', '08:55:00', '17:25:00'),
(5, '2024-10-01', 'absent', NULL, NULL),
(1, '2024-10-02', 'present', '08:58:00', '17:35:00'),
(2, '2024-10-02', 'halfday', '09:10:00', '13:00:00'),
(3, '2024-10-02', 'present', '09:05:00', '17:40:00'),
(4, '2024-10-02', 'leave', NULL, NULL),
(5, '2024-10-02', 'present', '09:20:00', '17:50:00');