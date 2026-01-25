<?php
/**
 * Admin Messages Actions Handler
 * Handles AJAX requests for message operations
 */

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Check authentication
checkAuth();

// Set JSON response header
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$response = ['success' => false, 'message' => ''];

try {
    $messagesCollection = getCollection('contact_messages');
    
    if (!$messagesCollection) {
        throw new Exception('Database connection error');
    }
    
    switch ($action) {
        case 'mark_all_read':
            $status_filter = $input['status_filter'] ?? 'all';
            
            // Build filter for update
            $filter = [];
            if ($status_filter !== 'all') {
                $filter['status'] = $status_filter;
            }
            
            // Only update unread messages
            $filter['status'] = 'unread';
            
            $result = $messagesCollection->updateMany(
                $filter,
                ['$set' => [
                    'status' => 'read',
                    'marked_read_at' => new MongoDB\BSON\UTCDateTime(),
                    'marked_read_by' => getCurrentAdminUsername()
                ]]
            );
            
            $response['success'] = true;
            $response['message'] = $result->getModifiedCount() . ' messages marked as read';
            break;
            
        case 'delete_message':
            $message_id = $input['message_id'] ?? '';
            
            if (empty($message_id) || !isValidObjectId($message_id)) {
                throw new Exception('Invalid message ID');
            }
            
            $objectId = stringToObjectId($message_id);
            $result = deleteDocument($messagesCollection, ['_id' => $objectId]);
            
            if ($result && $result->getDeletedCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Message deleted successfully';
            } else {
                throw new Exception('Message not found or could not be deleted');
            }
            break;
            
        case 'delete_messages':
            $message_ids = $input['message_ids'] ?? [];
            
            if (empty($message_ids) || !is_array($message_ids)) {
                throw new Exception('No messages selected');
            }
            
            // Convert string IDs to ObjectIds
            $objectIds = [];
            foreach ($message_ids as $id) {
                if (isValidObjectId($id)) {
                    $objectIds[] = stringToObjectId($id);
                }
            }
            
            if (empty($objectIds)) {
                throw new Exception('No valid message IDs provided');
            }
            
            $result = $messagesCollection->deleteMany(['_id' => ['$in' => $objectIds]]);
            
            $response['success'] = true;
            $response['message'] = $result->getDeletedCount() . ' messages deleted successfully';
            break;
            
        case 'update_status':
            $message_id = $input['message_id'] ?? '';
            $new_status = $input['status'] ?? '';
            
            if (empty($message_id) || !isValidObjectId($message_id)) {
                throw new Exception('Invalid message ID');
            }
            
            if (!in_array($new_status, ['unread', 'read', 'replied'])) {
                throw new Exception('Invalid status');
            }
            
            $objectId = stringToObjectId($message_id);
            $updateData = [
                'status' => $new_status,
                'status_updated_at' => new MongoDB\BSON\UTCDateTime(),
                'status_updated_by' => getCurrentAdminUsername()
            ];
            
            $result = updateDocument($messagesCollection, 
                ['_id' => $objectId], 
                ['$set' => $updateData]
            );
            
            if ($result && $result->getModifiedCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Status updated successfully';
            } else {
                throw new Exception('Message not found or status not changed');
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Messages action error: " . $e->getMessage());
}

echo json_encode($response);
?>