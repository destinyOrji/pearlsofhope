<?php
/**
 * Delete Activity Handler
 * Handles deletion of activity posts with confirmation
 */

// Start session for authentication and flash messages
session_start();

// Include required files
require_once '../../includes/functions.php';
require_once '../../includes/db.php';

// Check authentication
checkAuth('../../admin/login.php');

// Initialize variables
$errors = [];
$activityId = '';
$activity = null;

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('list.php', 'Invalid request method', 'error');
}

// Get activity ID from POST data
$activityId = $_POST['id'] ?? '';
$confirm = $_POST['confirm'] ?? '';

// Validate required parameters
if (empty($activityId)) {
    redirectWithMessage('list.php', 'Activity ID is required', 'error');
}

if (empty($confirm) || $confirm !== '1') {
    redirectWithMessage('list.php', 'Deletion not confirmed', 'error');
}

// Validate activity ID format
if (!validateObjectId($activityId)) {
    redirectWithMessage('list.php', 'Invalid activity ID format', 'error');
}

// Convert string ID to ObjectId
$objectId = stringToObjectId($activityId);
if (!$objectId) {
    redirectWithMessage('list.php', 'Invalid activity ID', 'error');
}

// Get database collection
$collection = getCollection('activities');
if (!$collection) {
    redirectWithMessage('list.php', 'Database connection error', 'error');
}

// Fetch activity to get image path before deletion
$activity = findOneDocument($collection, ['_id' => $objectId]);
if (!$activity) {
    redirectWithMessage('list.php', 'Activity not found', 'error');
}

// Store activity title for success message
$activityTitle = $activity['title'] ?? 'Unknown Activity';
$activityImage = $activity['image'] ?? '';

try {
    // Delete the activity document from MongoDB
    $deleteResult = deleteDocument($collection, ['_id' => $objectId]);
    
    if ($deleteResult && $deleteResult->getDeletedCount() > 0) {
        // Successfully deleted from database, now delete associated image file
        if (!empty($activityImage)) {
            $imageDeleted = deleteUploadedFile($activityImage);
            if (!$imageDeleted) {
                error_log("Warning: Failed to delete image file: " . $activityImage);
                // Don't fail the entire operation if image deletion fails
            }
        }
        
        // Success - redirect with success message
        redirectWithMessage(
            'list.php',
            'Activity "' . $activityTitle . '" has been deleted successfully!',
            'success'
        );
    } else {
        // Failed to delete from database
        redirectWithMessage('list.php', 'Failed to delete activity from database', 'error');
    }
    
} catch (Exception $e) {
    // Log the error and redirect with error message
    error_log("Error deleting activity: " . $e->getMessage());
    redirectWithMessage('list.php', 'An error occurred while deleting the activity', 'error');
}
?>