<?php
/**
 * Activities listing page
 * Displays published activities with pagination
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Set page title
$page_title = 'Activities';

// Pagination settings
$activitiesPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, $currentPage); // Ensure page is at least 1

// Calculate offset for MongoDB skip
$offset = ($currentPage - 1) * $activitiesPerPage;

// Get activities collection
$activitiesCollection = getCollection('activities');

if ($activitiesCollection === null) {
    $error_message = "Unable to connect to database. Please try again later.";
    $activities = [];
    $totalActivities = 0;
} else {
    // Filter for published activities only
    $filter = ['status' => 'published'];
    
    // Get total count for pagination
    $totalActivities = countDocuments($activitiesCollection, $filter);
    
    if ($totalActivities === false) {
        $totalActivities = 0;
        $activities = [];
        $error_message = "Error retrieving activities. Please try again later.";
    } else {
        // Fetch activities with pagination
        $options = [
            'sort' => ['created_at' => -1], // Sort by newest first
            'skip' => $offset,
            'limit' => $activitiesPerPage
        ];
        
        $cursor = findDocuments($activitiesCollection, $filter, $options);
        
        if ($cursor === false) {
            $activities = [];
            $error_message = "Error retrieving activities. Please try again later.";
        } else {
            // Convert cursor to array
            $activities = [];
            foreach ($cursor as $activity) {
                $activities[] = $activity;
            }
        }
    }
}

// Calculate pagination info
$totalPages = $totalActivities > 0 ? ceil($totalActivities / $activitiesPerPage) : 0;
$pagination = null;

if ($totalPages > 1) {
    $pagination = generatePagination($currentPage, $totalPages, 'activities.php');
}

// Include header
include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1><i class="fas fa-calendar-alt me-3"></i>Our Activities</h1>
                <p class="lead">Discover our latest initiatives, events, and community impact projects</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container">
    <?php
    // Display flash messages
    $flashMessage = getFlashMessage();
    if ($flashMessage): ?>
        <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo sanitizeOutput($flashMessage['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo sanitizeOutput($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($activities) && !isset($error_message)): ?>
        <!-- No Activities Message -->
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <div class="card shadow-sm">
                    <div class="card-body py-5">
                        <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 4rem;"></i>
                        <h3 class="text-muted mb-3">No Activities Available</h3>
                        <p class="text-muted mb-4">
                            We haven't published any activities yet. Please check back soon for updates on our latest initiatives and events.
                        </p>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Activities Grid -->
        <div class="row">
            <?php foreach ($activities as $activity): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card activity-card h-100 shadow-sm">
                        <?php if (!empty($activity['image'])): ?>
                            <img src="<?php echo '../uploads/' . sanitizeOutput($activity['image']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo sanitizeOutput($activity['title']); ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="display: none;">
                                <div class="text-center">
                                    <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2 mb-0">Image not found</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <?php echo sanitizeOutput($activity['title']); ?>
                            </h5>
                            
                            <p class="card-text text-muted flex-grow-1">
                                <?php 
                                // Create excerpt from content (150 characters)
                                $excerpt = strip_tags($activity['content']);
                                echo sanitizeOutput(truncateText($excerpt, 150));
                                ?>
                            </p>
                            
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="activity-date">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo formatDate($activity['created_at'], 'M j, Y'); ?>
                                    </small>
                                    <a href="activity-detail.php?id=<?php echo objectIdToString($activity['_id']); ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        Read More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($pagination && $totalPages > 1): ?>
            <!-- Pagination -->
            <div class="row mt-5">
                <div class="col-12">
                    <nav aria-label="Activities pagination">
                        <ul class="pagination justify-content-center">
                            <?php if (isset($pagination['prev'])): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo $pagination['prev']; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php foreach ($pagination['pages'] as $page): ?>
                                <li class="page-item <?php echo $page['current'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo $page['url']; ?>">
                                        <?php echo $page['number']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>

                            <?php if (isset($pagination['next'])): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo $pagination['next']; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Pagination Info -->
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-muted">
                        Showing <?php echo min($offset + 1, $totalActivities); ?> to 
                        <?php echo min($offset + $activitiesPerPage, $totalActivities); ?> of 
                        <?php echo $totalActivities; ?> activities
                    </p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.activity-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: none;
    overflow: hidden;
}

.activity-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.activity-card .card-img-top {
    height: 220px;
    width: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.3s ease;
}

.activity-card:hover .card-img-top {
    transform: scale(1.05);
}

.activity-card .card-body {
    padding: 1.5rem;
}

.activity-card .card-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.activity-card .card-text {
    line-height: 1.6;
    margin-bottom: 1rem;
}

.activity-card .btn {
    border-radius: 25px;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.activity-card .btn:hover {
    transform: translateY(-1px);
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4rem 0;
    margin-bottom: 3rem;
}

.page-header h1 {
    font-weight: 700;
    margin-bottom: 1rem;
}

.page-header .lead {
    font-size: 1.1rem;
    opacity: 0.9;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .activity-card .card-img-top {
        height: 200px;
    }
    
    .page-header {
        padding: 3rem 0;
    }
}

@media (max-width: 576px) {
    .activity-card .card-img-top {
        height: 180px;
    }
    
    .activity-card .card-body {
        padding: 1.25rem;
    }
}
</style>

<?php
// Include footer
include __DIR__ . '/../includes/footer.php';
?>