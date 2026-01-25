<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Check authentication
checkAuth();

$message = '';
$error = '';
$selectedPage = '';
$pageData = null;

// Handle form submission
if ($_POST) {
    $selectedPage = sanitizeInput($_POST['page_name']);
    $title = sanitizeInput($_POST['title']);
    $content = $_POST['content']; // Don't sanitize content as it may contain HTML
    
    // Validate inputs
    if (empty($selectedPage)) {
        $error = 'Please select a page to edit.';
    } elseif (empty($title)) {
        $error = 'Page title is required.';
    } elseif (empty($content)) {
        $error = 'Page content is required.';
    } else {
        try {
            $pagesCollection = getCollection('pages');
            
            // Check if page exists
            $existingPage = findOneDocument($pagesCollection, ['page_name' => $selectedPage]);
            
            $pageDocument = [
                'page_name' => $selectedPage,
                'title' => $title,
                'content' => $content,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ];
            
            if ($existingPage) {
                // Update existing page
                $result = updateDocument($pagesCollection, 
                    ['page_name' => $selectedPage], 
                    ['$set' => $pageDocument]
                );
            } else {
                // Insert new page
                $result = insertDocument($pagesCollection, $pageDocument);
            }
            
            if ($result) {
                $message = 'Page content updated successfully!';
            } else {
                $error = 'Failed to update page content.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Load page data if page is selected
if (isset($_GET['page']) || isset($_POST['page_name'])) {
    $selectedPage = isset($_GET['page']) ? sanitizeInput($_GET['page']) : $selectedPage;
    
    if (!empty($selectedPage)) {
        try {
            $pagesCollection = getCollection('pages');
            $pageData = findOneDocument($pagesCollection, ['page_name' => $selectedPage]);
        } catch (Exception $e) {
            $error = 'Failed to load page data: ' . $e->getMessage();
        }
    }
}

include '../../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2>Edit Static Pages</h2>
            
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
            
            <!-- Page Selection Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Select Page to Edit</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <select name="page" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Select a page --</option>
                                    <option value="home" <?php echo ($selectedPage === 'home') ? 'selected' : ''; ?>>Home Page</option>
                                    <option value="about" <?php echo ($selectedPage === 'about') ? 'selected' : ''; ?>>About Page</option>
                                    <option value="contact" <?php echo ($selectedPage === 'contact') ? 'selected' : ''; ?>>Contact Page</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Page Edit Form -->
            <?php if ($selectedPage): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Edit <?php echo ucfirst($selectedPage); ?> Page Content</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="page_name" value="<?php echo $selectedPage; ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Page Title</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo $pageData ? htmlspecialchars($pageData['title']) : ''; ?>" 
                                   required maxlength="255">
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Page Content</label>
                            <textarea class="form-control" id="content" name="content" rows="15" required><?php 
                                echo $pageData ? htmlspecialchars($pageData['content']) : ''; 
                            ?></textarea>
                            <div class="form-text">You can use HTML tags for formatting.</div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>