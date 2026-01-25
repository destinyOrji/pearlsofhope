<?php
/**
 * Admin Messages List
 * View all contact form messages
 */

$page_title = 'Contact Messages';
require_once '../../includes/admin-header.php';
require_once '../../includes/contact_fallback.php';

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build filter
$filter = [];
if ($status_filter !== 'all') {
    $filter['status'] = $status_filter;
}

// Get messages from database
$messages = [];
$total_messages = 0;
$error_message = '';

try {
    $messagesCollection = getCollection('contact_messages');
    $messages_from_db = [];
    
    if ($messagesCollection) {
        // Get total count
        $total_messages = countDocuments($messagesCollection, $filter);
        
        // Get messages with pagination
        $options = [
            'sort' => ['created_at' => -1],
            'skip' => $offset,
            'limit' => $per_page
        ];
        
        $cursor = findDocuments($messagesCollection, $filter, $options);
        if ($cursor) {
            $messages_from_db = $cursor->toArray();
        }
    }
    
    // Get messages from file fallback
    $messages_from_files = getContactMessagesFromFiles();
    
    // Filter file messages by status if needed
    if ($status_filter !== 'all') {
        $messages_from_files = array_filter($messages_from_files, function($msg) use ($status_filter) {
            return $msg['status'] === $status_filter;
        });
    }
    
    // Combine messages from both sources
    $all_messages = array_merge($messages_from_db, $messages_from_files);
    
    // Sort by created_at descending
    usort($all_messages, function($a, $b) {
        $timeA = isset($a['created_at']) ? 
            ($a['created_at'] instanceof MongoDB\BSON\UTCDateTime ? $a['created_at']->toDateTime()->getTimestamp() : strtotime($a['created_at'])) : 0;
        $timeB = isset($b['created_at']) ? 
            ($b['created_at'] instanceof MongoDB\BSON\UTCDateTime ? $b['created_at']->toDateTime()->getTimestamp() : strtotime($b['created_at'])) : 0;
        return $timeB - $timeA;
    });
    
    // Apply pagination to combined results
    $total_messages = count($all_messages);
    $messages = array_slice($all_messages, $offset, $per_page);
    
} catch (Exception $e) {
    error_log("Error fetching messages: " . $e->getMessage());
    
    // Fallback to file-only messages
    try {
        $messages_from_files = getContactMessagesFromFiles();
        
        // Filter by status if needed
        if ($status_filter !== 'all') {
            $messages_from_files = array_filter($messages_from_files, function($msg) use ($status_filter) {
                return $msg['status'] === $status_filter;
            });
        }
        
        $total_messages = count($messages_from_files);
        $messages = array_slice($messages_from_files, $offset, $per_page);
        
        error_log("Using file fallback for messages, found: " . count($messages_from_files));
    } catch (Exception $fallbackException) {
        error_log("Fallback error: " . $fallbackException->getMessage());
        $error_message = "Error loading messages. Please try again.";
    }
}

// Calculate pagination
$total_pages = $total_messages > 0 ? ceil($total_messages / $per_page) : 0;

// Get status counts for filter tabs
$status_counts = [
    'all' => 0,
    'unread' => 0,
    'read' => 0,
    'replied' => 0
];

try {
    if ($messagesCollection) {
        $status_counts['all'] = countDocuments($messagesCollection, []);
        $status_counts['unread'] = countDocuments($messagesCollection, ['status' => 'unread']);
        $status_counts['read'] = countDocuments($messagesCollection, ['status' => 'read']);
        $status_counts['replied'] = countDocuments($messagesCollection, ['status' => 'replied']);
    }
} catch (Exception $e) {
    error_log("Error getting status counts: " . $e->getMessage());
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-envelope me-2"></i>Contact Messages</h2>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" onclick="markAllAsRead()">
                    <i class="fas fa-check-double me-1"></i>Mark All as Read
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="deleteSelected()" id="deleteSelectedBtn" style="display: none;">
                    <i class="fas fa-trash me-1"></i>Delete Selected
                </button>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo sanitizeOutput($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Status Filter Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter === 'all' ? 'active' : ''; ?>" 
                   href="?status=all">
                    All Messages <span class="badge bg-secondary ms-1"><?php echo $status_counts['all']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter === 'unread' ? 'active' : ''; ?>" 
                   href="?status=unread">
                    Unread <span class="badge bg-danger ms-1"><?php echo $status_counts['unread']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter === 'read' ? 'active' : ''; ?>" 
                   href="?status=read">
                    Read <span class="badge bg-info ms-1"><?php echo $status_counts['read']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter === 'replied' ? 'active' : ''; ?>" 
                   href="?status=replied">
                    Replied <span class="badge bg-success ms-1"><?php echo $status_counts['replied']; ?></span>
                </a>
            </li>
        </ul>

        <?php if (empty($messages)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No Messages Found</h4>
                    <p class="text-muted">
                        <?php if ($status_filter === 'all'): ?>
                            No contact messages have been received yet.
                        <?php else: ?>
                            No <?php echo $status_filter; ?> messages found.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <!-- Messages List -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Status</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $message): ?>
                                    <tr class="message-row <?php echo $message['status'] === 'unread' ? 'table-warning' : ''; ?>" 
                                        data-id="<?php echo $message['_id']; ?>">
                                        <td>
                                            <input type="checkbox" class="form-check-input message-checkbox" 
                                                   value="<?php echo $message['_id']; ?>">
                                        </td>
                                        <td>
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
                                            <span class="badge <?php echo $status_class[$message['status']] ?? 'bg-secondary'; ?>">
                                                <i class="<?php echo $status_icon[$message['status']] ?? 'fas fa-question'; ?> me-1"></i>
                                                <?php echo ucfirst($message['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo sanitizeOutput($message['name']); ?></strong>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo sanitizeOutput($message['email']); ?>" 
                                               class="text-decoration-none">
                                                <?php echo sanitizeOutput($message['email']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                                  title="<?php echo sanitizeOutput($message['subject']); ?>">
                                                <?php echo sanitizeOutput($message['subject']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo formatDate($message['created_at'], 'M j, Y g:i A'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="view.php?id=<?php echo $message['_id']; ?>" 
                                                   class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deleteMessage('<?php echo $message['_id']; ?>')" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Messages pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $page - 1; ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $page + 1; ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <div class="text-center text-muted">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_messages); ?> 
                    of <?php echo $total_messages; ?> messages
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Select all checkbox functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.message-checkbox');
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    
    deleteBtn.style.display = this.checked ? 'inline-block' : 'none';
});

// Individual checkbox change
document.querySelectorAll('.message-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        const selectAll = document.getElementById('selectAll');
        
        deleteBtn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
        selectAll.checked = checkedBoxes.length === document.querySelectorAll('.message-checkbox').length;
    });
});

// Mark all as read
function markAllAsRead() {
    if (confirm('Mark all messages as read?')) {
        fetch('actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_all_read',
                status_filter: '<?php echo $status_filter; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error marking messages as read');
        });
    }
}

// Delete single message
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
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error deleting message');
        });
    }
}

// Delete selected messages
function deleteSelected() {
    const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select messages to delete');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${checkedBoxes.length} selected message(s)?`)) {
        const messageIds = Array.from(checkedBoxes).map(cb => cb.value);
        
        fetch('actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_messages',
                message_ids: messageIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error deleting messages');
        });
    }
}
</script>

<?php require_once '../../includes/admin-footer.php'; ?>