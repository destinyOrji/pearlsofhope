        </div>
    </main>

    <!-- Admin Footer -->
    <footer class="bg-dark text-light py-3 mt-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        <strong>NGO Website Admin Panel</strong>
                    </p>
                    <p class="text-muted small mb-0">
                        &copy; <?php echo date('Y'); ?> NGO Website. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted small">
                        <i class="fas fa-user me-1"></i>
                        Logged in as: <strong><?php echo sanitizeOutput($admin_username ?: 'Administrator'); ?></strong>
                    </p>
                    <p class="mb-0 text-muted small">
                        <i class="fas fa-clock me-1"></i>
                        Last activity: <?php echo date('Y-m-d H:i:s'); ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Admin JS -->
    <script>
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Confirm deletion actions
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-confirm') || 
                e.target.closest('.delete-confirm')) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Auto-logout warning
        let inactivityTimer;
        let warningTimer;
        const TIMEOUT_DURATION = 30 * 60 * 1000; // 30 minutes
        const WARNING_DURATION = 5 * 60 * 1000;  // 5 minutes before timeout

        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            clearTimeout(warningTimer);
            
            // Set warning timer
            warningTimer = setTimeout(function() {
                if (confirm('Your session will expire in 5 minutes due to inactivity. Click OK to stay logged in.')) {
                    resetInactivityTimer();
                }
            }, TIMEOUT_DURATION - WARNING_DURATION);
            
            // Set logout timer
            inactivityTimer = setTimeout(function() {
                alert('Your session has expired due to inactivity. You will be redirected to the login page.');
                window.location.href = 'logout.php';
            }, TIMEOUT_DURATION);
        }

        // Reset timer on user activity
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(function(event) {
            document.addEventListener(event, resetInactivityTimer, true);
        });

        // Initialize timer
        resetInactivityTimer();
    </script>
    
    <!-- Custom JS for specific pages -->
    <?php if (isset($custom_js)): ?>
        <script src="<?php echo $custom_js; ?>"></script>
    <?php endif; ?>
</body>
</html>