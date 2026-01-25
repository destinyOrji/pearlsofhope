    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="fas fa-heart me-2"></i>Pearls of Hope
                    </h5>
                    <p class="mb-2">Making a difference in our community through compassion and action.</p>
                    <p class="text-muted small mb-0">
                        &copy; <?php echo date('Y'); ?> Pearls of Hope. All rights reserved.
                    </p>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="about.php" class="text-light text-decoration-none">About</a></li>
                        <li><a href="activities.php" class="text-light text-decoration-none">Activities</a></li>
                        <li><a href="contact.php" class="text-light text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Connect With Us</h6>
                    <div class="social-links">
                        <a href="#" class="text-light me-3" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-light me-3" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-light me-3" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-light" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo isset($js_path) ? $js_path : 'assets/js/main.js'; ?>"></script>
</body>
</html>