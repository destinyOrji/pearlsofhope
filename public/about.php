<?php
/**
 * About page - displays organization history and goals from MongoDB
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Set page title
$page_title = 'About Us';

// Get about page content from MongoDB
$pagesCollection = getCollection('pages');
$aboutContent = null;

if ($pagesCollection) {
    $aboutContent = findOneDocument($pagesCollection, ['page_name' => 'about']);
}

// Default content if not found in database
$defaultTitle = 'About Our Organization';
$defaultContent = 'We are a dedicated NGO committed to making a positive impact in our community. Our mission is to help those in need and create lasting change through compassion and action.';

$aboutTitle = $aboutContent ? sanitizeOutput($aboutContent['title']) : $defaultTitle;
$aboutText = $aboutContent ? sanitizeOutput($aboutContent['content']) : $defaultContent;

// Include header
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3"><?php echo $aboutTitle; ?></h1>
                <div class="border-bottom border-primary mx-auto" style="width: 100px; height: 3px;"></div>
            </div>

            <!-- About Content -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="content-text">
                        <?php echo nl2br($aboutText); ?>
                    </div>
                </div>
            </div>

            <!-- Mission Section -->
            <div class="row mt-5">
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-heart fa-2x"></i>
                        </div>
                        <h4 class="fw-bold">Our Mission Statement</h4>
                        <p class="text-muted">helping the boy/girl child to discover purpose in the place of destiny.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-hands-helping fa-2x"></i>
                        </div>
                        <h4 class="fw-bold">Our Vision</h4>
                        <p class="text-muted">Making the Girl/boy chid regain ther place in the creation and regaining thier crown of glogry.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                        <h4 class="fw-bold">Our Impact</h4>
                        <p class="text-muted">Creating sustainable change through community partnerships, education, and direct assistance programs.</p>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="text-center mt-5">
                <div class="bg-light p-4 rounded">
                    <h3 class="mb-3">Get Involved</h3>
                    <p class="mb-4">Join us in making a difference. Together, we can create positive change in our community.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="activities.php" class="btn btn-primary">
                            <i class="fas fa-calendar-alt me-2"></i>View Our Activities
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include __DIR__ . '/../includes/footer.php';
?>