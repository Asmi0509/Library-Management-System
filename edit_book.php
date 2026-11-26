<?php
/**
 * Edit Book Controller & Page
 * Software Engineering-I Academic Prototype
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Enforce admin privileges
require_admin();

$bookId = isset($_POST['book_id']) ? (int)$_POST['book_id'] : (isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0);

if ($bookId <= 0) {
    set_flash('danger', 'Invalid book selection.');
    header('Location: admin_dashboard.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM books WHERE book_id = ? LIMIT 1');
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    set_flash('danger', 'Book not found in catalogue.');
    header('Location: admin_dashboard.php');
    exit;
}

$issuedStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE book_id = ? AND status = 'issued'");
$issuedStmt->execute([$bookId]);
$issuedCount = (int)$issuedStmt->fetchColumn();

$error = '';
$title = $book['title'];
$author = $book['author'];
$category = $book['category'];
$quantity = (int)$book['quantity'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    if ($title === '') {
        $error = 'Book title is required.';
    } elseif ($author === '') {
        $error = 'Author name is required.';
    } elseif ($category === '') {
        $error = 'Category / Subject is required.';
    } elseif ($quantity < 1) {
        $error = 'Total quantity must be at least 1 copy.';
    } else {
        try {
            $pdo->beginTransaction();

            $lockedBookStmt = $pdo->prepare('SELECT * FROM books WHERE book_id = ? FOR UPDATE');
            $lockedBookStmt->execute([$bookId]);
            $lockedBook = $lockedBookStmt->fetch();

            if (!$lockedBook) {
                $pdo->rollBack();
                set_flash('danger', 'Book not found in catalogue.');
                header('Location: admin_dashboard.php');
                exit;
            }

            $lockedIssuedStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE book_id = ? AND status = 'issued'");
            $lockedIssuedStmt->execute([$bookId]);
            $issuedCount = (int)$lockedIssuedStmt->fetchColumn();

            if ($quantity < $issuedCount) {
                $pdo->rollBack();
                $error = "Total quantity cannot be less than currently issued copies ({$issuedCount}).";
            } else {
                $newAvailable = $quantity - $issuedCount;
                $updateStmt = $pdo->prepare('
                    UPDATE books
                    SET title = ?, author = ?, category = ?, quantity = ?, available_quantity = ?
                    WHERE book_id = ?
                ');
                $updateStmt->execute([$title, $author, $category, $quantity, $newAvailable, $bookId]);
                $pdo->commit();

                set_flash('success', "Book updated successfully! \"{$title}\" (ID #{$bookId}) now has {$newAvailable} of {$quantity} copy(ies) available.");
                header('Location: admin_dashboard.php');
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$page_title = 'Edit Book - Admin Portal';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom shadow-sm border-0 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Book #<?php echo (int)$bookId; ?>
                </h5>
                <a href="admin_dashboard.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="edit_book.php" novalidate>
                    <input type="hidden" name="book_id" value="<?php echo (int)$bookId; ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Book ID</label>
                            <input type="text" class="form-control bg-light" value="#<?php echo (int)$bookId; ?>" disabled>
                        </div>

                        <div class="col-md-8">
                            <label for="category" class="form-label fw-semibold">Category / Field <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="category" name="category"
                                   required value="<?php echo htmlspecialchars($category); ?>">
                        </div>

                        <div class="col-12">
                            <label for="title" class="form-label fw-semibold">Book Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title"
                                   required value="<?php echo htmlspecialchars($title); ?>">
                        </div>

                        <div class="col-md-8">
                            <label for="author" class="form-label fw-semibold">Author(s) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="author" name="author"
                                   required value="<?php echo htmlspecialchars($author); ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="quantity" class="form-label fw-semibold">Total Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity"
                                   required min="<?php echo max(1, $issuedCount); ?>" max="1000" value="<?php echo (int)$quantity; ?>">
                            <div class="form-text small">Currently issued: <?php echo $issuedCount; ?></div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="admin_dashboard.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-custom px-4 shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
