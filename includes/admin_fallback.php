<?php
/**
 * Fallback admin authentication when MongoDB is not available
 */

/**
 * Get admin data directory
 */
function getAdminDataDir() {
    return __DIR__ . '/../data/admin';
}

/**
 * Ensure admin data directory exists
 */
function ensureAdminDataDir() {
    $dir = getAdminDataDir();
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            error_log("Failed to create admin data directory: " . $dir);
            return false;
        }
    }
    return true;
}

/**
 * Get admin file path
 */
function getAdminFilePath($username) {
    return getAdminDataDir() . '/' . md5($username) . '.json';
}

/**
 * Create default admin user if none exists
 */
function createDefaultAdmin() {
    if (!ensureAdminDataDir()) {
        return false;
    }
    
    $defaultUsername = 'admin';
    $defaultPassword = 'admin123'; // Change this in production!
    
    $adminFile = getAdminFilePath($defaultUsername);
    
    if (!file_exists($adminFile)) {
        $adminData = [
            'id' => uniqid(),
            'username' => $defaultUsername,
            'password' => password_hash($defaultPassword, PASSWORD_DEFAULT),
            'email' => 'admin@example.com',
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => null,
            'status' => 'active'
        ];
        
        $jsonData = json_encode($adminData, JSON_PRETTY_PRINT);
        if (file_put_contents($adminFile, $jsonData) !== false) {
            error_log("Default admin user created: username=admin, password=admin123");
            return true;
        } else {
            error_log("Failed to create default admin user file");
            return false;
        }
    }
    
    return true;
}

/**
 * Find admin by username from file
 */
function findAdminByUsername($username) {
    if (!ensureAdminDataDir()) {
        return null;
    }
    
    $adminFile = getAdminFilePath($username);
    
    if (!file_exists($adminFile)) {
        return null;
    }
    
    $content = file_get_contents($adminFile);
    if ($content === false) {
        return null;
    }
    
    $adminData = json_decode($content, true);
    if ($adminData === null) {
        return null;
    }
    
    return $adminData;
}

/**
 * Update admin last login time
 */
function updateAdminLastLogin($username) {
    if (!ensureAdminDataDir()) {
        return false;
    }
    
    $adminFile = getAdminFilePath($username);
    
    if (!file_exists($adminFile)) {
        return false;
    }
    
    $content = file_get_contents($adminFile);
    if ($content === false) {
        return false;
    }
    
    $adminData = json_decode($content, true);
    if ($adminData === null) {
        return false;
    }
    
    $adminData['last_login'] = date('Y-m-d H:i:s');
    
    $jsonData = json_encode($adminData, JSON_PRETTY_PRINT);
    return file_put_contents($adminFile, $jsonData) !== false;
}

/**
 * Authenticate admin with fallback system
 */
function authenticateAdminFallback($username, $password) {
    // Ensure default admin exists
    createDefaultAdmin();
    
    // Find admin by username
    $admin = findAdminByUsername($username);
    
    if (!$admin) {
        return false;
    }
    
    // Verify password
    if (!password_verify($password, $admin['password'])) {
        return false;
    }
    
    // Update last login
    updateAdminLastLogin($username);
    
    return $admin;
}

/**
 * Get all admins from files
 */
function getAllAdminsFromFiles() {
    if (!ensureAdminDataDir()) {
        return [];
    }
    
    $adminDir = getAdminDataDir();
    $admins = [];
    
    $files = glob($adminDir . '/*.json');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        if ($content !== false) {
            $admin = json_decode($content, true);
            if ($admin !== null) {
                // Remove password from returned data for security
                unset($admin['password']);
                $admins[] = $admin;
            }
        }
    }
    
    return $admins;
}
?>