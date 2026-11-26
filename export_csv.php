<?php
/**
 * Export Transactions Report (CSV)
 * Software Engineering-I Academic Prototype
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

require_admin();

$filter = $_GET['type'] ?? 'all';

if ($filter === 'issued') {
    $sql = "
        SELECT t.transaction_id, u.name AS student_name, u.email AS student_email,
               b.book_id, b.title AS book_title, b.category,
               t.issue_date, t.due_date, t.return_date, t.status
        FROM transactions t
        JOIN books b ON t.book_id = b.book_id
        JOIN users u ON t.user_id = u.user_id
        WHERE t.status = 'issued'
        ORDER BY t.issue_date DESC
    ";
    $filename = 'library_active_loans_' . date('Y-m-d') . '.csv';
} else {
    $sql = "
        SELECT t.transaction_id, u.name AS student_name, u.email AS student_email,
               b.book_id, b.title AS book_title, b.category,
               t.issue_date, t.due_date, t.return_date, t.status
        FROM transactions t
        JOIN books b ON t.book_id = b.book_id
        JOIN users u ON t.user_id = u.user_id
        ORDER BY t.transaction_id DESC
    ";
    $filename = 'library_all_transactions_' . date('Y-m-d') . '.csv';
}

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, [
    'Transaction ID',
    'Student Name',
    'Student Email',
    'Book ID',
    'Book Title',
    'Category',
    'Issue Date',
    'Due Date',
    'Return Date',
    'Status',
    'Days Overdue',
    'Fine Amount ($)'
]);

foreach ($rows as $r) {
    $fineInfo = calculate_overdue_and_fine($r['due_date'], $r['return_date']);
    fputcsv($output, [
        '#TX-' . $r['transaction_id'],
        $r['student_name'],
        $r['student_email'],
        $r['book_id'],
        $r['book_title'],
        $r['category'],
        $r['issue_date'],
        $r['due_date'],
        $r['return_date'] ?: 'Active / Not Returned',
        strtoupper($r['status']),
        $fineInfo['days'],
        number_format($fineInfo['fine'], 2)
    ]);
}

fclose($output);
exit;
