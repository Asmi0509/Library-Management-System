<?php
/**
 * Authentication and Session Management Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is currently logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the currently logged in user is an administrator
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if the currently logged in user is a student
 */
function is_student() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

/**
 * Enforce authentication: redirect to login if not authenticated
 */
function require_login($redirect_url = 'login.php') {
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to access this page.');
        header("Location: $redirect_url");
        exit;
    }
}

/**
 * Enforce admin role: redirect if not admin
 */
function require_admin($redirect_url = 'login.php') {
    require_login($redirect_url);
    if (!is_admin()) {
        set_flash('danger', 'Access denied. Administrator privileges are required.');
        header("Location: student_dashboard.php");
        exit;
    }
}

/**
 * Enforce student role: redirect if not student
 */
function require_student($redirect_url = 'login.php') {
    require_login($redirect_url);
    if (!is_student()) {
        set_flash('danger', 'Access restricted to student accounts.');
        header("Location: admin_dashboard.php");
        exit;
    }
}

/**
 * Set a flash message for display on the next page view
 * $type: 'success', 'danger', 'warning', 'info'
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Retrieve and clear the flash message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render flash message as a Bootstrap alert component
 */
function display_flash() {
    $flash = get_flash();
    if ($flash) {
        $type = htmlspecialchars($flash['type']);
        $message = htmlspecialchars($flash['message']);
        $icon = 'bi-info-circle-fill';
        if ($type === 'success') $icon = 'bi-check-circle-fill';
        if ($type === 'danger')  $icon = 'bi-exclamation-triangle-fill';
        if ($type === 'warning') $icon = 'bi-exclamation-circle-fill';

        return "
        <div class=\"alert alert-{$type} alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4\" role=\"alert\">
            <i class=\"bi {$icon} me-2 fs-5\"></i>
            <div class=\"flex-grow-1\">{$message}</div>
            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
        </div>";
    }
    return '';
}
