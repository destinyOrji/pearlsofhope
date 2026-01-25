<?php
/**
 * Admin Message View
 * View individual contact message and reply
 */

$page_title = 'View Message';
require_once '../../includes/admin-header.php';

// Get message ID
$message_id = $_GET['id'] ?? '';

if (empty($message_id) || !isValidObjectId($message_id)) {
    redirectWithMessage('list.php', 'Invalid message ID', 'error');
}

// Get message from database
$message = null;
$error_message = '';

try {
    $messagesCollection = getCollection('contact_messages');
    if ($messagesCollection) {
        $objectId = stringToObjectId($message_id);
        if ($objectId) {
            $message = findOneDocument($messagesCollection, ['_id' => $objectId]);
            
            // Mark as read if it's unread
            if ($message && $message['status'] === 'unread') {
                updateDocument($messagesCollection, 
                    ['_id' => $objectId], 
                    ['$set' => ['status' => 'read']]
                );
                $message['status'] = 'read'; // Update local copy
            }
        }
    }
    
    if (!$message) {
        redirectWithMessage('list.php', 'Message not found', 'error');
    }
} catch (Exception $e) {
    error_log("Error fetching message: " . $e->getMessage());
    $error_message = "Error loading message. Please try again.";
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $reply_message = sanitizeInput($_POST['reply_message'] ?? '');
    
    if (!empty($reply_message)) {
        try {
            // Update message status to replied
            $result = updateDocument($messagesCollection, 
                ['_id' => stringToObjectId($message_id)], 
                ['$set' => [
                    'status' => 'replied',
                    'reply_message' => $reply_message,
                    'replied_by' => getCurrentAdminUsername(),
                    'replied_at' => new MongoDB\BSON\UTCDateTime()
                ]]
            );
            
            if ($result) {
                // Here you could also send an email to the person
                // For now, just show success message
                redirectWithMessage('view.php?id=' . $message_id, 'Reply sent successfully!', 'success');
            } else {
                $error_message = 'Error sending reply. Please try again.';
            }
        } catch (Exception $e) {
            error_log("Error sending reply: " . $e->getMessage());
            $error_message = 'Error sending reply. Please try again.';
        }
    } else {
        $error_message = 'Please enter a reply message.';
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-envelope-open me-2"></i>View Message</h2>
            <div class="btn-group" role="group">
                <a href="list.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Messages
                </a>
                <button type="button" class="btn btn-outline-danger" 
                        onclick="deleteMessage('<?php echo $message['_id']; ?>')">
                    <i class="fas fa-trash me-1"></i>Delete
                </button>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo sanitizeOutput($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <!-- Message Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i><?php echo sanitizeOutput($message['name']); ?>
                            </h5>
                            <small class="text-muted">
                                <i class="fas fa-envelope me-1"></i><?php echo sanitizeOutput($message['email']); ?>
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php
                            $status_class = [
                                'unread' => 'bg-danger',
                                'read' => 'bg-info',
                                'replied' => 'bg-success'
                            ];
                            $status_icon = [
                                'unread' => 'fas fa-envelope',
                                'read' => 'fas fa-envelope-open',
                                'replied' => 'fas fa-reply'
                            ];
                            ?>
                            <span class="badge <?php echo $status_class[$message['status']] ?? 'bg-secondary'; ?> fs-6">
                                <i class="<?php echo $status_icon[$message['status']] ?? 'fas fa-question'; ?> me-1"></i>
                                <?php echo ucfirst($message['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Subject:</strong><br>
                            <span class="text-primary"><?php echo sanitizeOutput($message['subject']); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Date:</strong><br>
                            <span class="text-muted">
                                <?php echo formatDate($message['created_at'], 'F j, Y \a\t g:i A'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Message:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            <?php echo nl2br(sanitizeOutput($message['message'])); ?>
                        </div>
                    </div>
                    
                    <!-- Technical Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>IP Address:</strong> <?php echo sanitizeOutput($message['ip_address'] ?? 'Unknown'); ?>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>User Agent:</strong> 
                                <span class="text-truncate d-inline-block" style="max-width: 300px;" 
                                      title="<?php echo sanitizeOutput($message['user_agent'] ?? 'Unknown'); ?>">
                                    <?php echo sanitizeOutput($message['user_agent'] ?? 'Unknown'); ?>
                                </span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Previous Reply (if exists) -->
            <?php if (isset($message['reply_message']) && !empty($message['reply_message'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-reply me-2"></i>Your Reply
                        </h6>
                        <small>
                            Sent by <?php echo sanitizeOutput($message['replied_by'] ?? 'Admin'); ?> 
                            on <?php echo formatDate($message['replied_at'], 'F j, Y \a\t g:i A'); ?>
                        </small>
                    </div>
                    <div class="card-body">
                        <?php echo nl2br(sanitizeOutput($message['reply_message'])); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reply Form -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-paper-plane me-2"></i>
                        <?php echo isset($message['reply_message']) ? 'Send Another Reply' : 'Send Reply'; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="reply_message" class="form-label">Reply Message</label>
                            <textarea class="form-control" 
                                      id="reply_message" 
                                      name="reply_message" 
                                      rows="6" 
                                      required 
                                      placeholder="Type your reply here..."></textarea>
                            <div class="form-text">
                                This reply will be saved in the database. 
                                <?php if (!empty($message['email'])): ?>
                                You can also email them directly at 
                                <a href="mailto:<?php echo sanitizeOutput($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>">
                                    <?php echo sanitizeOutput($message['email']); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <div>
                                <?php if (!empty($message['email'])): ?>
                                    <a href="mailto:<?php echo sanitizeOutput($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-envelope me-1"></i>Send Email Directly
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-1"></i>Save Reply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        fetch('actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_message',
                message_id: messageId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'list.php';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error deleting message');
        });
    }
}
</script>

<?php require_once '../../includes/admin-footer.php'; ?>