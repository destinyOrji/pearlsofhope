<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Check authentication
checkAuth();

$message = '';
$error = '';

// Handle form submission
if ($_POST) {
    $name = sanitizeInput($_POST['name']);
    $role = sanitizeInput($_POST['role']);
    $description = $_POST['description']; // Don't sanitize as it may contain line breaks
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $status = sanitizeInput($_POST['status']);
    $display_order = (int)$_POST['display_order'];
    
    // Validate inputs
    if (empty($name)) {
        $error = 'Name is required.';
    } elseif (empty($role)) {
        $error = 'Role is required.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Handle image upload
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['filename'];
            } else {
                $error = $uploadResult['error'];
            }
        }
        
        if (empty($error)) {
            try {
                $teamCollection = getCollection('team_members');
                
                $teamMember = [
                    'name' => $name,
                    'role' => $role,
                    'description' => $description,
                    'email' => $email,
                    'phone' => $phone,
                    'image' => $imagePath,
                    'status' => $status,
                    'display_order' => $display_order,
                    'created_at' => new MongoDB\BSON\UTCDateTime(),
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ];
                
                $result = insertDocument($teamCollection, $teamMember);
                
                if ($result && $result->getInsertedCount() > 0) {
                    $message = 'Team member created successfully!';
                    // Clear form data
                    $_POST = [];
                } else {
                    $error = 'Failed to create team member.';
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include '../../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Add New Team Member</h2>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Team List
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                           required maxlength="100">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role/Position *</label>
                                    <input type="text" class="form-control" id="role" name="role" 
                                           value="<?php echo isset($_POST['role']) ? htmlspecialchars($_POST['role']) : ''; ?>" 
                                           required maxlength="100"
                                           placeholder="e.g., Executive Director, Program Manager">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           maxlength="100">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                           maxlength="20">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control" id="image" name="image" 
                                           accept="image/jpeg,image/png,image/gif">
                                    <div class="form-text">Optional. JPG, PNG, or GIF format. Max 5MB.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" 
                                           value="<?php echo isset($_POST['display_order']) ? $_POST['display_order'] : '0'; ?>" 
                                           min="0" max="999">
                                    <div class="form-text">Lower numbers appear first. Use 0 for default ordering.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description/Bio</label>
                            <textarea class="form-control" id="description" name="description" rows="6"
                                      placeholder="Brief description about the team member, their background, experience, etc."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Team Member
                            </button>
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>