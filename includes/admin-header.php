<?php
// Include functions for authentication (config.php will handle session start)
require_once __DIR__ . '/functions.php';

// Check authentication BEFORE any output
checkAuth();

// Get current page for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Get admin username for display
$admin_username = getCurrentAdminUsername();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin Panel' : 'Admin Panel'; ?> - NGO Website</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo isset($css_path) ? $css_path : '../public/assets/css/style.css'; ?>" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .admin-navbar {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }
        .admin-navbar .navbar-brand {
            color: #fff !important;
            font-weight: bold;
        }
        .admin-navbar .nav-link {
            color: #ecf0f1 !important;
            transition: color 0.3s ease;
        }
        .admin-navbar .nav-link:hover,
        .admin-navbar .nav-link.active {
            color: #3498db !important;
        }
        .admin-navbar .dropdown-menu {
            background-color: #34495e;
            border: none;
        }
        .admin-navbar .dropdown-item {
            color: #ecf0f1;
        }
        .admin-navbar .dropdown-item:hover {
            background-color: #2c3e50;
            color: #3498db;
        }
        .main-content {
            min-height: calc(100vh - 200px);
            padding-top: 2rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg admin-navbar shadow">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cog me-2"></i>Admin Panel
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" 
                    aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php' && $current_dir == 'admin') ? 'active' : ''; ?>" 
                           href="<?php echo ($current_dir == 'admin') ? 'index.php' : '../index.php'; ?>">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo ($current_dir == 'activities') ? 'active' : ''; ?>" 
                           href="#" id="activitiesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-newspaper me-1"></i>Activities
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="activitiesDropdown">
                            <li>
                                <a class="dropdown-item <?php echo ($current_page == 'list.php' && $current_dir == 'activities') ? 'active' : ''; ?>" 
                                   href="<?php echo ($current_dir == 'activities') ? 'list.php' : 'activities/list.php'; ?>">
                                    <i class="fas fa-list me-1"></i>View All
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo ($current_page == 'create.php' && $current_dir == 'activities') ? 'active' : ''; ?>" 
                                   href="<?php echo ($current_dir == 'activities') ? 'create.php' : 'activities/create.php'; ?>">
                                    <i class="fas fa-plus me-1"></i>Create New
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo ($current_dir == 'team') ? 'active' : ''; ?>" 
                           href="#" id="teamDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users me-1"></i>Team
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="teamDropdown">
                            <li>
                                <a class="dropdown-item <?php echo ($current_page == 'list.php' && $current_dir == 'team') ? 'active' : ''; ?>" 
                                   href="<?php echo ($current_dir == 'team') ? 'list.php' : 'team/list.php'; ?>">
                                    <i class="fas fa-list me-1"></i>View All
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo ($current_page == 'create.php' && $current_dir == 'team') ? 'active' : ''; ?>" 
                                   href="<?php echo ($current_dir == 'team') ? 'create.php' : 'team/create.php'; ?>">
                                    <i class="fas fa-plus me-1"></i>Add New Member
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo ($current_dir == 'messages') ? 'active' : ''; ?>" 
                           href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-envelope me-1"></i>Messages
                            <?php
                            // Get unread message count for badge
                            $unreadCount = 0;
                            try {
                                $messagesCollection = getCollection('contact_messages');
                                if ($messagesCollection) {
                                    $unreadCount = countDocuments($messagesCollection, ['status' => 'unread']);
                                }
                            } catch (Exception $e) {
                                // Ignore error for navigation
                            }
                            if ($unreadCount > 0):
                            ?>
                                <span class="badge bg-danger ms-1"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="messagesDropdown">
                            <li>
                                <a class="dropdown-item <?php echo ($current_page == 'list.php' && $current_dir == 'messages') ? 'active' : ''; ?>" 
                                   href="<?php echo ($current_dir == 'messages') ? 'list.php' : 'messages/list.php'; ?>">
                                    <i class="fas fa-list me-1"></i>All Messages
                                    <?php if ($unreadCount > 0): ?>
                                        <span class="badge bg-danger ms-1"><?php echo $unreadCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" 
                                   href="<?php echo ($current_dir == 'messages') ? 'list.php?status=unread' : 'messages/list.php?status=unread'; ?>">
                                    <i class="fas fa-envelope me-1"></i>Unread Messages
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo ($current_dir == 'pages') ? 'active' : ''; ?>" 
                           href="#" id="pagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-alt me-1"></i>Pages
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="pagesDropdown">
                            <li>
                                <a class="dropdown-item" 
                                   href="<?php echo ($current_dir == 'pages') ? 'edit.php?page=home' : 'pages/edit.php?page=home'; ?>">
                                    <i class="fas fa-home me-1"></i>Home Page
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" 
                                   href="<?php echo ($current_dir == 'pages') ? 'edit.php?page=about' : 'pages/edit.php?page=about'; ?>">
                                    <i class="fas fa-info-circle me-1"></i>About Page
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" 
                                   href="<?php echo ($current_dir == 'pages') ? 'edit.php?page=contact' : 'pages/edit.php?page=contact'; ?>">
                                    <i class="fas fa-envelope me-1"></i>Contact Page
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
                
                <!-- User Info and Logout -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo sanitizeOutput($admin_username ?: 'Administrator'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="../public/index.php" target="_blank">
                                    <i class="fas fa-external-link-alt me-1"></i>View Website
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo ($current_dir == 'admin') ? 'logout.php' : '../logout.php'; ?>">
                                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php
    $flash = getFlashMessage();
    if ($flash):
        $alertClass = '';
        switch ($flash['type']) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
            default:
                $alertClass = 'alert-info';
        }
    ?>
    <div class="container-fluid mt-3">
        <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
            <?php echo sanitizeOutput($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="main-content">
        <div class="container-fluid">