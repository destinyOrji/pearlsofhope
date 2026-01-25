<?php
/**
 * Edit Activity Page
 * Allows administrators to edit existing activity posts
 */

// Set page title for header
$page_title = 'Edit Activity';

// Include admin header
require_once '../../includes/admin-header.php';

// Initialize variables
$errors = [];
$success = false;
$activity = null;
$title = '';
$content = '';
$status = 'published';
$currentImage = '';

// Get activity ID from URL parameter
$activityId = $_GET['id'] ?? '';

// Validate activity ID
if (empty($activityId)) {
    redirectWithMessage('list.php', 'Activity ID is required', 'error');
}

if (!validateObjectId($activityId)) {
    redirectWithMessage('list.php', 'Invalid activity ID format', 'error');
}

// Convert string ID to ObjectId
$objectId = stringToObjectId($activityId);
if (!$objectId) {
    redirectWithMessage('list.php', 'Invalid activity ID', 'error');
}

// Fetch activity from database
$collection = getCollection('activities');
if (!$collection) {
    redirectWithMessage('list.php', 'Database connection error', 'error');
}

$activity = findOneDocument($collection, ['_id' => $objectId]);
if (!$activity) {
    redirectWithMessage('list.php', 'Activity not found', 'error');
}

// Set form values from existing activity data
$title = $activity['title'] ?? '';
$content = $activity['content'] ?? '';
$status = $activity['status'] ?? 'published';
$currentImage = $activity['image'] ?? '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $title = sanitizeInput($_POST['title'] ?? '');
    $content = sanitizeInput($_POST['content'] ?? '');
    $status = sanitizeInput($_POST['status'] ?? 'published');
    
    // Validate required fields
    if (!validateRequired($title)) {
        $errors[] = 'Title is required';
    } elseif (!validateLength($title, 1, 255)) {
        $errors[] = 'Title must be between 1 and 255 characters';
    }
    
    if (!validateRequired($content, 10)) {
        $errors[] = 'Content is required and must be at least 10 characters';
    }
    
    // Validate status
    if (!in_array($status, ['draft', 'published'])) {
        $errors[] = 'Invalid status selected';
    }
    
    // Handle image upload if provided
    $newImagePath = null;
    $replaceImage = false;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imageValidation = validateImageUpload($_FILES['image']);
        
        if (!$imageValidation['valid']) {
            $errors[] = $imageValidation['error'];
        } else {
            // Generate unique filename and save file
            $uniqueFilename = generateUniqueFilename($_FILES['image']['name']);
            
            if (saveUploadedFile($_FILES['image'], $uniqueFilename)) {
                $newImagePath = $uniqueFilename;
                $replaceImage = true;
            } else {
                $errors[] = 'Failed to save uploaded image';
            }
        }
    }
    
    // If no errors, update in database
    if (empty($errors)) {
        $updateData = [
            '$set' => [
                'title' => $title,
                'content' => $content,
                'status' => $status
            ]
        ];
        
        // Add new image path if uploaded
        if ($replaceImage && $newImagePath) {
            $updateData['$set']['image'] = $newImagePath;
        }
        
        $result = updateDocument($collection, ['_id' => $objectId], $updateData);
        
        if ($result && $result->getModifiedCount() > 0) {
            // Delete old image if new one was uploaded
            if ($replaceImage && !empty($currentImage)) {
                deleteUploadedFile($currentImage);
            }
            
            // Success - redirect to activity list with success message
            redirectWithMessage(
                'list.php',
                'Activity "' . $title . '" has been updated successfully!',
                'success'
            );
        } else {
            $errors[] = 'Failed to update activity in database';
            
            // Clean up new uploaded image if database update failed
            if ($replaceImage && $newImagePath) {
                deleteUploadedFile($newImagePath);
            }
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2 text-primary"></i>Edit Activity
            </h1>
            <div>
                <a href="../../public/activity-detail.php?id=<?php echo $activityId; ?>" 
                   class="btn btn-outline-info me-2" 
                   target="_blank">
                    <i class="fas fa-eye me-1"></i>View Activity
                </a>
                <a href="list.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Activities
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger" role="alert">
            <h6 class="alert-heading">
                <i class="fas fa-exclamation-triangle me-1"></i>Please correct the following errors:
            </h6>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo sanitizeOutput($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>Activity Details
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" novalidate>
                    <div class="mb-3">
                        <label for="title" class="form-label">
                            Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control <?php echo in_array('Title is required', $errors) || in_array('Title must be between 1 and 255 characters', $errors) ? 'is-invalid' : ''; ?>" 
                               id="title" 
                               name="title" 
                               value="<?php echo sanitizeOutput($title); ?>" 
                               maxlength="255" 
                               required>
                        <div class="form-text">
                            Enter a descriptive title for the activity (maximum 255 characters)
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">
                            Content <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control <?php echo in_array('Content is required and must be at least 10 characters', $errors) ? 'is-invalid' : ''; ?>" 
                                  id="content" 
                                  name="content" 
                                  rows="10" 
                                  required><?php echo sanitizeOutput($content); ?></textarea>
                        <div class="form-text">
                            Provide detailed information about the activity (minimum 10 characters)
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">
                            Featured Image
                        </label>
                        
                        <?php if (!empty($currentImage)): ?>
                        <div class="mb-2">
                            <div class="border rounded p-2 bg-light">
                                <img src="../../uploads/<?php echo sanitizeOutput($currentImage); ?>" 
                                     alt="Current Image" 
                                     class="img-thumbnail" 
                                     style="max-width: 200px; max-height: 150px;">
                                <div class="small text-muted mt-1">
                                    Current image: <?php echo sanitizeOutput($currentImage); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <input type="file" 
                               class="form-control <?php echo (isset($errors) && array_filter($errors, function($error) { return strpos($error, 'image') !== false || strpos($error, 'file') !== false; })) ? 'is-invalid' : ''; ?>" 
                               id="image" 
                               name="image" 
                               accept="image/jpeg,image/png,image/gif">
                        <div class="form-text">
                            <?php if (!empty($currentImage)): ?>
                                Upload a new image to replace the current one (JPG, PNG, or GIF, maximum 5MB)
                            <?php else: ?>
                                Upload a featured image for the activity (JPG, PNG, or GIF, maximum 5MB)
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-select <?php echo in_array('Invalid status selected', $errors) ? 'is-invalid' : ''; ?>" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>
                                Published (visible to visitors)
                            </option>
                            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>
                                Draft (not visible to visitors)
                            </option>
                        </select>
                        <div class="form-text">
                            Choose whether to publish the activity or save as draft
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="list.php" class="btn btn-outline-secondary me-md-2">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Activity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Activity Information
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary">Created:</h6>
                    <p class="small mb-0">
                        <?php echo formatDate($activity['created_at'], 'F j, Y \a\t g:i A'); ?>
                    </p>
                </div>
                
                <?php if (isset($activity['updated_at'])): ?>
                <div class="mb-3">
                    <h6 class="text-primary">Last Updated:</h6>
                    <p class="small mb-0">
                        <?php echo formatDate($activity['updated_at'], 'F j, Y \a\t g:i A'); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <h6 class="text-primary">Activity ID:</h6>
                    <p class="small mb-0 font-monospace">
                        <?php echo sanitizeOutput($activityId); ?>
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Current Status:</h6>
                    <span class="badge bg-<?php echo $activity['status'] === 'published' ? 'success' : 'warning'; ?>">
                        <i class="fas fa-<?php echo $activity['status'] === 'published' ? 'eye' : 'eye-slash'; ?> me-1"></i>
                        <?php echo ucfirst(sanitizeOutput($activity['status'] ?? 'published')); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Guidelines
                </h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">Title Guidelines:</h6>
                <ul class="small mb-3">
                    <li>Keep it descriptive and engaging</li>
                    <li>Maximum 255 characters</li>
                    <li>Use proper capitalization</li>
                </ul>
                
                <h6 class="text-primary">Content Guidelines:</h6>
                <ul class="small mb-3">
                    <li>Minimum 10 characters required</li>
                    <li>Include relevant details about the activity</li>
                    <li>Use clear and engaging language</li>
                    <li>Break up long text with paragraphs</li>
                </ul>
                
                <h6 class="text-primary">Image Guidelines:</h6>
                <ul class="small mb-3">
                    <li>Supported formats: JPG, PNG, GIF</li>
                    <li>Maximum file size: 5MB</li>
                    <li>Recommended dimensions: 800x600px or larger</li>
                    <li>Use high-quality, relevant images</li>
                </ul>
                
                <h6 class="text-primary">Status Options:</h6>
                <ul class="small">
                    <li><strong>Published:</strong> Visible to all website visitors</li>
                    <li><strong>Draft:</strong> Only visible in admin panel</li>
                </ul>
            </div>
        </div>
        
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="card-title mb-0">
                    <i class="fas fa-lightbulb me-2"></i>Tips
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <p class="mb-2">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <strong>Save as Draft</strong> to review changes before publishing
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <strong>Upload new image</strong> to replace the current one
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <strong>View Activity</strong> to see how it appears to visitors
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<
script>
// Character counter for title
document.getElementById('title').addEventListener('input', function() {
    const maxLength = 255;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // Find or create character counter
    let counter = document.getElementById('title-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'title-counter';
        counter.className = 'form-text';
        this.parentNode.appendChild(counter);
    }
    
    counter.textContent = `${currentLength}/${maxLength} characters`;
    counter.className = remaining < 20 ? 'form-text text-warning' : 'form-text text-muted';
});

// Character counter for content
document.getElementById('content').addEventListener('input', function() {
    const minLength = 10;
    const currentLength = this.value.length;
    
    // Find or create character counter
    let counter = document.getElementById('content-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'content-counter';
        counter.className = 'form-text';
        this.parentNode.appendChild(counter);
    }
    
    if (currentLength < minLength) {
        counter.textContent = `${currentLength} characters (minimum ${minLength} required)`;
        counter.className = 'form-text text-danger';
    } else {
        counter.textContent = `${currentLength} characters`;
        counter.className = 'form-text text-muted';
    }
});

// File size validation and preview
document.getElementById('image').addEventListener('change', function() {
    const maxSize = 5 * 1024 * 1024; // 5MB
    const file = this.files[0];
    
    if (file && file.size > maxSize) {
        alert('File size exceeds 5MB limit. Please choose a smaller file.');
        this.value = '';
        return;
    }
    
    // Show file preview if it's an image
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Find or create preview container
            let preview = document.getElementById('image-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'image-preview';
                preview.className = 'mt-2';
                document.getElementById('image').parentNode.appendChild(preview);
            }
            
            preview.innerHTML = `
                <div class="border rounded p-2 bg-light">
                    <img src="${e.target.result}" alt="New Image Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                    <div class="small text-muted mt-1">New image preview: ${file.name}</div>
                    <div class="small text-info">This will replace the current image when you save</div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const content = document.getElementById('content').value.trim();
    
    if (!title) {
        alert('Please enter a title for the activity.');
        document.getElementById('title').focus();
        e.preventDefault();
        return false;
    }
    
    if (content.length < 10) {
        alert('Please enter at least 10 characters for the content.');
        document.getElementById('content').focus();
        e.preventDefault();
        return false;
    }
    
    // Confirm if user wants to proceed with image replacement
    const imageInput = document.getElementById('image');
    if (imageInput.files.length > 0) {
        const currentImageExists = <?php echo !empty($currentImage) ? 'true' : 'false'; ?>;
        if (currentImageExists) {
            if (!confirm('You have selected a new image. This will replace the current image. Do you want to continue?')) {
                e.preventDefault();
                return false;
            }
        }
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
    submitBtn.disabled = true;
    
    // Re-enable button after 10 seconds as fallback
    setTimeout(function() {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 10000);
});

// Initialize character counters on page load
document.addEventListener('DOMContentLoaded', function() {
    // Trigger input events to show initial character counts
    document.getElementById('title').dispatchEvent(new Event('input'));
    document.getElementById('content').dispatchEvent(new Event('input'));
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+S or Cmd+S to save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.querySelector('form').submit();
    }
    
    // Escape to cancel (go back to list)
    if (e.key === 'Escape') {
        if (confirm('Are you sure you want to cancel editing? Any unsaved changes will be lost.')) {
            window.location.href = 'list.php';
        }
    }
});

// Warn user about unsaved changes
let formChanged = false;
document.querySelectorAll('input, textarea, select').forEach(function(element) {
    element.addEventListener('change', function() {
        formChanged = true;
    });
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});

// Reset form changed flag when form is submitted
document.querySelector('form').addEventListener('submit', function() {
    formChanged = false;
});
</script>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>