<?php
/**
 * Activity detail page
 * Displays full activity content for a specific activity
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize variables
$activity = null;
$error_message = null;
$page_title = 'Activity Details';

// Check if ID parameter is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $error_message = "Activity ID is required.";
} else {
    $activityId = sanitizeInput($_GET['id']);
    
    // Validate ObjectId format
    if (!validateObjectId($activityId)) {
        $error_message = "Invalid activity ID format.";
    } else {
        // Convert string to ObjectId
        $objectId = stringToObjectId($activityId);
        
        if ($objectId === false) {
            $error_message = "Invalid activity ID.";
        } else {
            // Get activities collection
            $activitiesCollection = getCollection('activities');
            
            if ($activitiesCollection === null) {
                $error_message = "Unable to connect to database. Please try again later.";
            } else {
                // Find the activity by ID and ensure it's published
                $filter = [
                    '_id' => $objectId,
                    'status' => 'published'
                ];
                
                $activity = findOneDocument($activitiesCollection, $filter);
                
                if ($activity === false) {
                    $error_message = "Error retrieving activity. Please try again later.";
                } elseif ($activity === null) {
                    $error_message = "Activity not found or not published.";
                } else {
                    // Set page title to activity title
                    $page_title = $activity['title'];
                }
            }
        }
    }
}

// Include header
include __DIR__ . '/../includes/header.php';
?>

<?php if ($error_message): ?>
    <!-- Error Message -->
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <div class="card shadow-sm mt-5">
                    <div class="card-body py-5">
                        <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 4rem;"></i>
                        <h3 class="text-muted mb-3">Activity Not Found</h3>
                        <p class="text-muted mb-4">
                            <?php echo sanitizeOutput($error_message); ?>
                        </p>
                        <a href="activities.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Activities
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Activity Content -->
    <div class="container">
        <!-- Back Navigation -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="activities.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Activities
                </a>
            </div>
        </div>

        <!-- Activity Header -->
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <article class="activity-detail">
                    <!-- Activity Title -->
                    <header class="mb-4">
                        <h1 class="display-5 fw-bold mb-3">
                            <?php echo sanitizeOutput($activity['title']); ?>
                        </h1>
                        
                        <!-- Activity Meta -->
                        <div class="activity-meta text-muted mb-4">
                            <i class="fas fa-calendar me-2"></i>
                            <span>Published on <?php echo formatDate($activity['created_at'], 'F j, Y'); ?></span>
                            
                            <?php if (isset($activity['updated_at']) && $activity['updated_at'] != $activity['created_at']): ?>
                                <span class="ms-3">
                                    <i class="fas fa-edit me-2"></i>
                                    Last updated <?php echo formatDate($activity['updated_at'], 'F j, Y'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </header>

                    <!-- Activity Image -->
                    <?php if (!empty($activity['image'])): ?>
                        <div class="activity-image mb-4">
                            <img src="<?php echo '../uploads/' . sanitizeOutput($activity['image']); ?>" 
                                 class="img-fluid rounded shadow-sm" 
                                 alt="<?php echo sanitizeOutput($activity['title']); ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div class="alert alert-warning" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Image could not be loaded: <?php echo sanitizeOutput($activity['image']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Activity Content -->
                    <div class="activity-content">
                        <div class="content-body">
                            <?php 
                            // Display content with preserved formatting
                            $content = nl2br(sanitizeOutput($activity['content']));
                            echo $content;
                            ?>
                        </div>
                    </div>

                    <!-- Activity Footer -->
                    <footer class="activity-footer mt-5 pt-4 border-top">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="activity-tags">
                                    <span class="badge bg-primary me-2">
                                        <i class="fas fa-tag me-1"></i>Activity
                                    </span>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-calendar me-1"></i><?php echo formatDate($activity['created_at'], 'Y'); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <div class="share-buttons">
                                    <span class="text-muted me-3">Share this activity:</span>
                                    <a href="#" class="btn btn-outline-primary btn-sm me-2" 
                                       onclick="shareOnFacebook(); return false;" title="Share on Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="#" class="btn btn-outline-info btn-sm me-2" 
                                       onclick="shareOnTwitter(); return false;" title="Share on Twitter">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="#" class="btn btn-outline-success btn-sm" 
                                       onclick="copyToClipboard(); return false;" title="Copy link">
                                        <i class="fas fa-link"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </footer>
                </article>
            </div>
        </div>

        <!-- Navigation to Other Activities -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <div class="card bg-light">
                    <div class="card-body py-4">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-calendar-alt me-2"></i>Explore More Activities
                        </h5>
                        <p class="card-text text-muted mb-3">
                            Discover more of our initiatives and community impact projects.
                        </p>
                        <a href="activities.php" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>View All Activities
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for sharing functionality -->
    <script>
        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, 'facebook-share', 'width=580,height=296');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, 'twitter-share', 'width=550,height=235');
        }

        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                // Show success message
                const btn = event.target.closest('a');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success');
                
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-success');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                alert('Could not copy link to clipboard');
            });
        }
    </script>
<?php endif; ?>

<?php
// Include footer
include __DIR__ . '/../includes/footer.php';
?>