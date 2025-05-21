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

// Check if book_id is provided in URL for direct borrowing
$selected_book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
$book = null;

if ($selected_book_id > 0) {
    // Get book details
    $book = $db->getRow("SELECT * FROM books WHERE id = ? AND available_copies > 0", [$selected_book_id]);
    
    if (!$book) {
        setSessionMessage('The selected book is not available for borrowing.', 'error');
        redirect(APP_URL . '/user/search.php');
    }
}

// Process borrow form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
    
    if ($book_id <= 0) {
        setSessionMessage('Please select a valid book to borrow.', 'error');
    } else {
        // Check if the book is available
        $book = $db->getRow("SELECT * FROM books WHERE id = ? AND available_copies > 0", [$book_id]);
        
        if (!$book) {
            setSessionMessage('The selected book is not available for borrowing.', 'error');
        } else {
            // Check if user already has this book borrowed
            $alreadyBorrowed = $db->getRow("
                SELECT id FROM borrowed_books 
                WHERE user_id = ? AND book_id = ? AND return_date IS NULL
            ", [$user_id, $book_id]);
            
            if ($alreadyBorrowed) {
                setSessionMessage('You have already borrowed this book and have not returned it yet.', 'error');
            } else {
                // Begin transaction
                $conn->beginTransaction();
                
                try {
                    // Insert borrow record
                    $borrowQuery = "
                        INSERT INTO borrowed_books (user_id, book_id, borrow_date) 
                        VALUES (?, ?, CURRENT_TIMESTAMP)
                    ";
                    $db->query($borrowQuery, [$user_id, $book_id]);
                    
                    // Update book available copies
                    $updateQuery = "
                        UPDATE books 
                        SET available_copies = available_copies - 1 
                        WHERE id = ? AND available_copies > 0
                    ";
                    $db->query($updateQuery, [$book_id]);
                    
                    // Commit transaction
                    $conn->commit();
                    
                    setSessionMessage('Book borrowed successfully!');
                    redirect(APP_URL . '/user/dashboard.php');
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollBack();
                    setSessionMessage('Failed to borrow book. Please try again.', 'error');
                }
            }
        }
    }
}

// Get all available books for the dropdown
$availableBooks = $db->getAll("SELECT * FROM books WHERE available_copies > 0 ORDER BY book_name");

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1><i class="fas fa-book"></i> Borrow Books</h1>
        <hr>
    </div>
</div>

<?php if ($book): ?>
<!-- Direct borrowing for a specific book -->
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-book"></i> Borrow Book</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="alert alert-info">
                        <p><strong>You are about to borrow:</strong></p>
                        <p>
                            <strong>Book ID:</strong> <?php echo htmlspecialchars($book['book_id']); ?><br>
                            <strong>Book Name:</strong> <?php echo htmlspecialchars($book['book_name']); ?><br>
                            <strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?><br>
                            <strong>Available Copies:</strong> <?php echo $book['available_copies']; ?> of <?php echo $book['total_copies']; ?>
                        </p>
                    </div>
                    
                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo APP_URL; ?>/user/search.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirm Borrow
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- General borrowing page -->
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-book"></i> Borrow a Book</h5>
            </div>
            <div class="card-body">
                <?php if (empty($availableBooks)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> There are no books available for borrowing at the moment.
                    </div>
                    <a href="<?php echo APP_URL; ?>/user/search.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Books
                    </a>
                <?php else: ?>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="book_id" class="form-label">Select Book</label>
                            <select class="form-select" id="book_id" name="book_id" required>
                                <option value="">-- Select a book --</option>
                                <?php foreach ($availableBooks as $availableBook): ?>
                                    <option value="<?php echo $availableBook['id']; ?>">
                                        <?php echo htmlspecialchars($availableBook['book_name']); ?> 
                                        by <?php echo htmlspecialchars($availableBook['author']); ?>
                                        (<?php echo $availableBook['available_copies']; ?> available)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-book"></i> Borrow Book
                            </button>
                            <a href="<?php echo APP_URL; ?>/user/search.php" class="btn btn-secondary">
                                <i class="fas fa-search"></i> Search Books
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Currently Borrowed Books -->
        <?php
        $borrowedBooks = $db->getAll("
            SELECT b.id, b.borrow_date, bk.book_id as book_code, bk.book_name, bk.author 
            FROM borrowed_books b
            JOIN books bk ON b.book_id = bk.id
            WHERE b.user_id = ? AND b.return_date IS NULL
            ORDER BY b.borrow_date DESC
        ", [$user_id]);
        ?>
        
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5><i class="fas fa-book"></i> Your Currently Borrowed Books</h5>
            </div>
            <div class="card-body">
                <?php if (empty($borrowedBooks)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You don't have any borrowed books currently.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Book Code</th>
                                    <th>Book Name</th>
                                    <th>Author</th>
                                    <th>Borrowed Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowedBooks as $borrowedBook): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($borrowedBook['book_code']); ?></td>
                                        <td><?php echo htmlspecialchars($borrowedBook['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($borrowedBook['author']); ?></td>
                                        <td><?php echo formatDate($borrowedBook['borrow_date']); ?></td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/user/return.php?id=<?php echo $borrowedBook['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-undo"></i> Return
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?php echo APP_URL; ?>/user/return.php" class="btn btn-success">
                        <i class="fas fa-undo"></i> Return Books
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_once '../includes/footer.php'; ?>
