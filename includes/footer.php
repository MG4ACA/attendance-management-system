    </main>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2024 Employee Attendance Management System. Developed for IT Firm Assignment.</p>
        </div>
    </footer>

    <script>
        // Auto-hide flash messages
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                setTimeout(function() {
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
            dateElements.forEach(function(element) {
                element.textContent = dateString + ' - ' + timeString;
            });
        }

        // Update date every second
        setInterval(updateCurrentDate, 1000);
        updateCurrentDate();
    </script>
</body>
</html>