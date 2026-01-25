<?php
/**
 * Admin Dashboard
 * Main admin panel page
 */

$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/admin-header.php';

// Get some basic stats for the dashboard
$totalActivities = 0;
$recentActivities = 0;
$recentActivityList = [];

// Get message statistics
$totalMessages = 0;
$unreadMessages = 0;

try {
    $activitiesCollection = getCollection('activities');
    if ($activitiesCollection) {
        $totalActivities = $activitiesCollection->countDocuments([]);
        
        // Calculate timestamp for 30 days ago (in milliseconds for MongoDB)
        $thirtyDaysAgo = (time() - (30 * 24 * 60 * 60)) * 1000;
        
        // For now, let's get all activities and filter in PHP to avoid MongoDB class issues
        $allActivities = findDocuments($activitiesCollection, []);
        if ($allActivities && $allActivities !== false) {
            $activitiesArray = [];
            foreach ($allActivities as $activity) {
                $activitiesArray[] = $activity;
            }
            
            // Count recent activities
            $recentCount = 0;
            foreach ($activitiesArray as $activity) {
                if (isset($activity['created_at']) && $activity['created_at']->toDateTime()->getTimestamp() * 1000 >= $thirtyDaysAgo) {
                    $recentCount++;
                }
            }
            $recentActivities = $recentCount;
            
            // Get 5 most recent activities
            usort($activitiesArray, function($a, $b) {
                $timeA = isset($a['created_at']) ? $a['created_at']->toDateTime()->getTimestamp() : 0;
                $timeB = isset($b['created_at']) ? $b['created_at']->toDateTime()->getTimestamp() : 0;
                return $timeB - $timeA;
            });
            
            $recentActivityList = array_slice($activitiesArray, 0, 5);
        }
    }
    
    // Get message statistics
    $messagesCollection = getCollection('contact_messages');
    if ($messagesCollection) {
        $totalMessages = countDocuments($messagesCollection, []);
        $unreadMessages = countDocuments($messagesCollection, ['status' => 'unread']);
    }
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    // Keep default values
}
?>
            <div class="row">
                <div class="col-md-12">
                    <h1 class="mb-4">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </h1>
                    <p class="lead">Welcome back, <?php echo sanitizeOutput(getCurrentAdminUsername()); ?>!</p>
                    
                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?php echo $totalActivities; ?></h4>
                                            <p class="mb-0">Total Activities</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-newspaper fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?php echo $recentActivities; ?></h4>
                                            <p class="mb-0">Recent Activities (30 days)</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?php echo $unreadMessages; ?></h4>
                                            <p class="mb-0">Unread Messages</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-envelope fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Stats Row -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?php echo $totalMessages; ?></h4>
                                            <p class="mb-0">Total Messages</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-comments fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?php echo date('H:i'); ?></h4>
                                            <p class="mb-0">Current Time</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-dark text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?php echo date('M j'); ?></h4>
                                            <p class="mb-0">Today's Date</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-calendar fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-newspaper me-2"></i>Activities Management
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Create, edit, and manage activity posts for your NGO website.</p>
                                    <div class="btn-group" role="group">
                                        <a href="activities/list.php" class="btn btn-outline-primary">
                                            <i class="fas fa-list me-1"></i>View All
                                        </a>
                                        <a href="activities/create.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>Create New
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-users me-2"></i>Team Management
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Manage your organization's team members and their information.</p>
                                    <div class="btn-group" role="group">
                                        <a href="team/list.php" class="btn btn-outline-success">
                                            <i class="fas fa-list me-1"></i>View All
                                        </a>
                                        <a href="team/create.php" class="btn btn-success">
                                            <i class="fas fa-plus me-1"></i>Add Member
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-envelope me-2"></i>Messages Management
                                        <?php if ($unreadMessages > 0): ?>
                                            <span class="badge bg-danger ms-1"><?php echo $unreadMessages; ?></span>
                                        <?php endif; ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">View and respond to contact form messages from website visitors.</p>
                                    <div class="btn-group" role="group">
                                        <a href="messages/list.php" class="btn btn-outline-info">
                                            <i class="fas fa-list me-1"></i>All Messages
                                        </a>
                                        <?php if ($unreadMessages > 0): ?>
                                            <a href="messages/list.php?status=unread" class="btn btn-info">
                                                <i class="fas fa-envelope me-1"></i>Unread (<?php echo $unreadMessages; ?>)
                                            </a>
                                        <?php else: ?>
                                            <a href="messages/list.php?status=unread" class="btn btn-outline-info">
                                                <i class="fas fa-envelope me-1"></i>Unread
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Second Row - Pages Management -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-file-alt me-2"></i>Pages Management
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Edit static page content including Home, About, and Contact pages.</p>
                                    <div class="btn-group" role="group">
                                        <a href="pages/edit.php?page=home" class="btn btn-outline-secondary">
                                            <i class="fas fa-home me-1"></i>Home
                                        </a>
                                        <a href="pages/edit.php?page=about" class="btn btn-outline-secondary">
                                            <i class="fas fa-info-circle me-1"></i>About
                                        </a>
                                        <a href="pages/edit.php?page=contact" class="btn btn-outline-secondary">
                                            <i class="fas fa-envelope me-1"></i>Contact
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activities -->
                    <?php if (!empty($recentActivityList)): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history me-2"></i>Recent Activities
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentActivityList as $activity): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo sanitizeOutput($activity['title']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo ($activity['status'] === 'published') ? 'success' : 'warning'; ?>">
                                                            <?php echo sanitizeOutput(ucfirst($activity['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php echo formatDate($activity['created_at'], 'M j, Y'); ?>
                                                    </td>
                                                    <td>
                                                        <a href="activities/edit.php?id=<?php echo objectIdToString($activity['_id']); ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center">
                                        <a href="activities/list.php" class="btn btn-outline-primary">
                                            <i class="fas fa-list me-1"></i>View All Activities
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Quick Links -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Quick Links</h6>
                                    <a href="../public/index.php" class="btn btn-outline-secondary me-2" target="_blank">
                                        <i class="fas fa-external-link-alt me-1"></i>View Public Website
                                    </a>
                                    <a href="logout.php" class="btn btn-outline-danger">
                                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>