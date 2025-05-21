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

// Get user information
$user = $db->getRow("SELECT * FROM users WHERE id = ?", [$user_id]);

// Get currently borrowed books
$borrowedBooks = $db->getAll("
    SELECT b.id, b.borrow_date, bk.id as book_id, bk.book_id as book_code, bk.book_name, bk.author 
    FROM borrowed_books b
    JOIN books bk ON b.book_id = bk.id
    WHERE b.user_id = ? AND b.return_date IS NULL
    ORDER BY b.borrow_date DESC
", [$user_id]);

// Get borrowing history
$borrowingHistory = $db->getAll("
    SELECT b.id, b.borrow_date, b.return_date, bk.book_id as book_code, bk.book_name, bk.author 
    FROM borrowed_books b
    JOIN books bk ON b.book_id = bk.id
    WHERE b.user_id = ? AND b.return_date IS NOT NULL
    ORDER BY b.return_date DESC
    LIMIT 10
", [$user_id]);

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1><i class="fas fa-tachometer-alt"></i> User Dashboard</h1>
        <hr>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-user"></i> User Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Joined:</strong> <?php echo formatDate($user['created_at']); ?></p>
                <div class="d-grid gap-2">
                    <a href="<?php echo APP_URL; ?>/user/search.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Books
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5><i class="fas fa-book"></i> Currently Borrowed Books</h5>
            </div>
            <div class="card-body">
                <?php if (empty($borrowedBooks)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You don't have any borrowed books currently.
                    </div>
                    <a href="<?php echo APP_URL; ?>/user/borrow.php" class="btn btn-primary">
                        <i class="fas fa-book"></i> Borrow Books
                    </a>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
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
                                <?php foreach ($borrowedBooks as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['book_code']); ?></td>
                                        <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo formatDate($book['borrow_date']); ?></td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/user/return.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-undo"></i> Return
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>/user/borrow.php" class="btn btn-primary">
                            <i class="fas fa-book"></i> Borrow More Books
                        </a>
                        <a href="<?php echo APP_URL; ?>/user/return.php" class="btn btn-success">
                            <i class="fas fa-undo"></i> Return Books
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5><i class="fas fa-history"></i> Borrowing History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($borrowingHistory)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You don't have any borrowing history yet.
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
                                    <th>Returned Date</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowingHistory as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['book_code']); ?></td>
                                        <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo formatDate($book['borrow_date']); ?></td>
                                        <td><?php echo formatDate($book['return_date']); ?></td>
                                        <td>
                                            <?php 
                                            $days = daysBetween($book['borrow_date'], $book['return_date']);
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

<?php include_once '../includes/footer.php'; ?>
