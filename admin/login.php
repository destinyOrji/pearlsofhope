<?php
/**
 * Admin Login Page
 * Handles administrator authentication
 */

// Configure session settings before any output
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin_fallback.php';

// Define authentication functions locally to avoid double inclusion
if (!function_exists('isAuthenticated')) {
    function isAuthenticated() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return trim(stripslashes($data));
    }
}

if (!function_exists('sanitizeOutput')) {
    function sanitizeOutput($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('objectIdToString')) {
    function objectIdToString($objectId) {
        return (string) $objectId;
    }
}

// Redirect if already logged in
if (isAuthenticated()) {
    header('Location: index.php');
    exit();
}

$error = '';
$timeout_message = '';
$logout_message = '';

// Check for timeout parameter
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $timeout_message = 'Your session has expired due to inactivity. Please log in again.';
}

// Check for logout parameter
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $logout_message = 'You have been successfully logged out.';
}

// Handle login form submission
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Try MongoDB first
            $collection = getCollection('administrators');
            $admin = null;
            $authSuccess = false;
            
            if ($collection) {
                // Find administrator by username in MongoDB
                $admin = findOneDocument($collection, ['username' => $username]);
                
                if ($admin && password_verify($password, $admin['password'])) {
                    $authSuccess = true;
                    error_log("Admin authentication successful via MongoDB");
                }
            }
            
            // If MongoDB failed, try file fallback
            if (!$authSuccess) {
                $admin = authenticateAdminFallback($username, $password);
                if ($admin) {
                    $authSuccess = true;
                    error_log("Admin authentication successful via file fallback");
                }
            }
            
            if ($authSuccess && $admin) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = isset($admin['_id']) ? objectIdToString($admin['_id']) : $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['last_activity'] = time();
                
                // Use absolute URL for redirect to avoid issues
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $path = dirname($_SERVER['REQUEST_URI']);
                $redirectUrl = $protocol . '://' . $host . $path . '/index.php';
                
                header('Location: ' . $redirectUrl);
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            
            // Try file fallback on exception
            try {
                $admin = authenticateAdminFallback($username, $password);
                if ($admin) {
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Set session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['last_activity'] = time();
                    
                    // Use absolute URL for redirect to avoid issues
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $path = dirname($_SERVER['REQUEST_URI']);
                    $redirectUrl = $protocol . '://' . $host . $path . '/index.php';
                    
                    header('Location: ' . $redirectUrl);
                    exit();
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (Exception $fallbackException) {
                error_log("Fallback login error: " . $fallbackException->getMessage());
                $error = 'An error occurred during login. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h2 {
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: #6c757d;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="login-header">
                        <h2>Admin Login</h2>
                        <p><?php echo SITE_NAME; ?></p>
                    </div>
                    
                    <?php if (!empty($logout_message)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo sanitizeOutput($logout_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($timeout_message)): ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-clock"></i> <?php echo sanitizeOutput($timeout_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo sanitizeOutput($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   value="<?php echo isset($_POST['username']) ? sanitizeOutput($_POST['username']) : ''; ?>"
                                   required 
                                   autocomplete="username"
                                   autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   autocomplete="current-password">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="../public/index.php" class="text-muted text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Back to Website
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>