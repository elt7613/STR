        </div>
    </div>
    <footer class="admin-footer">
        <p>&copy; <?php echo date('Y'); ?> STR Admin Panel</p>
    </footer>
    
    <!-- jQuery and Bootstrap JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript for admin panel -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar on mobile
        const toggleBtn = document.querySelector('.toggle-sidebar');
        const adminContainer = document.querySelector('.admin-container');
        
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                adminContainer.classList.toggle('sidebar-collapsed');
            });
        }
        
        // Close alerts
        const closeButtons = document.querySelectorAll('.close-alert');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.opacity = '0';
                setTimeout(() => {
                    this.parentElement.style.display = 'none';
                }, 300);
            });
        });
        
        // Auto close alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        });
    });
    </script>
</body>
</html> 