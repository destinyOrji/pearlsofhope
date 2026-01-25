<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Check authentication
checkAuth();

$message = '';
$error = '';
$teamMember = null;

// Get team member ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$memberId = $_GET['id'];

// Validate ObjectId
if (!isValidObjectId($memberId)) {
    header('Location: list.php');
    exit;
}

// Get team member data
try {
    $teamCollection = getCollection('team_members');
    $teamMember = findOneDocument($teamCollection, ['_id' => new MongoDB\BSON\ObjectId($memberId)]);
    
    if (!$teamMember) {
        header('Location: list.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Error fetching team member: " . $e->getMessage());
    header('Location: list.php');
    exit;
}

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
        $imagePath = $teamMember['image']; // Keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                // Delete old image if it exists
                if (!empty($teamMember['image'])) {
                    $oldImagePath = UPLOAD_DIR . $teamMember['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $imagePath = $uploadResult['filename'];
            } else {
                $error = $uploadResult['error'];
            }
        }
        
        if (empty($error)) {
            try {
                $updateData = [
                    'name' => $name,
                    'role' => $role,
                    'description' => $description,
                    'email' => $email,
                    'phone' => $phone,
                    'image' => $imagePath,
                    'status' => $status,
                    'display_order' => $display_order,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ];
                
                $result = updateDocument($teamCollection, 
                    ['_id' => new MongoDB\BSON\ObjectId($memberId)], 
                    ['$set' => $updateData]
                );
                
                if ($result && $result->getModifiedCount() > 0) {
                    $message = 'Team member updated successfully!';
                    // Refresh team member data
                    $teamMember = findOneDocument($teamCollection, ['_id' => new MongoDB\BSON\ObjectId($memberId)]);
                } else {
                    $message = 'Team member updated successfully!'; // Even if no changes were made
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
                <h2>Edit Team Member</h2>
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
                                           value="<?php echo htmlspecialchars($teamMember['name']); ?>" 
                                           required maxlength="100">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role/Position *</label>
                                    <input type="text" class="form-control" id="role" name="role" 
                                           value="<?php echo htmlspecialchars($teamMember['role']); ?>" 
                                           required maxlength="100"
                                           placeholder="e.g., Executive Director, Program Manager">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($teamMember['email'] ?? ''); ?>" 
                                           maxlength="100">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($teamMember['phone'] ?? ''); ?>" 
                                           maxlength="20">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Profile Photo</label>
                                    <?php if (!empty($teamMember['image'])): ?>
                                        <div class="mb-2">
                                            <img src="../../uploads/<?php echo htmlspecialchars($teamMember['image']); ?>" 
                                                 alt="Current photo" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 150px; max-height: 150px;">
                                            <div class="form-text">Current photo</div>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="image" name="image" 
                                           accept="image/jpeg,image/png,image/gif">
                                    <div class="form-text">Optional. JPG, PNG, or GIF format. Max 5MB. Leave empty to keep current photo.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo ($teamMember['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($teamMember['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" 
                                           value="<?php echo $teamMember['display_order'] ?? 0; ?>" 
                                           min="0" max="999">
                                    <div class="form-text">Lower numbers appear first. Use 0 for default ordering.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description/Bio</label>
                            <textarea class="form-control" id="description" name="description" rows="6"
                                      placeholder="Brief description about the team member, their background, experience, etc."><?php echo htmlspecialchars($teamMember['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Team Member
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