<?php
http_response_code(403);
include '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="py-5">
                <h1 class="display-1 text-muted">403</h1>
                <h2 class="mb-4">Access Forbidden</h2>
                <p class="lead mb-4">
                    You don't have permission to access this resource. 
                    This area is restricted and requires proper authorization.
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="/public/" class="btn btn-primary">Go Home</a>
                    <a href="/admin/login.php" class="btn btn-outline-primary">Admin Login</a>
                    <a href="/public/contact.php" class="btn btn-outline-secondary">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>