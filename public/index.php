<?php
/**
 * NGO Website - Home Page
 * Displays the organization's mission statement and overview
 */

// Include required files
require_once '../includes/config.php';
require_once '../includes/db.php';

// Set page title
$page_title = 'Home';

// Fetch home page content from MongoDB
$pagesCollection = getCollection('pages');
$homeContent = null;
$error_message = '';

if ($pagesCollection) {
    $homeContent = findOneDocument($pagesCollection, ['page_name' => 'home']);
    
    if ($homeContent === false) {
        $error_message = 'Unable to load page content. Please try again later.';
    } elseif ($homeContent === null) {
        // Default content if no home page content exists in database
        $homeContent = [
            'title' => 'Welcome to Our Pearls of Hope',
            'content' => 'We are dedicated to making a positive impact in our community through compassion, action, and unwavering commitment to helping those in need. Our mission is to create lasting change and build a better future for all.'
        ];
    }
} else {
    $error_message = 'Database connection error. Please try again later.';
    // Fallback content
    $homeContent = [
        'title' => 'Welcome to Our NGO',
        'content' => 'We are dedicated to making a positive impact in our community through compassion, action, and unwavering commitment to helping those in need.'
    ];
}

// Fetch recent activities for homepage display
$recentActivities = [];
try {
    $activitiesCollection = getCollection('activities');
    if ($activitiesCollection) {
        $cursor = findDocuments(
            $activitiesCollection, 
            ['status' => 'published'], 
            ['sort' => ['created_at' => -1], 'limit' => 3]
        );
        if ($cursor) {
            $recentActivities = $cursor->toArray();
        }
    }
} catch (Exception $e) {
    error_log("Error fetching recent activities: " . $e->getMessage());
}

// Fetch contact page content
$contactContent = null;
try {
    if ($pagesCollection) {
        $contactContent = findOneDocument($pagesCollection, ['page_name' => 'contact']);
    }
} catch (Exception $e) {
    error_log("Error fetching contact content: " . $e->getMessage());
}

// Fetch about page content for homepage display
$aboutContent = null;
try {
    if ($pagesCollection) {
        $aboutContent = findOneDocument($pagesCollection, ['page_name' => 'about']);
    }
} catch (Exception $e) {
    error_log("Error fetching about content: " . $e->getMessage());
}

// Include header
include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="page-header">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-warning mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <h1 class="display-4 fw-bold mb-4">
                    <?php echo htmlspecialchars($homeContent['title']); ?>
                </h1>
                <p class="lead">
                    Making a difference in our community through compassion and action
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="container">
    <!-- Mission Statement Section -->
    <section class="content-section">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mission-statement">
                    <h3 class="mb-4">
                        <i class="fas fa-heart text-primary me-2"></i>
                        Our Mission
                    </h3>
                    <p class="lead mb-0">
                        <?php echo nl2br(htmlspecialchars($homeContent['content'])); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="content-section">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-hands-helping fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Community Support</h5>
                        <p class="card-text">
                            We provide essential support and resources to families and individuals in need within our community.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Education Programs</h5>
                        <p class="card-text">
                            Educational initiatives and programs designed to empower individuals and create opportunities for growth.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-globe fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Global Impact</h5>
                        <p class="card-text">
                            Working towards sustainable solutions that create positive change both locally and globally.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="content-section">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-5">
                    <h3 class="mb-3">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        About Our Organization
                    </h3>
                    <p class="lead text-muted">Learn more about our history, values, and commitment to the community</p>
                </div>
            </div>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <div class="about-content">
                    <?php if ($aboutContent && !empty($aboutContent['content'])): ?>
                        <h4 class="mb-3"><?php echo htmlspecialchars($aboutContent['title'] ?? 'About Us'); ?></h4>
                        <div class="about-text">
                            <?php 
                            $aboutText = strip_tags($aboutContent['content']);
                            $aboutExcerpt = substr($aboutText, 0, 400) . (strlen($aboutText) > 400 ? '...' : '');
                            echo nl2br(htmlspecialchars($aboutExcerpt));
                            ?>
                        </div>
                    <?php else: ?>
                        <h4 class="mb-3">Our Story</h4>
                        <p class="mb-3">
                            Founded with a vision to create positive change, our NGO has been serving the community for years. 
                            We believe in the power of collective action and the importance of giving back to society.
                        </p>
                        <p class="mb-3">
                            Our dedicated team works tirelessly to address various social issues, from education and healthcare 
                            to environmental conservation and poverty alleviation. Every project we undertake is driven by our 
                            core values of compassion, integrity, and sustainability.
                        </p>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="about.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-right me-2"></i>Read Our Full Story
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="stat-card text-center p-3 bg-light rounded">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                            <h5 class="mb-1">500+</h5>
                            <small class="text-muted">People Helped</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card text-center p-3 bg-light rounded">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-project-diagram fa-2x text-primary"></i>
                            </div>
                            <h5 class="mb-1">25+</h5>
                            <small class="text-muted">Projects Completed</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card text-center p-3 bg-light rounded">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-heart fa-2x text-primary"></i>
                            </div>
                            <h5 class="mb-1">100+</h5>
                            <small class="text-muted">Volunteers</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card text-center p-3 bg-light rounded">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-globe fa-2x text-primary"></i>
                            </div>
                            <h5 class="mb-1">5+</h5>
                            <small class="text-muted">Communities Served</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Activities Section -->
    <?php if (!empty($recentActivities)): ?>
    <section class="content-section">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-5">
                    <h3 class="mb-3">
                        <i class="fas fa-newspaper text-primary me-2"></i>
                        Recent Activities
                    </h3>
                    <p class="lead text-muted">Stay updated with our latest initiatives and community work</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($recentActivities as $activity): ?>
            <div class="col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($activity['image'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($activity['image']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($activity['title']); ?>"
                         style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($activity['title']); ?></h5>
                        <p class="card-text flex-grow-1">
                            <?php 
                            $excerpt = strip_tags($activity['content']);
                            echo htmlspecialchars(substr($excerpt, 0, 120) . (strlen($excerpt) > 120 ? '...' : ''));
                            ?>
                        </p>
                        <div class="mt-auto">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php 
                                if (isset($activity['created_at'])) {
                                    echo $activity['created_at']->toDateTime()->format('M j, Y');
                                }
                                ?>
                            </small>
                            <div class="mt-2">
                                <a href="activity-detail.php?id=<?php echo $activity['_id']; ?>" 
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
        
        <div class="row">
            <div class="col-12 text-center">
                <a href="activities.php" class="btn btn-primary">
                    <i class="fas fa-list me-2"></i>View All Activities
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Contact Information Section -->
    <section class="content-section">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-5">
                    <h3 class="mb-3">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        Get In Touch
                    </h3>
                    <p class="lead text-muted">We'd love to hear from you and answer any questions</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <?php if ($contactContent && !empty($contactContent['content'])): ?>
                            <div class="mb-4">
                                <?php echo nl2br(htmlspecialchars($contactContent['content'])); ?>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Address</h6>
                                            <p class="mb-0 text-muted">123 NGO Street<br>Community City, CC 12345</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-phone fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Phone</h6>
                                            <p class="mb-0 text-muted">+1 (555) 123-4567</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-envelope fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Email</h6>
                                            <p class="mb-0 text-muted">info@ngowebsite.org</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-clock fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Office Hours</h6>
                                            <p class="mb-0 text-muted">Mon-Fri: 9:00 AM - 5:00 PM</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <a href="contact.php" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Us a Message
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="content-section">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="bg-light p-5 rounded-custom">
                    <h3 class="mb-4">Get Involved</h3>
                    <p class="lead mb-4">
                        Join us in making a difference. Learn more about our work and discover how you can contribute to positive change in our community.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                        <a href="about.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-info-circle me-2"></i>
                            Learn More About Us
                        </a>
                        <a href="activities.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-calendar-alt me-2"></i>
                            View Our Activities
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-envelope me-2"></i>
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>