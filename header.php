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
                <i class="bi bi-book-half text-info fs-3"></i>
                <div class="d-inline-block lh-1 text-start">
                    <span class="d-block fw-bold tracking-wide">LIBRARY MANAGEMENT</span>
                    <small class="text-white-50" style="font-size: 0.72rem; letter-spacing: 0.5px;">COLLEGE DIGITAL PORTAL</small>
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
                            <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-2 px-3 py-1 rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-5"></i>
                                <span><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                                <span class="badge <?php echo is_admin() ? 'bg-danger' : 'bg-info text-dark'; ?> text-uppercase" style="font-size: 0.68rem;">
                                    <?php echo htmlspecialchars($_SESSION['role']); ?>
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                <li>
                                    <div class="dropdown-header text-muted small">
                                        Signed in as<br>
                                        <strong class="text-dark"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></strong>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if (is_student()): ?>
                                    <li><a class="dropdown-item" href="student_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>My Dashboard</a></li>
                                <?php elseif (is_admin()): ?>
                                    <li><a class="dropdown-item" href="admin_dashboard.php"><i class="bi bi-sliders me-2"></i>Admin Dashboard</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php?role=student" class="btn btn-outline-light btn-sm px-3 rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Student Login
                        </a>
                        <a href="login.php?role=admin" class="btn btn-light btn-sm text-primary px-3 fw-bold rounded-pill">
                            <i class="bi bi-shield-lock me-1"></i> Admin Login
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
