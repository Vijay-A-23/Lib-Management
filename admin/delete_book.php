<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Require admin login
requireAdmin();

$db = Database::getInstance();
$conn = $db->getConnection();

// Get book ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setSessionMessage('Invalid book ID', 'error');
    redirect(APP_URL . '/admin/dashboard.php');
}

// Get book details
$book = $db->getRow("SELECT * FROM books WHERE id = ?", [$id]);
if (!$book) {
    setSessionMessage('Book not found', 'error');
    redirect(APP_URL . '/admin/dashboard.php');
}

// Check if the book can be deleted (no active borrows)
$borrowedCount = $db->getRow("SELECT COUNT(*) as count FROM borrowed_books WHERE book_id = ? AND return_date IS NULL", [$id])['count'] ?? 0;

if ($borrowedCount > 0) {
    setSessionMessage('Cannot delete book. It has active borrowings.', 'error');
    redirect(APP_URL . '/admin/dashboard.php');
}

// Confirm deletion
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] == 1;

if ($confirmed) {
    // Delete book transaction history
    $db->query("DELETE FROM borrowed_books WHERE book_id = ?", [$id]);
    
    // Delete the book
    $result = $db->query("DELETE FROM books WHERE id = ?", [$id]);
    
    if ($result) {
        setSessionMessage('Book deleted successfully!');
    } else {
        setSessionMessage('Failed to delete book. Please try again.', 'error');
    }
    
    redirect(APP_URL . '/admin/dashboard.php');
} else {
    // Show confirmation page
    include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h4><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h4>
            </div>
            <div class="card-body">
                <p>Are you sure you want to delete the following book?</p>
                
                <table class="table">
                    <tr>
                        <th>Book ID:</th>
                        <td><?php echo htmlspecialchars($book['book_id']); ?></td>
                    </tr>
                    <tr>
                        <th>Book Name:</th>
                        <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Author:</th>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                    </tr>
                    <tr>
                        <th>Copies:</th>
                        <td><?php echo $book['available_copies']; ?> / <?php echo $book['total_copies']; ?></td>
                    </tr>
                </table>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle"></i> This action cannot be undone. All borrowing history for this book will also be deleted.
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="btn btn-secondary me-md-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/delete_book.php?id=<?php echo $id; ?>&confirm=1" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Book
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    include_once '../includes/footer.php';
}
?>
