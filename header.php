<?php
/**
 * Common Header and Navigation Bar
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth.php';

// Determine active page name for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = isset($page_title) ? $page_title : 'Library Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - LMS</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Application CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="brand-icon-wrapper me-1">
                    <i class="bi bi-tree-fill text-success-light fs-3"></i>
                </div>
                <div class="d-inline-block lh-1 text-start">
                    <span class="d-block fw-bold tracking-wide">ECO-LIBRARY</span>
                    <small class="text-white-50" style="font-size: 0.72rem; letter-spacing: 0.6px;">COLLEGE DIGITAL PORTAL</small>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-house-door me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'catalogue.php') ? 'active' : ''; ?>" href="catalogue.php">
                            <i class="bi bi-journals me-1"></i> Book Catalogue
                        </a>
                    </li>
                    
                    <?php if (is_student()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page === 'student_dashboard.php') ? 'active' : ''; ?>" href="student_dashboard.php">
                                <i class="bi bi-person-workspace me-1"></i> Student Dashboard
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page === 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
                                <i class="bi bi-shield-lock me-1"></i> Admin Portal
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <?php if (is_logged_in()): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-2 px-3 py-1 rounded-pill user-dropdown-btn shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo get_user_avatar($_SESSION['name'] ?? '', $_SESSION['email'] ?? ''); ?>" 
                                     alt="Profile Avatar" 
                                     class="rounded-circle border border-2 border-success-subtle header-avatar" 
                                     style="width: 32px; height: 32px; object-fit: cover;">
                                <span class="fw-semibold text-white"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                                <span class="badge <?php echo is_admin() ? 'bg-danger' : 'bg-success text-white'; ?> text-uppercase px-2 py-1 rounded-pill" style="font-size: 0.65rem;">
                                    <?php echo htmlspecialchars($_SESSION['role']); ?>
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 rounded-3">
                                <li>
                                    <div class="dropdown-header text-muted small p-3 bg-light rounded-top-3">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <img src="<?php echo get_user_avatar($_SESSION['name'] ?? '', $_SESSION['email'] ?? ''); ?>" 
                                                 alt="Avatar" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <?php if (is_student()): ?>
                                    <li><a class="dropdown-item py-2" href="student_dashboard.php"><i class="bi bi-speedometer2 me-2 text-success"></i>My Student Portal</a></li>
                                <?php elseif (is_admin()): ?>
                                    <li><a class="dropdown-item py-2" href="admin_dashboard.php"><i class="bi bi-sliders me-2 text-primary"></i>Admin Dashboard</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php?role=student" class="btn btn-outline-light btn-sm px-3 rounded-pill fw-medium">
                            <i class="bi bi-person-circle me-1"></i> Student Login
                        </a>
                        <a href="login.php?role=admin" class="btn btn-emerald-light btn-sm px-3 fw-bold rounded-pill shadow-sm">
                            <i class="bi bi-shield-check me-1"></i> Admin Portal
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container Wrapper -->
    <main class="py-4">
        <div class="container">
            <!-- Flash notification messages rendered automatically -->
            <?php echo display_flash(); ?>
