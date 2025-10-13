# Employee Attendance Management System

A comprehensive web-based attendance management system designed for small IT firms to streamline employee attendance tracking, management, and reporting.

## Features

### Core Functionality
- **Daily Attendance Marking**: Employees can mark attendance using Employee ID or NIC
- **Employee Management**: Admin can add, edit, and manage employee records
- **Comprehensive Reports**: Generate attendance reports (daily, weekly, monthly, custom range)
- **Department Management**: Pre-defined departments with departmental attendance tracking
- **Multiple Attendance Status**: Present, Absent, Late, Half Day, Leave

### Admin Features
- Secure admin login system
- Employee CRUD operations
- Advanced filtering and reporting
- Export reports to CSV
- Print-friendly report layouts

### Employee Features
- Simple attendance marking interface
- Search by Employee ID or NIC
- Real-time attendance status updates
- No authentication required for attendance marking

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome 6.0

## Installation & Setup

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Step-by-Step Installation

1. **Download/Clone the Project**
   ```
   Place the project folder in your web server's document root:
   - XAMPP: htdocs/attendance-management-system
   - WAMP: www/attendance-management-system
   ```

2. **Database Setup**
   - Start Apache and MySQL services
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `attendance_system`
   - Import the SQL file: `sql/schema.sql`

3. **Configuration**
   - Open `config/db.php`
   - Update database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', ''); // Your MySQL password
     define('DB_NAME', 'attendance_system');
     ```

4. **Access the System**
   - Open web browser
   - Navigate to: `http://localhost/attendance-management-system/public/`

## Default Login Credentials

**Admin Access:**
- Username: `admin`
- Password: `admin123`

## Project Structure

```
attendance-management-system/
├── config/
│   └── db.php                 # Database configuration
├── includes/
│   ├── header.php            # Common header
│   └── footer.php            # Common footer
├── public/
│   ├── index.php             # Dashboard
│   ├── admin_login.php       # Admin login
│   ├── admin_logout.php      # Admin logout
│   ├── mark_attendance.php   # Employee attendance marking
│   ├── manage_employees.php  # Employee management (admin)
│   └── attendance_report.php # Attendance reports (admin)
├── assets/
│   └── style.css            # Stylesheet
├── sql/
│   └── schema.sql           # Database schema
└── README.md
```

## Database Schema

### Tables
1. **admins** - Admin user accounts
2. **departments** - Company departments (predefined)
3. **employees** - Employee records
4. **attendance** - Daily attendance records

### Pre-loaded Data
- 7 departments (Software Development, QA, DevOps, UI/UX, Project Management, HR, Administration)
- 5 sample employees with attendance records
- 1 admin user (admin/admin123)

## Usage Guide

### For Employees (Attendance Marking)
1. Go to "Mark Attendance" page
2. Enter Employee ID or NIC in search box
3. Click "Search Employee"
4. Select attendance status (Present/Late/Half Day/Absent/Leave)
5. Add notes if needed
6. Click "Mark Attendance"

### For Administrators
1. Login with admin credentials
2. **Manage Employees**: Add, edit, or deactivate employees
3. **View Reports**: Generate and export attendance reports
4. **Dashboard**: View daily statistics and system overview

## Report Features

### Report Types
- **Daily**: Current day attendance
- **Weekly**: Monday to Sunday of current week
- **Monthly**: Full current month
- **Custom Range**: Any date range

### Export Options
- Print-friendly view
- CSV export for Excel compatibility
- Real-time filtering by department and employee

## Attendance Status Types

- **Present**: On time and working full day
- **Late**: Arrived late but working full day  
- **Half Day**: Working only half day
- **Absent**: Not present at work
- **Leave**: On approved leave

## Security Features

- Password hashing for admin accounts
- SQL injection prevention using prepared statements
- Session management for admin authentication
- Input validation and sanitization

## Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Responsive Design

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL service is running
   - Verify database credentials in `config/db.php`
   - Ensure database `attendance_system` exists

2. **Page Not Found (404)**
   - Verify project is in correct web server directory
   - Check file permissions
   - Ensure Apache/web server is running

3. **Login Issues**
   - Use default credentials: admin/admin123
   - Check if admin table has data
   - Clear browser cache and cookies

4. **Attendance Not Saving**
   - Check database connection
   - Verify employee exists and is active
   - Check browser console for JavaScript errors

## Future Enhancements

Potential improvements for production use:
- Email notifications for attendance
- Mobile app integration
- Biometric attendance integration
- Leave management system
- Payroll integration
- Advanced analytics dashboard

## Assignment Note

This project is developed as an educational assignment for demonstrating:
- PHP web development skills
- Database design and implementation
- User interface design
- Session management
- Report generation
- Responsive web design

## Support

For technical support or questions regarding this assignment project:
- Check the troubleshooting section above
- Review the code comments for implementation details
- Verify all setup steps have been completed correctly

---

**Developed for**: IT Firm Assignment Project  
**Date**: 2024  
**Technology**: PHP, MySQL, HTML5, CSS3