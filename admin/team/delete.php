<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Check authentication
checkAuth();

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

try {
    $teamCollection = getCollection('team_members');
    
    // Get team member data to delete associated image
    $teamMember = findOneDocument($teamCollection, ['_id' => new MongoDB\BSON\ObjectId($memberId)]);
    
    if ($teamMember) {
        // Delete associated image file if it exists
        if (!empty($teamMember['image'])) {
            $imagePath = UPLOAD_DIR . $teamMember['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete team member from database
        $result = deleteDocument($teamCollection, ['_id' => new MongoDB\BSON\ObjectId($memberId)]);
        
        if ($result && $result->getDeletedCount() > 0) {
            $_SESSION['success_message'] = 'Team member deleted successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to delete team member.';
        }
    } else {
        $_SESSION['error_message'] = 'Team member not found.';
    }
} catch (Exception $e) {
    error_log("Error deleting team member: " . $e->getMessage());
    $_SESSION['error_message'] = 'Database error occurred while deleting team member.';
}

header('Location: list.php');
exit;
?>