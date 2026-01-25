<?php
/**
 * Create Activity Page
 * Allows administrators to create new activity posts
 */

// Set page title for header
$page_title = 'Create Activity';

// Include admin header
require_once '../../includes/admin-header.php';

// Initialize variables
$errors = [];
$success = false;
$title = '';
$content = '';
$status = 'published';

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
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imageValidation = validateImageUpload($_FILES['image']);
        
        if (!$imageValidation['valid']) {
            $errors[] = $imageValidation['error'];
        } else {
            // Generate unique filename and save file
            $uniqueFilename = generateUniqueFilename($_FILES['image']['name']);
            
            if (saveUploadedFile($_FILES['image'], $uniqueFilename)) {
                $imagePath = $uniqueFilename;
            } else {
                $errors[] = 'Failed to save uploaded image';
            }
        }
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        $collection = getCollection('activities');
        
        if ($collection) {
            $activityData = [
                'title' => $title,
                'content' => $content,
                'status' => $status,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ];
            
            // Add image path if uploaded
            if ($imagePath) {
                $activityData['image'] = $imagePath;
            }
            
            $result = insertDocument($collection, $activityData);
            
            if ($result) {
                // Success - redirect to activity list with success message
                redirectWithMessage(
                    'list.php',
                    'Activity "' . $title . '" has been created successfully!',
                    'success'
                );
            } else {
                $errors[] = 'Failed to save activity to database';
                
                // Clean up uploaded image if database save failed
                if ($imagePath) {
                    deleteUploadedFile($imagePath);
                }
            }
        } else {
            $errors[] = 'Database connection error';
            
            // Clean up uploaded image if database connection failed
            if ($imagePath) {
                deleteUploadedFile($imagePath);
            }
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-plus-circle me-2 text-primary"></i>Create New Activity
            </h1>
            <a href="list.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Activities
            </a>
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
                        <input type="file" 
                               class="form-control <?php echo (isset($errors) && array_filter($errors, function($error) { return strpos($error, 'image') !== false || strpos($error, 'file') !== false; })) ? 'is-invalid' : ''; ?>" 
                               id="image" 
                               name="image" 
                               accept="image/jpeg,image/png,image/gif">
                        <div class="form-text">
                            Optional: Upload a featured image for the activity (JPG, PNG, or GIF, maximum 5MB)
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
                            Choose whether to publish the activity immediately or save as draft
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="list.php" class="btn btn-outline-secondary me-md-2">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Activity
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
                        <strong>Save as Draft</strong> to review content before publishing
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <strong>Use engaging titles</strong> to attract more readers
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <strong>Add images</strong> to make activities more visually appealing
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

// File size validation
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
                    <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                    <div class="small text-muted mt-1">Preview: ${file.name}</div>
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
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
    submitBtn.disabled = true;
    
    // Re-enable button after 10 seconds as fallback
    setTimeout(function() {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 10000);
});
</script>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>