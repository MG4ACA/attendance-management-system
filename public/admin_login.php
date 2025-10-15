<?php
require_once '../config/db.php';
$page_title = 'Admin Login';

// If already logged in, redirect to dashboard
if (is_admin_logged_in()) {
  redirect('index.php');
}

$error = '';

if ($_POST) {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if (empty($username) || empty($password)) {
    $error = 'Please enter both username and password.';
  } else {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND status = 'active'");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
      $_SESSION['admin_id'] = $admin['id'];
      $_SESSION['admin_username'] = $admin['username'];
      $_SESSION['admin_name'] = $admin['full_name'];

      flash_message('Welcome back, ' . $admin['full_name'] . '!');
      redirect('index.php');
    } else {
      $error = 'Invalid username or password.';
    }
  }
}

include '../includes/header.php';
?>

<div class="login-container">
  <div class="login-header">
    <i class="fas fa-user-shield"></i>
    <h2>Admin Login</h2>
    <p>Access the administration panel</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error">
      <i class="fas fa-exclamation-triangle"></i>
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label for="username" class="form-label">
        <i class="fas fa-user"></i> Username
      </label>
      <input type="text" id="username" name="username" class="form-input"
        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autocomplete="username">
    </div>

    <div class="form-group">
      <label for="password" class="form-label">
        <i class="fas fa-lock"></i> Password
      </label>
      <div class="password-input-container">
        <input type="password" id="password" name="password" class="form-input password-input" required
          autocomplete="current-password">
        <button type="button" class="password-toggle" onclick="togglePassword()">
          <i class="fas fa-eye" id="toggleIcon"></i>
        </button>
      </div>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%;">
      <i class="fas fa-sign-in-alt"></i> Login
    </button>
  </form>
</div>

<script>
  function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleIcon.classList.remove('fa-eye');
      toggleIcon.classList.add('fa-eye-slash');
    } else {
      passwordInput.type = 'password';
      toggleIcon.classList.remove('fa-eye-slash');
      toggleIcon.classList.add('fa-eye');
    }
  }
</script>

<?php include '../includes/footer.php'; ?>

