<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Total number of books in the library
$totalBooks = $db->getRow("SELECT COUNT(*) as count FROM books")['count'] ?? 0;

// Total available books
$availableBooks = $db->getRow("SELECT SUM(available_copies) as count FROM books")['count'] ?? 0;

// Total registered users
$totalUsers = $db->getRow("SELECT COUNT(*) as count FROM users")['count'] ?? 0;

// Recently added books
$recentBooks = $db->getAll("SELECT * FROM books ORDER BY id DESC LIMIT 5");

include_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="jumbotron bg-light p-5 rounded">
            <h1 class="display-4">Welcome to the Library Management System</h1>
            <p class="lead">A simple and efficient way to manage books and borrowings.</p>
            <hr class="my-4">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <p>Welcome, Admin! Manage books, users, and track borrowings.</p>
                    <a class="btn btn-primary btn-lg" href="<?php echo APP_URL; ?>/admin/dashboard.php" role="button">
                        <i class="fas fa-tachometer-alt"></i> Go to Admin Dashboard
                    </a>
                <?php else: ?>
                    <p>Welcome back! You can search, borrow, and return books.</p>
                    <a class="btn btn-primary btn-lg" href="<?php echo APP_URL; ?>/user/dashboard.php" role="button">
                        <i class="fas fa-tachometer-alt"></i> Go to Your Dashboard
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <p>Please login to your account or sign up to get started.</p>
                <div class="d-flex gap-2">
                    <a class="btn btn-primary btn-lg" href="<?php echo APP_URL; ?>/login.php" role="button">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a class="btn btn-success btn-lg" href="<?php echo APP_URL; ?>/signup.php" role="button">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-book"></i> Library Stats</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span>Total Books:</span>
                    <span class="badge bg-primary"><?php echo $totalBooks; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Available Books:</span>
                    <span class="badge bg-success"><?php echo $availableBooks; ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Registered Users:</span>
                    <span class="badge bg-info"><?php echo $totalUsers; ?></span>
                </div>
            </div>
            <?php if (!isLoggedIn()): ?>
            <div class="card-footer">
                <a href="<?php echo APP_URL; ?>/signup.php" class="btn btn-success w-100">Sign Up Now</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5><i class="fas fa-book"></i> Recently Added Books</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentBooks)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No books have been added to the library yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Book ID</th>
                                    <th>Book Name</th>
                                    <th>Author</th>
                                    <th>Availability</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBooks as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['book_id']); ?></td>
                                        <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td>
                                            <?php if ($book['available_copies'] <= 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($book['available_copies'] < ($book['total_copies'] / 2)): ?>
                                                <span class="badge bg-warning text-dark">Limited</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (isLoggedIn() && !isAdmin()): ?>
            <div class="card-footer">
                <a href="<?php echo APP_URL; ?>/user/search.php" class="btn btn-info text-white w-100">
                    <i class="fas fa-search"></i> Search All Books
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-info-circle"></i> About the Library Management System</h5>
            </div>
            <div class="card-body">
                <p>The Library Management System is designed to help libraries efficiently manage their book inventory and track borrowings.</p>
                <p><strong>Features include:</strong></p>
                <div class="row">
                    <div class="col-md-6">
                        <ul>
                            <li>User registration and authentication</li>
                            <li>Book search functionality</li>
                            <li>Book borrowing and returning</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul>
                            <li>Admin book management</li>
                            <li>Borrowing history tracking</li>
                            <li>Book availability status</li>
                        </ul>
                    </div>
                </div>
                <?php if (!isLoggedIn()): ?>
                    <p>To get started, please <a href="<?php echo APP_URL; ?>/login.php">login</a> or <a href="<?php echo APP_URL; ?>/signup.php">sign up</a> for an account.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
