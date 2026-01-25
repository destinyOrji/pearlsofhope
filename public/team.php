<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Get team members from database
$teamMembers = [];
try {
    $teamCollection = getCollection('team_members');
    if ($teamCollection) {
        $cursor = findDocuments($teamCollection, 
            ['status' => 'active'], 
            ['sort' => ['display_order' => 1, 'created_at' => 1]]
        );
        if ($cursor) {
            $teamMembers = $cursor->toArray();
        }
    }
} catch (Exception $e) {
    error_log("Error fetching team members: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-center mb-5">Our Team</h1>
            
            <?php if (empty($teamMembers)): ?>
                <div class="text-center">
                    <p class="lead">Meet our dedicated team members who work tirelessly to make a difference in our community.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Team member information will be available soon.
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($teamMembers as $member): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 team-member-card">
                                <?php if (!empty($member['image'])): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($member['image']); ?>" 
                                         class="card-img-top team-member-image" 
                                         alt="<?php echo htmlspecialchars($member['name']); ?>">
                                <?php else: ?>
                                    <div class="card-img-top team-member-placeholder d-flex align-items-center justify-content-center">
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($member['name']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-primary"><?php echo htmlspecialchars($member['role']); ?></h6>
                                    
                                    <?php if (!empty($member['description'])): ?>
                                        <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars($member['description'])); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($member['email']) || !empty($member['phone'])): ?>
                                        <div class="mt-auto">
                                            <hr>
                                            <div class="contact-info">
                                                <?php if (!empty($member['email'])): ?>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-envelope"></i> 
                                                        <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>">
                                                            <?php echo htmlspecialchars($member['email']); ?>
                                                        </a>
                                                    </small>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($member['phone'])): ?>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-phone"></i> 
                                                        <?php echo htmlspecialchars($member['phone']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.team-member-card {
    transition: transform 0.2s ease-in-out;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.team-member-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.team-member-image {
    height: 280px;
    width: 100%;
    object-fit: cover;
    object-position: center top;
    transition: transform 0.3s ease;
}

.team-member-card:hover .team-member-image {
    transform: scale(1.05);
}

.team-member-placeholder {
    height: 280px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.team-member-placeholder i {
    color: #6c757d;
}

.card-body {
    padding: 1.5rem;
}

.card-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.card-subtitle {
    font-weight: 500;
    font-size: 0.95rem;
    margin-bottom: 1rem;
}

.card-text {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #6c757d;
}

.contact-info {
    background-color: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-top: 1rem;
}

.contact-info small {
    margin-bottom: 0.25rem;
}

.contact-info i {
    width: 16px;
    margin-right: 0.5rem;
    color: #007bff;
}

.contact-info a {
    color: #495057;
    text-decoration: none;
    transition: color 0.2s ease;
}

.contact-info a:hover {
    color: #007bff;
    text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .team-member-image,
    .team-member-placeholder {
        height: 250px;
    }
    
    .card-body {
        padding: 1.25rem;
    }
}

@media (max-width: 576px) {
    .team-member-image,
    .team-member-placeholder {
        height: 220px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>