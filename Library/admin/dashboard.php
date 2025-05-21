<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Require admin login
requireAdmin();

$db = Database::getInstance();
$conn = $db->getConnection();

// Get stats for dashboard
$totalBooks = $db->getRow("SELECT SUM(total_copies) as total FROM books")['total'] ?? 0;
$availableBooks = $db->getRow("SELECT SUM(available_copies) as total FROM books")['total'] ?? 0;
$borrowedBooks = $totalBooks - $availableBooks;
$totalUsers = $db->getRow("SELECT COUNT(*) as total FROM users")['total'] ?? 0;
$activeLoans = $db->getRow("SELECT COUNT(*) as total FROM borrowed_books WHERE return_date IS NULL")['total'] ?? 0;

// Get recent books
$recentBooks = $db->getAll("SELECT * FROM books ORDER BY id DESC LIMIT 5");

// Get recent transactions
$recentTransactions = $db->getAll("
    SELECT b.id, b.borrow_date, b.return_date, u.name as user_name, bk.book_name 
    FROM borrowed_books b
    JOIN users u ON b.user_id = u.id
    JOIN books bk ON b.book_id = bk.id
    ORDER BY b.borrow_date DESC
    LIMIT 10
");

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <hr>
    </div>
</div>

<div class="row mb-4">
    <div class="dashboard-stats">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card text-white card-primary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-book me-2"></i>Total Books</h5>
                        <h2 class="card-text"><?php echo $totalBooks; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white card-success">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-check-circle me-2"></i>Available Books</h5>
                        <h2 class="card-text"><?php echo $availableBooks; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white card-danger">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-book-reader me-2"></i>Borrowed Books</h5>
                        <h2 class="card-text"><?php echo $borrowedBooks; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white card-info">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users me-2"></i>Registered Users</h5>
                        <h2 class="card-text"><?php echo $totalUsers; ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-book me-1"></i>
                Recently Added Books
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Book ID</th>
                                <th>Book Name</th>
                                <th>Author</th>
                                <th>Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentBooks)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No books found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentBooks as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['book_id']); ?></td>
                                        <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td>
                                            <?php echo $book['available_copies']; ?> / <?php echo $book['total_copies']; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/admin/edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/admin/delete_book.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-danger confirm-action" data-confirm-message="Are you sure you want to delete this book?">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="<?php echo APP_URL; ?>/admin/add_book.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Book
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-exchange-alt me-1"></i>
                Recent Transactions
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Book</th>
                                <th>Borrowed Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentTransactions)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No transactions found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($transaction['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['book_name']); ?></td>
                                        <td><?php echo formatDate($transaction['borrow_date']); ?></td>
                                        <td>
                                            <?php if ($transaction['return_date']): ?>
                                                <span class="badge bg-success">Returned on <?php echo formatDate($transaction['return_date']); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Borrowed</span>
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

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-1"></i>
                Summary
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>Active Loans:</div>
                    <div><strong><?php echo $activeLoans; ?></strong></div>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <div>Loan Rate:</div>
                    <div><strong><?php echo $totalBooks > 0 ? round(($borrowedBooks / $totalBooks) * 100, 2) : 0; ?>%</strong></div>
                </div>
                <div class="d-flex justify-content-between">
                    <div>Books Per User:</div>
                    <div><strong><?php echo $totalUsers > 0 ? round($totalBooks / $totalUsers, 2) : 0; ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-book me-1"></i>
                Manage Books
            </div>
            <div class="card-body">
                <a href="<?php echo APP_URL; ?>/admin/add_book.php" class="btn btn-primary mb-3">
                    <i class="fas fa-plus"></i> Add New Book
                </a>
                
                <?php
                // Get all books
                $allBooks = $db->getAll("SELECT * FROM books ORDER BY book_name");
                ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Book ID</th>
                                <th>Book Name</th>
                                <th>Author</th>
                                <th>Total Copies</th>
                                <th>Available Copies</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allBooks)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No books found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allBooks as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['book_id']); ?></td>
                                        <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo $book['total_copies']; ?></td>
                                        <td>
                                            <?php if ($book['available_copies'] <= 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($book['available_copies'] < ($book['total_copies'] / 2)): ?>
                                                <span class="badge bg-warning text-dark"><?php echo $book['available_copies']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?php echo $book['available_copies']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/admin/edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/admin/delete_book.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-danger confirm-action" data-confirm-message="Are you sure you want to delete this book?">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
