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

/**
 * Calculate overdue days and fine amount
 * @param string $dueDate Format 'YYYY-MM-DD'
 * @param string|null $returnDate Format 'YYYY-MM-DD' or null if still issued
 * @return array ['is_overdue' => bool, 'days' => int, 'fine' => float, 'fine_formatted' => string]
 */
function calculate_overdue_and_fine($dueDate, $returnDate = null) {
    $fineRate = defined('FINE_RATE_PER_DAY') ? FINE_RATE_PER_DAY : 1.00;
    
    $dueTimestamp = strtotime($dueDate);
    $endTimestamp = $returnDate ? strtotime($returnDate) : strtotime(date('Y-m-d'));
    
    if ($endTimestamp > $dueTimestamp) {
        $daysOverdue = (int)round(($endTimestamp - $dueTimestamp) / 86400);
        $fine = $daysOverdue * $fineRate;
        return [
            'is_overdue' => true,
            'days' => $daysOverdue,
            'fine' => $fine,
            'fine_formatted' => '$' . number_format($fine, 2)
        ];
    }
    
    return [
        'is_overdue' => false,
        'days' => 0,
        'fine' => 0.00,
        'fine_formatted' => '$0.00'
    ];
}

/**
 * Return a high-quality professional profile picture URL for a user
 * @param string $name User's full name
 * @param string $email User's email
 * @return string Image URL
 */
function get_user_avatar($name, $email = '') {
    $email = strtolower(trim($email));
    
    // Curated high-resolution professional student portraits
    $portraits = [
        'student@library.com'          => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=256&h=256&q=80',
        'emma.watson@college.edu'      => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=256&h=256&q=80',
        'liam.davis@college.edu'       => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=256&h=256&q=80',
        'sophia.miller@college.edu'    => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=256&h=256&q=80',
        'noah.wilson@college.edu'      => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=256&h=256&q=80',
        'olivia.taylor@college.edu'    => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=256&h=256&q=80',
        'ethan.anderson@college.edu'   => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=256&h=256&q=80',
        'ava.thomas@college.edu'       => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=256&h=256&q=80',
        'lucas.martinez@college.edu'   => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=256&h=256&q=80',
        'mia.garcia@college.edu'       => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=256&h=256&q=80',
        'benjamin.r@college.edu'       => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=256&h=256&q=80',
        'charlotte.c@college.edu'      => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=256&h=256&q=80',
        'henry.rodriguez@college.edu'  => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=256&h=256&q=80',
        'amelia.lewis@college.edu'     => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=256&h=256&q=80',
        'daniel.lee@college.edu'       => 'https://images.unsplash.com/photo-1501196354995-cbb51c65aaea?auto=format&fit=crop&w=256&h=256&q=80',
        'harper.walker@college.edu'    => 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?auto=format&fit=crop&w=256&h=256&q=80',
        'matthew.hall@college.edu'     => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=256&h=256&q=80',
        'evelyn.allen@college.edu'     => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=256&h=256&q=80',
        'james.young@college.edu'      => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=256&h=256&q=80',
        'isabella.king@college.edu'    => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&w=256&h=256&q=80',
        'mason.wright@college.edu'     => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=256&h=256&q=80',
        'admin@library.com'            => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=256&h=256&q=80'
    ];

    if (isset($portraits[$email])) {
        return $portraits[$email];
    }

    $encodedName = urlencode($name ?: 'Student');
    return "https://ui-avatars.com/api/?name={$encodedName}&background=2d6a4f&color=ffffff&size=256&font-size=0.4&bold=true";
}
