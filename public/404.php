<?php
http_response_code(404);
include '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="py-5">
                <h1 class="display-1 text-muted">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead mb-4">
                    Sorry, the page you are looking for could not be found. 
                    It may have been moved, deleted, or you entered the wrong URL.
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="/public/" class="btn btn-primary">Go Home</a>
                    <a href="/public/activities.php" class="btn btn-outline-primary">View Activities</a>
                    <a href="/public/contact.php" class="btn btn-outline-secondary">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>