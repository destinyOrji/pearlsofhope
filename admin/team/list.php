<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Check authentication
checkAuth();

// Get all team members
$teamMembers = [];
try {
    $teamCollection = getCollection('team_members');
    if ($teamCollection) {
        $cursor = findDocuments($teamCollection, [], ['sort' => ['display_order' => 1, 'created_at' => 1]]);
        if ($cursor) {
            $teamMembers = $cursor->toArray();
        }
    }
} catch (Exception $e) {
    error_log("Error fetching team members: " . $e->getMessage());
}

include '../../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Team Members</h2>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Team Member
                </a>
            </div>
            
            <?php if (empty($teamMembers)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No team members found. <a href="create.php">Add the first team member</a>.
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Order</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teamMembers as $member): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($member['image'])): ?>
                                                    <img src="../../uploads/<?php echo htmlspecialchars($member['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($member['name']); ?>" 
                                                         class="rounded-circle admin-team-thumb" 
                                                         style="width: 50px; height: 50px; object-fit: cover; object-position: center;">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($member['name']); ?></strong>
                                                <?php if (!empty($member['email'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($member['email']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($member['role']); ?></td>
                                            <td>
                                                <?php if ($member['status'] === 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo isset($member['display_order']) ? $member['display_order'] : 0; ?></td>
                                            <td>
                                                <?php 
                                                if (isset($member['created_at'])) {
                                                    echo $member['created_at']->toDateTime()->format('M j, Y');
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="edit.php?id=<?php echo $member['_id']; ?>" 
                                                       class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?php echo $member['_id']; ?>" 
                                                       class="btn btn-outline-danger" 
                                                       title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this team member?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>