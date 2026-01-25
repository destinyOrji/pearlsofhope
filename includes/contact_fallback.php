<?php
/**
 * Fallback contact form handler when MongoDB is not available
 */

/**
 * Save contact message to file when database is unavailable
 */
function saveContactMessageToFile($messageData) {
    $messagesDir = __DIR__ . '/../data/contact_messages';
    
    // Create directory if it doesn't exist
    if (!is_dir($messagesDir)) {
        if (!mkdir($messagesDir, 0755, true)) {
            error_log("Failed to create contact messages directory: " . $messagesDir);
            return false;
        }
    }
    
    // Generate unique filename
    $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.json';
    $filepath = $messagesDir . '/' . $filename;
    
    // Prepare message data
    $messageRecord = [
        'id' => uniqid(),
        'name' => $messageData['name'],
        'email' => $messageData['email'],
        'subject' => $messageData['subject'],
        'message' => $messageData['message'],
        'status' => 'unread',
        'created_at' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    // Save to file
    $jsonData = json_encode($messageRecord, JSON_PRETTY_PRINT);
    if (file_put_contents($filepath, $jsonData) !== false) {
        error_log("Contact message saved to file: " . $filepath);
        return true;
    } else {
        error_log("Failed to save contact message to file: " . $filepath);
        return false;
    }
}

/**
 * Get all contact messages from files
 */
function getContactMessagesFromFiles() {
    $messagesDir = __DIR__ . '/../data/contact_messages';
    $messages = [];
    
    if (!is_dir($messagesDir)) {
        return $messages;
    }
    
    $files = glob($messagesDir . '/*.json');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        if ($content !== false) {
            $message = json_decode($content, true);
            if ($message !== null) {
                $messages[] = $message;
            }
        }
    }
    
    // Sort by created_at descending
    usort($messages, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return $messages;
}
?>