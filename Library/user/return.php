<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Require user login (non-admin)
requireUser();

$db = Database::getInstance();
$conn = $db->getConnection();

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Check if borrow_id is provided in URL for direct returning
$borrow_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$borrowedBook = null;

if ($borrow_id > 0) {
    // Get borrowed book details
    $borrowedBook = $db->getRow("
        SELECT b.*, bk.book_name, bk.author, bk.book_id as book_code
        FROM borrowed_books b
        JOIN books bk ON b.book_id = bk.id
        WHERE b.id = ? AND b.user_id = ? AND b.return_date IS NULL
    ", [$borrow_id, $user_id]);
    
    if (!$borrowedBook) {
        setSessionMessage('The selected borrowed book record was not found or has already been returned.', 'error');
        redirect(APP_URL . '/user/dashboard.php');
    }
}

// Process return form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $return_borrow_id = isset($_POST['borrow_id']) ? (int)$_POST['borrow_id'] : 0;
    
    if ($return_borrow_id <= 0) {
        setSessionMessage('Please select a valid book to return.', 'error');
    } else {
        // Check if the borrow record exists and belongs to this user
        $borrowRecord = $db->getRow("
            SELECT * FROM borrowed_books 
            WHERE id = ? AND user_id = ? AND return_date IS NULL
        ", [$return_borrow_id, $user_id]);
        
        if (!$borrowRecord) {
            setSessionMessage('The selected borrowed book record was not found or has already been returned.', 'error');
        } else {
            // Begin transaction
            $conn->beginTransaction();
            
            try {
                // Update borrow record with return date
                $returnQuery = "
                    UPDATE borrowed_books 
                    SET return_date = CURRENT_TIMESTAMP 
                    WHERE id = ? AND return_date IS NULL
                ";
                $db->query($returnQuery, [$return_borrow_id]);
                
                // Update book available copies
                $updateQuery = "
                    UPDATE books 
                    SET available_copies = available_copies + 1 
                    WHERE id = ?
                ";
                $db->query($updateQuery, [$borrowRecord['book_id']]);
                
                // Commit transaction
                $conn->commit();
                
                setSessionMessage('Book returned successfully!');
                redirect(APP_URL . '/user/dashboard.php');
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollBack();
                setSessionMessage('Failed to return book. Please try again.', 'error');
            }
        }
    }
}

// Get all borrowed books by user that haven't been returned yet
$borrowedBooks = $db->getAll("
    SELECT b.id, b.borrow_date, bk.id as book_id, bk.book_id as book_code, bk.book_name, bk.author 
    FROM borrowed_books b
    JOIN books bk ON b.book_id = bk.id
    WHERE b.user_id = ? AND b.return_date IS NULL
    ORDER BY b.borrow_date DESC
", [$user_id]);

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1><i class="fas fa-undo"></i> Return Books</h1>
        <hr>
    </div>
</div>

<?php if ($borrowedBook): ?>
<!-- Direct returning for a specific book -->
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-undo"></i> Return Book</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="alert alert-info">
                        <p><strong>You are about to return:</strong></p>
                        <p>
                            <strong>Book ID:</strong> <?php echo htmlspecialchars($borrowedBook['book_code']); ?><br>
                            <strong>Book Name:</strong> <?php echo htmlspecialchars($borrowedBook['book_name']); ?><br>
                            <strong>Author:</strong> <?php echo htmlspecialchars($borrowedBook['author']); ?><br>
                            <strong>Borrowed Date:</strong> <?php echo formatDate($borrowedBook['borrow_date']); ?><br>
                            <strong>Duration:</strong> <?php echo daysBetween($borrowedBook['borrow_date']); ?> days
                        </p>
                    </div>
                    
                    <input type="hidden" name="borrow_id" value="<?php echo $borrowedBook['id']; ?>">
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo APP_URL; ?>/user/dashboard.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Confirm Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- General return page -->
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-undo"></i> Return a Book</h5>
            </div>
            <div class="card-body">
                <?php if (empty($borrowedBooks)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You don't have any borrowed books to return.
                    </div>
                    <a href="<?php echo APP_URL; ?>/user/borrow.php" class="btn btn-primary">
                        <i class="fas fa-book"></i> Borrow Books
                    </a>
                <?php else: ?>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="borrow_id" class="form-label">Select Book to Return</label>
                            <select class="form-select" id="borrow_id" name="borrow_id" required>
                                <option value="">-- Select a borrowed book --</option>
                                <?php foreach ($borrowedBooks as $book): ?>
                                    <option value="<?php echo $book['id']; ?>">
                                        <?php echo htmlspecialchars($book['book_name']); ?> 
                                        by <?php echo htmlspecialchars($book['author']); ?>
                                        (Borrowed on <?php echo formatDate($book['borrow_date']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-undo"></i> Return Book
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Borrowing History -->
        <?php
        $borrowingHistory = $db->getAll("
            SELECT b.id, b.borrow_date, b.return_date, bk.book_id as book_code, bk.book_name, bk.author 
            FROM borrowed_books b
            JOIN books bk ON b.book_id = bk.id
            WHERE b.user_id = ? AND b.return_date IS NOT NULL
            ORDER BY b.return_date DESC
            LIMIT 10
        ", [$user_id]);
        ?>
        
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5><i class="fas fa-history"></i> Your Return History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($borrowingHistory)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You don't have any return history yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Book ID</th>
                                    <th>Book Name</th>
                                    <th>Author</th>
                                    <th>Borrowed Date</th>
                                    <th>Returned Date</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowingHistory as $history): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($history['book_code']); ?></td>
                                        <td><?php echo htmlspecialchars($history['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($history['author']); ?></td>
                                        <td><?php echo formatDate($history['borrow_date']); ?></td>
                                        <td><?php echo formatDate($history['return_date']); ?></td>
                                        <td>
                                            <?php 
                                            $days = daysBetween($history['borrow_date'], $history['return_date']);
                                            echo $days . ' ' . ($days == 1 ? 'day' : 'days'); 
                                            ?>
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
</div>
<?php endif; ?>

<?php include_once '../includes/footer.php'; ?>
