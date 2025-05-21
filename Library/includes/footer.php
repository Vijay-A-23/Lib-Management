    </div><!-- /.container -->

    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
            &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - All Rights Reserved
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Enable tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Enable confirmation dialogs
        document.addEventListener('DOMContentLoaded', function() {
            const confirmButtons = document.querySelectorAll('.confirm-action');
            
            confirmButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm(this.getAttribute('data-confirm-message') || 'Are you sure you want to perform this action?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
