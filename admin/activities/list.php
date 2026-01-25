<?php
/**
 * Activity List Page
 * Displays all activities with management options for administrators
 */

// Set page title for header
$page_title = 'Manage Activities';

// Include admin header
require_once '../../includes/admin-header.php';

// Initialize variables
$activities = [];
$totalActivities = 0;
$error = null;

// Fetch activities from database
try {
    $collection = getCollection('activities');
    
    if ($collection) {
        // Get total count
        $totalActivities = countDocuments($collection);
        
        // Fetch all activities sorted by created_at (newest first)
        $cursor = findDocuments($collection, [], [
            'sort' => ['created_at' => -1]
        ]);
        
        if ($cursor !== false) {
            try {
                // Use toArray() method if available, otherwise fallback to iterator_to_array
                if (method_exists($cursor, 'toArray')) {
                    $activities = $cursor->toArray();
                } else {
                    /** @var array $activities */
                    $activities = iterator_to_array($cursor);
                }
            } catch (Exception $e) {
                error_log('Error converting cursor to array: ' . $e->getMessage());
                $activities = [];
            }
        }
    } else {
        $error = 'Database connection error';
    }
} catch (Exception $e) {
    $error = 'Error fetching activities: ' . $e->getMessage();
    error_log($error);
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-newspaper me-2 text-primary"></i>Manage Activities
                </h1>
                <p class="text-muted mb-0">
                    Total: <?php echo $totalActivities; ?> activities
                </p>
            </div>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Create New Activity
            </a>
        </div>
    </div>
</div>

<?php if ($error): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger" role="alert">
            <h6 class="alert-heading">
                <i class="fas fa-exclamation-triangle me-1"></i>Error
            </h6>
            <?php echo sanitizeOutput($error); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Activities List
                </h5>
                <small class="text-light">
                    Sorted by: Newest First
                </small>
            </div>
            <div class="card-body p-0">
                <?php if (empty($activities) && !$error): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Activities Found</h5>
                        <p class="text-muted mb-3">
                            You haven't created any activities yet. Start by creating your first activity.
                        </p>
                        <a href="create.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Create First Activity
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="border-0">
                                        <i class="fas fa-image me-1"></i>Image
                                    </th>
                                    <th scope="col" class="border-0">
                                        <i class="fas fa-heading me-1"></i>Title
                                    </th>
                                    <th scope="col" class="border-0">
                                        <i class="fas fa-calendar me-1"></i>Created Date
                                    </th>
                                    <th scope="col" class="border-0">
                                        <i class="fas fa-eye me-1"></i>Status
                                    </th>
                                    <th scope="col" class="border-0 text-center">
                                        <i class="fas fa-cogs me-1"></i>Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $activity): ?>
                                    <tr>
                                        <td class="align-middle">
                                            <?php if (isset($activity['image']) && !empty($activity['image'])): ?>
                                                <img src="../../uploads/<?php echo sanitizeOutput($activity['image']); ?>" 
                                                     alt="Activity Image" 
                                                     class="img-thumbnail" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?php echo sanitizeOutput($activity['title']); ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?php echo sanitizeOutput(truncateText($activity['content'], 80)); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div>
                                                <div class="fw-medium">
                                                    <?php echo formatDate($activity['created_at'], 'M j, Y'); ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo formatDate($activity['created_at'], 'g:i A'); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <?php 
                                            $status = $activity['status'] ?? 'published';
                                            $statusClass = $status === 'published' ? 'success' : 'warning';
                                            $statusIcon = $status === 'published' ? 'eye' : 'eye-slash';
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                                <?php echo ucfirst(sanitizeOutput($status)); ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="btn-group" role="group" aria-label="Activity actions">
                                                <a href="../../public/activity-detail.php?id=<?php echo objectIdToString($activity['_id']); ?>" 
                                                   class="btn btn-outline-info btn-sm" 
                                                   title="View Activity" 
                                                   target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo objectIdToString($activity['_id']); ?>" 
                                                   class="btn btn-outline-primary btn-sm" 
                                                   title="Edit Activity">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm" 
                                                        title="Delete Activity"
                                                        onclick="confirmDelete('<?php echo objectIdToString($activity['_id']); ?>', '<?php echo sanitizeOutput(addslashes($activity['title'])); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($activities)): ?>
            <div class="card-footer bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            Showing <?php echo count($activities); ?> of <?php echo $totalActivities; ?> activities
                        </small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Click on actions to view, edit, or delete activities
                        </small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Stats Cards -->
<?php if (!empty($activities)): ?>
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Activities</h6>
                        <h3 class="mb-0"><?php echo $totalActivities; ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-newspaper fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Published</h6>
                        <h3 class="mb-0">
                            <?php 
                            $publishedCount = 0;
                            foreach ($activities as $activity) {
                                if (($activity['status'] ?? 'published') === 'published') {
                                    $publishedCount++;
                                }
                            }
                            echo $publishedCount;
                            ?>
                        </h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-eye fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Drafts</h6>
                        <h3 class="mb-0">
                            <?php 
                            $draftCount = 0;
                            foreach ($activities as $activity) {
                                if (($activity['status'] ?? 'published') === 'draft') {
                                    $draftCount++;
                                }
                            }
                            echo $draftCount;
                            ?>
                        </h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-eye-slash fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">This Month</h6>
                        <h3 class="mb-0">
                            <?php 
                            $thisMonthCount = 0;
                            $currentMonth = date('Y-m');
                            foreach ($activities as $activity) {
                                $activityMonth = formatDate($activity['created_at'], 'Y-m');
                                if ($activityMonth === $currentMonth) {
                                    $thisMonthCount++;
                                }
                            }
                            echo $thisMonthCount;
                            ?>
                        </h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
/**
 * Confirm deletion of activity
 */
function confirmDelete(activityId, activityTitle) {
    if (confirm(`Are you sure you want to delete the activity "${activityTitle}"?\n\nThis action cannot be undone.`)) {
        // Create a form and submit it to delete.php
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = activityId;
        
        const confirmInput = document.createElement('input');
        confirmInput.type = 'hidden';
        confirmInput.name = 'confirm';
        confirmInput.value = '1';
        
        form.appendChild(idInput);
        form.appendChild(confirmInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-refresh page every 5 minutes to show latest activities
setTimeout(function() {
    location.reload();
}, 300000);

// Add tooltips to action buttons
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+N or Cmd+N to create new activity
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        window.location.href = 'create.php';
    }
    
    // F5 to refresh
    if (e.key === 'F5') {
        e.preventDefault();
        location.reload();
    }
});

// Show loading state for action buttons
document.querySelectorAll('.btn-group a').forEach(function(link) {
    link.addEventListener('click', function() {
        const icon = this.querySelector('i');
        const originalClass = icon.className;
        icon.className = 'fas fa-spinner fa-spin';
        
        // Restore original icon after 3 seconds as fallback
        setTimeout(function() {
            icon.className = originalClass;
        }, 3000);
    });
});
</script>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>