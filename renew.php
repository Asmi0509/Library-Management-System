<?php
/**
 * Loan Renewal Controller
 * Software Engineering-I Academic Prototype
 * 
 * Rules:
 * 1. Authenticated students can renew their active loans.
 * 2. Administrators can renew any active loan.
 * 3. Extends the loan due date by RENEW_EXTEND_DAYS (e.g. 7 days).
 * 4. Can only be renewed if status is 'issued' and not currently overdue.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$userId = $_SESSION['user_id'];
$isAdmin = is_admin();
$redirectTo = $_POST['redirect_to'] ?? $_GET['redirect_to'] ?? ($isAdmin ? 'admin_dashboard.php' : 'student_dashboard.php');

if (!in_array($redirectTo, ['admin_dashboard.php', 'student_dashboard.php', 'catalogue.php'])) {
    $redirectTo = $isAdmin ? 'admin_dashboard.php' : 'student_dashboard.php';
}

$transactionId = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : (isset($_GET['transaction_id']) ? (int)$_GET['transaction_id'] : 0);

if ($transactionId <= 0) {
    set_flash('danger', 'Invalid transaction identifier provided.');
    header("Location: $redirectTo");
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT t.*, b.title 
        FROM transactions t
        JOIN books b ON t.book_id = b.book_id
        WHERE t.transaction_id = ?
    ");
    $stmt->execute([$transactionId]);
    $tx = $stmt->fetch();

    if (!$tx) {
        set_flash('danger', 'Borrowing transaction record not found.');
        header("Location: $redirectTo");
        exit;
    }

    if (!$isAdmin && (int)$tx['user_id'] !== (int)$userId) {
        set_flash('danger', 'Unauthorized: You can only renew books borrowed under your own account.');
        header("Location: student_dashboard.php");
        exit;
    }

    if ($tx['status'] !== 'issued') {
        set_flash('warning', 'Only actively issued books can be renewed.');
        header("Location: $redirectTo");
        exit;
    }

    // Overdue check
    $fineInfo = calculate_overdue_and_fine($tx['due_date']);
    if ($fineInfo['is_overdue']) {
        set_flash('danger', 'Cannot renew an overdue book! Outstanding fine: ' . $fineInfo['fine_formatted'] . '. Please return the book at the library circulation desk.');
        header("Location: $redirectTo");
        exit;
    }

    $extendDays = defined('RENEW_EXTEND_DAYS') ? RENEW_EXTEND_DAYS : 7;
    $newDueDate = date('Y-m-d', strtotime($tx['due_date'] . " +$extendDays days"));

    $updateStmt = $pdo->prepare("UPDATE transactions SET due_date = ? WHERE transaction_id = ?");
    $updateStmt->execute([$newDueDate, $transactionId]);

    set_flash('success', 'Loan extended! "' . htmlspecialchars($tx['title']) . '" due date has been renewed by +' . $extendDays . ' days (New Due Date: ' . date('M d, Y', strtotime($newDueDate)) . ').');
    header("Location: $redirectTo");
    exit;

} catch (Exception $e) {
    set_flash('danger', 'An error occurred while renewing the book loan.');
    header("Location: $redirectTo");
    exit;
}
