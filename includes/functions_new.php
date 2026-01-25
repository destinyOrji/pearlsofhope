<?php
/**
 * Common utility functions for NGO Website
 */

// Prevent multiple inclusions
if (defined('NGO_FUNCTIONS_LOADED')) {
    return;
}
define('NGO_FUNCTIONS_LOADED', true);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Sanitize input data for output
 */
if (!function_exists('sanitizeOutput')) {
    function sanitizeOutput($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Sanitize input data for database storage
 */
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return trim(stripslashes($data));
    }
}

/**
 * Validate email address
 */
if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

/**
 * Validate required field
 */
if (!function_exists('validateRequired')) {
    function validateRequired($value, $minLength = 1) {
        return !empty(trim($value)) && strlen(trim($value)) >= $minLength;
    }
}

/**
 * Validate string length
 */
if (!function_exists('validateLength')) {
    function validateLength($value, $minLength = 0, $maxLength = PHP_INT_MAX) {
        $length = strlen(trim($value));
        return $length >= $minLength && $length <= $maxLength;
    }
}

/**
 * Validate MongoDB ObjectId string
 */
if (!function_exists('validateObjectId')) {
    function validateObjectId($id) {
        if (!is_string($id) || strlen($id) !== 24) {
            return false;
        }
        return ctype_xdigit($id);
    }
}

/**
 * Check if a string is a valid MongoDB ObjectId
 */
if (!function_exists('isValidObjectId')) {
    function isValidObjectId($id) {
        return validateObjectId($id);
    }
}

/**
 * Convert string to MongoDB ObjectId
 */
if (!function_exists('stringToObjectId')) {
    function stringToObjectId($id) {
        if (!validateObjectId($id)) {
            return false;
        }
        
        try {
            return new MongoDB\BSON\ObjectId($id);
        } catch (Exception $e) {
            error_log("Error converting string to ObjectId: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Convert ObjectId to string
 */
if (!function_exists('objectIdToString')) {
    function objectIdToString($objectId) {
        return (string) $objectId;
    }
}

/**
 * Check if user is authenticated (admin)
 */
if (!function_exists('isAuthenticated')) {
    function isAuthenticated() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}

/**
 * Check authentication and redirect if not authenticated
 */
if (!function_exists('checkAuth')) {
    function checkAuth($redirectUrl = '/admin/login.php') {
        if (!isAuthenticated()) {
            header('Location: ' . $redirectUrl);
            exit();
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $inactive = time() - $_SESSION['last_activity'];
            if ($inactive >= ADMIN_SESSION_TIMEOUT) {
                session_destroy();
                header('Location: ' . $redirectUrl . '?timeout=1');
                exit();
            }
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Get current authenticated admin ID
 */
if (!function_exists('getCurrentAdminId')) {
    function getCurrentAdminId() {
        return isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
    }
}

/**
 * Get current authenticated admin username
 */
if (!function_exists('getCurrentAdminUsername')) {
    function getCurrentAdminUsername() {
        return isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : null;
    }
}

/**
 * Format date for display
 */
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'Y-m-d H:i:s') {
        if ($date instanceof MongoDB\BSON\UTCDateTime) {
            return $date->toDateTime()->format($format);
        }
        return '';
    }
}

/**
 * Truncate text to specified length
 */
if (!function_exists('truncateText')) {
    function truncateText($text, $length = 150, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
}

/**
 * Redirect with message
 */
if (!function_exists('redirectWithMessage')) {
    function redirectWithMessage($url, $message, $type = 'info') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        header('Location: ' . $url);
        exit();
    }
}

/**
 * Get and clear flash message
 */
if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = [
                'message' => $_SESSION['flash_message'],
                'type' => $_SESSION['flash_type'] ?? 'info'
            ];
            
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            
            return $message;
        }
        
        return null;
    }
}

/**
 * Handle image upload with validation and saving
 */
if (!function_exists('handleImageUpload')) {
    function handleImageUpload($file) {
        $result = ['success' => false, 'filename' => '', 'error' => ''];
        
        // Validate the uploaded file
        $validation = validateImageUpload($file);
        if (!$validation['valid']) {
            $result['error'] = $validation['error'];
            return $result;
        }
        
        // Generate unique filename
        $filename = generateUniqueFilename($file['name']);
        
        // Save the file
        if (saveUploadedFile($file, $filename)) {
            $result['success'] = true;
            $result['filename'] = $filename;
        } else {
            $result['error'] = 'Failed to save uploaded file';
        }
        
        return $result;
    }
}

/**
 * Validate uploaded image file
 */
if (!function_exists('validateImageUpload')) {
    function validateImageUpload($file) {
        $result = ['valid' => false, 'error' => ''];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $result['error'] = 'No file uploaded';
            return $result;
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = 'File upload error: ' . $file['error'];
            return $result;
        }
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            $result['error'] = 'File size exceeds maximum allowed size of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB';
            return $result;
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            $result['error'] = 'Invalid file type. Only JPG, PNG, and GIF files are allowed';
            return $result;
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
            $result['error'] = 'Invalid file extension. Only JPG, PNG, and GIF files are allowed';
            return $result;
        }
        
        $result['valid'] = true;
        return $result;
    }
}

/**
 * Generate unique filename for uploaded file
 */
if (!function_exists('generateUniqueFilename')) {
    function generateUniqueFilename($originalName) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return uniqid('upload_', true) . '.' . $extension;
    }
}

/**
 * Save uploaded file
 */
if (!function_exists('saveUploadedFile')) {
    function saveUploadedFile($file, $filename) {
        $targetPath = UPLOAD_DIR . $filename;
        
        // Create upload directory if it doesn't exist
        if (!is_dir(UPLOAD_DIR)) {
            if (!mkdir(UPLOAD_DIR, 0755, true)) {
                error_log("Failed to create upload directory: " . UPLOAD_DIR);
                return false;
            }
        }
        
        return move_uploaded_file($file['tmp_name'], $targetPath);
    }
}

/**
 * Delete uploaded file
 */
if (!function_exists('deleteUploadedFile')) {
    function deleteUploadedFile($filename) {
        if (empty($filename)) {
            return true; // Nothing to delete
        }
        
        $filePath = UPLOAD_DIR . $filename;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true; // File doesn't exist, consider it deleted
    }
}

/**
 * Generate pagination links
 */
if (!function_exists('generatePagination')) {
    function generatePagination($currentPage, $totalPages, $baseUrl) {
        $pagination = [];
        
        // Previous page
        if ($currentPage > 1) {
            $pagination['prev'] = $baseUrl . '?page=' . ($currentPage - 1);
        }
        
        // Page numbers
        $pagination['pages'] = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            $pagination['pages'][] = [
                'number' => $i,
                'url' => $baseUrl . '?page=' . $i,
                'current' => $i === $currentPage
            ];
        }
        
        // Next page
        if ($currentPage < $totalPages) {
            $pagination['next'] = $baseUrl . '?page=' . ($currentPage + 1);
        }
        
        return $pagination;
    }
}
?>