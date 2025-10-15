</main>

<footer class="footer">
  <div class="footer-content">
    <p>&copy; 2024 Employee Attendance Management System. Developed for IT Firm Assignment.</p>
  </div>
</footer>

<script>
  // Toast notification function
  function showToast(message, type = 'success') {
    // Remove any existing toast
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
      existingToast.remove();
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
                <i class="fas fa-${getToastIcon(type)}"></i>
                ${message}
                <button class="close-toast" onclick="this.parentElement.remove()">&times;</button>
            `;

    // Add to page
    document.body.appendChild(toast);

    // Show toast
    setTimeout(() => {
      toast.classList.add('show');
    }, 100);

    // Auto hide after 5 seconds
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => {
        if (toast.parentElement) {
          toast.remove();
        }
      }, 300);
    }, 5000);
  }

  function getToastIcon(type) {
    switch (type) {
      case 'success': return 'check-circle';
      case 'error': return 'exclamation-circle';
      case 'warning': return 'exclamation-triangle';
      case 'info': return 'info-circle';
      default: return 'info-circle';
    }
  }

  // Auto-hide flash messages
  setTimeout(function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
      alert.style.opacity = '0';
      setTimeout(function () {
        alert.remove();
      }, 300);
    });
  }, 5000);

  // Current date display
  function updateCurrentDate() {
    const now = new Date();
    const dateString = now.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
    const timeString = now.toLocaleTimeString('en-US');

    const dateElements = document.querySelectorAll('.current-date');
    dateElements.forEach(function (element) {
      element.textContent = dateString + ' - ' + timeString;
    });
  }

  // Update date every second
  setInterval(updateCurrentDate, 1000);
  updateCurrentDate();
</script>
</body>

</html>
