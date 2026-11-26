<?php
/**
 * Student Dashboard
 * Software Engineering-I Academic Prototype
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Enforce student role
require_student();

$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'];

// 1. Fetch Summary Stats
// Total books available in library
$totalAvailableInLibrary = (int)$pdo->query("SELECT SUM(available_quantity) FROM books")->fetchColumn() ?: 0;

// Books currently borrowed by this student
$activeBorrowedCountStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND status = 'issued'");
$activeBorrowedCountStmt->execute([$userId]);
$currentlyBorrowedCount = (int)$activeBorrowedCountStmt->fetchColumn() ?: 0;

$maxBorrowLimit = defined('MAX_BORROW_LIMIT') ? MAX_BORROW_LIMIT : 3;

// Total borrowing history for this student
$totalHistoryCountStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND status = 'returned'");
$totalHistoryCountStmt->execute([$userId]);
$historyCount = (int)$totalHistoryCountStmt->fetchColumn() ?: 0;

// 2. Fetch Student's Currently Borrowed Books
$activeLoansStmt = $pdo->prepare("
    SELECT t.transaction_id, t.issue_date, t.due_date, t.status,
           b.book_id, b.title, b.author, b.category
    FROM transactions t
    JOIN books b ON t.book_id = b.book_id
    WHERE t.user_id = ? AND t.status = 'issued'
    ORDER BY t.issue_date DESC
");
$activeLoansStmt->execute([$userId]);
$activeLoans = $activeLoansStmt->fetchAll();

// Calculate overdue and fine metrics
$totalPendingFine = 0.00;
$overdueLoansCount = 0;
foreach ($activeLoans as &$loan) {
    $fineInfo = calculate_overdue_and_fine($loan['due_date']);
    $loan['fine_info'] = $fineInfo;
    if ($fineInfo['is_overdue']) {
        $totalPendingFine += $fineInfo['fine'];
        $overdueLoansCount++;
    }
}
unset($loan);

// Build list of active book IDs for quick badge checks in the catalogue
$activeBookIds = array_column($activeLoans, 'book_id');

// 3. Fetch Student's Returned Borrowing History
$historyStmt = $pdo->prepare("
    SELECT t.transaction_id, t.issue_date, t.due_date, t.return_date, t.status,
           b.book_id, b.title, b.author, b.category
    FROM transactions t
    JOIN books b ON t.book_id = b.book_id
    WHERE t.user_id = ? AND t.status = 'returned'
    ORDER BY t.return_date DESC
");
$historyStmt->execute([$userId]);
$borrowingHistory = $historyStmt->fetchAll();

foreach ($borrowingHistory as &$hist) {
    $hist['fine_info'] = calculate_overdue_and_fine($hist['due_date'], $hist['return_date']);
}
unset($hist);

// 4. Handle Search for Book Catalogue
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$availableOnly = !empty($_GET['available_only']);

$catStmt = $pdo->query("SELECT DISTINCT category FROM books ORDER BY category ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

$catQuery = "SELECT * FROM books WHERE 1=1";
$params = [];

if (!empty($search)) {
    $catQuery .= " AND (title LIKE ? OR author LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $catQuery .= " AND category = ?";
    $params[] = $category;
}

if ($availableOnly) {
    $catQuery .= " AND available_quantity > 0";
}

$catQuery .= " ORDER BY book_id ASC";
$booksStmt = $pdo->prepare($catQuery);
$booksStmt->execute($params);
$books = $booksStmt->fetchAll();

$page_title = 'Student Dashboard - Library Management System';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Student Profile Banner -->
<div class="student-profile-banner mb-4">
    <div class="row align-items-center">
        <div class="col-auto">
            <div class="position-relative">
                <img src="<?php echo get_user_avatar($userName, $_SESSION['email'] ?? ''); ?>" 
                     alt="<?php echo htmlspecialchars($userName); ?>" 
                     class="student-avatar-lg">
                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-3 border-white rounded-circle" title="Active Student"></span>
            </div>
        </div>
        <div class="col">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">
                    Welcome, <?php echo htmlspecialchars($userName); ?>!
                </h3>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-semibold">
                    <i class="bi bi-check-circle-fill me-1"></i> Verified Student Member
                </span>
            </div>
            <p class="text-muted mb-2 small">
                <i class="bi bi-envelope-fill me-1 text-success"></i> <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?> &bull; 
                <i class="bi bi-mortarboard-fill me-1 text-success"></i> Computer Science & Engineering &bull; Class of 2027
            </p>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-white text-dark border px-3 py-1 rounded-pill">
                    <i class="bi bi-person-badge me-1 text-muted"></i> Student ID: #STU-<?php echo str_pad($userId, 4, '0', STR_PAD_LEFT); ?>
                </span>
                <span class="badge <?php echo ($currentlyBorrowedCount >= $maxBorrowLimit) ? 'bg-danger text-white' : 'bg-white text-success border'; ?> px-3 py-1 rounded-pill">
                    <i class="bi bi-bookmark-star-fill me-1"></i> Quota: <?php echo $currentlyBorrowedCount; ?> of <?php echo $maxBorrowLimit; ?> Books Borrowed
                </span>
                <?php if ($totalPendingFine > 0): ?>
                    <span class="badge bg-danger text-white px-3 py-1 rounded-pill">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Fines Due: $<?php echo number_format($totalPendingFine, 2); ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-white text-success border px-3 py-1 rounded-pill">
                        <i class="bi bi-shield-check me-1"></i> Account Good Standing ($0.00)
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Summary Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-book"></i>
            </div>
            <div>
                <div class="stat-value"><?php echo $totalAvailableInLibrary; ?></div>
                <div class="stat-label">Available in Library</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="bi bi-bookmark-check"></i>
            </div>
            <div>
                <div class="stat-value"><?php echo $currentlyBorrowedCount; ?> <span class="fs-6 text-muted">/ <?php echo $maxBorrowLimit; ?></span></div>
                <div class="stat-label">
                    Active Loans
                    <span class="badge <?php echo ($currentlyBorrowedCount >= $maxBorrowLimit) ? 'bg-danger' : 'bg-primary-subtle text-primary border'; ?> ms-1">
                        Quota <?php echo $currentlyBorrowedCount; ?>/<?php echo $maxBorrowLimit; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon <?php echo ($overdueLoansCount > 0) ? 'danger' : 'success'; ?>">
                <i class="bi <?php echo ($overdueLoansCount > 0) ? 'bi-exclamation-triangle-fill text-danger' : 'bi-check2-circle text-success'; ?>"></i>
            </div>
            <div>
                <div class="stat-value <?php echo ($overdueLoansCount > 0) ? 'text-danger' : 'text-success'; ?>"><?php echo $overdueLoansCount; ?></div>
                <div class="stat-label">
                    Overdue Books
                    <?php if ($overdueLoansCount > 0): ?>
                        <span class="badge bg-danger ms-1">Action Needed</span>
                    <?php else: ?>
                        <span class="badge bg-success-subtle text-success border ms-1">All On Time</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon <?php echo ($totalPendingFine > 0) ? 'danger' : 'success'; ?>">
                <i class="bi bi-currency-dollar <?php echo ($totalPendingFine > 0) ? 'text-danger' : 'text-success'; ?>"></i>
            </div>
            <div>
                <div class="stat-value <?php echo ($totalPendingFine > 0) ? 'text-danger' : 'text-success'; ?>">
                    $<?php echo number_format($totalPendingFine, 2); ?>
                </div>
                <div class="stat-label">Outstanding Fine</div>
            </div>
        </div>
    </div>
</div>

<!-- Nav Tabs for Student Sections -->
<ul class="nav nav-pills mb-4 gap-2" id="studentTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold rounded-pill px-4" id="catalogue-tab" data-bs-toggle="pill" data-bs-target="#catalogue-section" type="button" role="tab">
            <i class="bi bi-search me-1"></i> Browse & Borrow
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold rounded-pill px-4 position-relative" id="borrowed-tab" data-bs-toggle="pill" data-bs-target="#borrowed-section" type="button" role="tab">
            <i class="bi bi-journal-bookmark me-1"></i> My Borrowed Books
            <?php if ($currentlyBorrowedCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $currentlyBorrowedCount; ?>
                </span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold rounded-pill px-4" id="history-tab" data-bs-toggle="pill" data-bs-target="#history-section" type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i> Borrowing History
        </button>
    </li>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="studentTabContent">

    <!-- ================= TAB 1: BROWSE & BORROW ================= -->
    <div class="tab-pane fade show active" id="catalogue-section" role="tabpanel">
        
        <!-- Search Bar -->
        <div class="card card-custom p-3 mb-4 shadow-sm">
            <form method="GET" action="student_dashboard.php" class="row g-2 align-items-center">
                <div class="col-md-5 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="liveSearchInput" name="search" class="form-control border-start-0" 
                               placeholder="Search books by title or author..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-md-3 col-lg-3">
                    <select id="categoryFilter" name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 col-lg-2">
                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" name="available_only" value="1" id="availCheck" <?php echo $availableOnly ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <label class="form-check-label small fw-semibold text-secondary" for="availCheck">In-Stock Only</label>
                    </div>
                </div>

                <div class="col-md-2 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <?php if (!empty($search) || !empty($category) || $availableOnly): ?>
                        <a href="student_dashboard.php" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Catalogue Table -->
        <div class="card card-custom border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-book-half text-primary me-2"></i>Library Book Catalogue
                </h6>
                <span class="small text-muted">
                    Found <?php echo count($books); ?> books
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-custom mb-0 align-middle">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 80px;">ID</th>
                            <th scope="col">Book Title</th>
                            <th scope="col">Author</th>
                            <th scope="col">Category</th>
                            <th scope="col" class="text-center">Total</th>
                            <th scope="col" class="text-center">Available</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-end" style="width: 140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="catalogueTableBody">
                        <?php if (empty($books)): ?>
                            <tr class="no-results-row">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary"></i>
                                    No books match the given criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($books as $b): ?>
                                <?php
                                    $isAvail = $b['available_quantity'] > 0;
                                    $isAlreadyIssued = in_array($b['book_id'], $activeBookIds);
                                ?>
                                <tr>
                                    <td class="fw-bold text-secondary">#<?php echo htmlspecialchars($b['book_id']); ?></td>
                                    <td>
                                        <strong class="book-title text-dark"><?php echo htmlspecialchars($b['title']); ?></strong>
                                    </td>
                                    <td class="book-author text-muted"><?php echo htmlspecialchars($b['author']); ?></td>
                                    <td>
                                        <span class="book-category badge-category"><?php echo htmlspecialchars($b['category']); ?></span>
                                    </td>
                                    <td class="text-center fw-semibold"><?php echo (int)$b['quantity']; ?></td>
                                    <td class="text-center">
                                        <span class="fw-bold <?php echo $isAvail ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo (int)$b['available_quantity']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isAvail): ?>
                                            <span class="badge-available">
                                                <i class="bi bi-check-circle-fill"></i> Available (<?php echo $b['available_quantity']; ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-unavailable">
                                                <i class="bi bi-x-circle-fill"></i> Unavailable
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($isAlreadyIssued): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-2 px-3">
                                                <i class="bi bi-bookmark-check-fill me-1"></i> In My Loans
                                            </span>
                                        <?php elseif ($currentlyBorrowedCount >= $maxBorrowLimit): ?>
                                            <button class="btn btn-sm btn-outline-danger" disabled title="Quota of <?php echo $maxBorrowLimit; ?> books reached">
                                                <i class="bi bi-slash-circle me-1"></i> Quota Full (<?php echo $maxBorrowLimit; ?>/<?php echo $maxBorrowLimit; ?>)
                                            </button>
                                        <?php elseif ($isAvail): ?>
                                            <form method="POST" action="borrow.php" class="d-inline">
                                                <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($b['book_id']); ?>">
                                                <input type="hidden" name="redirect_to" value="student_dashboard.php">
                                                <button type="submit" class="btn btn-sm btn-primary-custom">
                                                    <i class="bi bi-plus-circle me-1"></i> Borrow
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="bi bi-dash-circle me-1"></i> Out of Stock
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ================= TAB 2: CURRENTLY BORROWED ================= -->
    <div class="tab-pane fade" id="borrowed-section" role="tabpanel">
        <div class="card card-custom border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-check text-success me-2"></i>Currently Borrowed Books
                </h6>
                <span class="badge bg-primary rounded-pill"><?php echo count($activeLoans); ?> Active Loan(s)</span>
            </div>

            <?php if (empty($activeLoans)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-journal-text fs-1 d-block mb-3 text-secondary"></i>
                    <h5 class="fw-bold text-dark">No Books Currently Borrowed</h5>
                    <p class="small mb-3">You do not have any books issued at the moment. Browse the catalogue to borrow reading material.</p>
                    <button class="btn btn-primary-custom btn-sm" onclick="bootstrap.Tab.getInstance(document.getElementById('catalogue-tab')).show();">
                        <i class="bi bi-search me-1"></i> Browse Catalogue
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom mb-0 align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Book Title</th>
                                <th scope="col">Author</th>
                                <th scope="col">Category</th>
                                <th scope="col">Issue Date</th>
                                <th scope="col">Due Date</th>
                                <th scope="col" class="text-center">Overdue / Fine</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-end" style="width: 220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeLoans as $loan): ?>
                                <?php
                                    $fine = $loan['fine_info'];
                                    $isOverdue = $fine['is_overdue'];
                                ?>
                                <tr>
                                    <td>
                                        <strong class="text-dark"><?php echo htmlspecialchars($loan['title']); ?></strong>
                                        <div class="small text-muted">ID: #<?php echo htmlspecialchars($loan['book_id']); ?></div>
                                    </td>
                                    <td class="text-muted"><?php echo htmlspecialchars($loan['author']); ?></td>
                                    <td>
                                        <span class="badge-category"><?php echo htmlspecialchars($loan['category']); ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($loan['issue_date'])); ?></td>
                                    <td>
                                        <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ''; ?>">
                                            <?php echo date('M d, Y', strtotime($loan['due_date'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isOverdue): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2 rounded-pill fw-bold">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                <?php echo $fine['days']; ?>d late (<?php echo $fine['fine_formatted']; ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 rounded-pill">
                                                <i class="bi bi-shield-check me-1"></i> $0.00 (On Time)
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isOverdue): ?>
                                            <span class="badge bg-danger text-white py-1 px-2 rounded-pill">
                                                <i class="bi bi-clock-history me-1"></i> Overdue
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-status-issued">
                                                <i class="bi bi-clock me-1"></i> Active
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <!-- Renew Button (extends due date by 7 days if not overdue) -->
                                            <?php if (!$isOverdue): ?>
                                                <form method="POST" action="renew.php" class="d-inline">
                                                    <input type="hidden" name="transaction_id" value="<?php echo htmlspecialchars($loan['transaction_id']); ?>">
                                                    <input type="hidden" name="redirect_to" value="student_dashboard.php">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Extend loan by +7 days">
                                                        <i class="bi bi-arrow-repeat me-1"></i> Renew (+7d)
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <!-- Return Button -->
                                            <form method="POST" action="return.php" class="d-inline">
                                                <input type="hidden" name="transaction_id" value="<?php echo htmlspecialchars($loan['transaction_id']); ?>">
                                                <input type="hidden" name="redirect_to" value="student_dashboard.php">
                                                <button type="submit" class="btn btn-sm btn-outline-navy">
                                                    <i class="bi bi-arrow-return-left me-1"></i> Return
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ================= TAB 3: BORROWING HISTORY ================= -->
    <div class="tab-pane fade" id="history-section" role="tabpanel">
        <div class="card card-custom border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>Past Borrowing History
                </h6>
                <span class="small text-muted"><?php echo count($borrowingHistory); ?> Records</span>
            </div>

            <?php if (empty($borrowingHistory)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-hourglass-split fs-1 d-block mb-3 text-secondary"></i>
                    <p class="mb-0">You have not completed any book returns yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom mb-0 align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Transaction ID</th>
                                <th scope="col">Book Title</th>
                                <th scope="col">Author</th>
                                <th scope="col">Issue Date</th>
                                <th scope="col">Due Date</th>
                                <th scope="col">Returned Date</th>
                                <th scope="col" class="text-center">Overdue Fine</th>
                                <th scope="col" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($borrowingHistory as $hist): ?>
                                <?php $fine = $hist['fine_info']; ?>
                                <tr>
                                    <td class="text-muted fw-bold">#TX-<?php echo htmlspecialchars($hist['transaction_id']); ?></td>
                                    <td>
                                        <strong class="text-dark"><?php echo htmlspecialchars($hist['title']); ?></strong>
                                    </td>
                                    <td class="text-muted"><?php echo htmlspecialchars($hist['author']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($hist['issue_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($hist['due_date'])); ?></td>
                                    <td class="text-success fw-semibold">
                                        <?php echo date('M d, Y', strtotime($hist['return_date'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($fine['is_overdue']): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2 rounded-pill small">
                                                Late (<?php echo $fine['days']; ?>d: <?php echo $fine['fine_formatted']; ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 rounded-pill small">
                                                $0.00 (On Time)
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-status-returned">
                                            <i class="bi bi-check2-circle me-1"></i> Returned
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
