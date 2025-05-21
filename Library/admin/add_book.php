<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Require admin login
requireAdmin();

$db = Database::getInstance();
$conn = $db->getConnection();

$errors = [];
$book = [
    'book_id' => '',
    'book_name' => '',
    'author' => '',
    'total_copies' => '',
    'available_copies' => ''
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $book['book_id'] = cleanInput($_POST['book_id'] ?? '');
    $book['book_name'] = cleanInput($_POST['book_name'] ?? '');
    $book['author'] = cleanInput($_POST['author'] ?? '');
    $book['total_copies'] = (int)($_POST['total_copies'] ?? 0);
    $book['available_copies'] = (int)($_POST['available_copies'] ?? 0);
    
    // Validation
    if (empty($book['book_id'])) {
        $errors[] = 'Book ID is required';
    } else {
        // Check if book ID already exists
        $existingBook = $db->getRow("SELECT id FROM books WHERE book_id = ?", [$book['book_id']]);
        if ($existingBook) {
            $errors[] = 'Book ID already exists. Please use a unique ID.';
        }
    }
    
    if (empty($book['book_name'])) {
        $errors[] = 'Book Name is required';
    }
    
    if (empty($book['author'])) {
        $errors[] = 'Author is required';
    }
    
    if ($book['total_copies'] <= 0) {
        $errors[] = 'Total Copies must be greater than 0';
    }
    
    if ($book['available_copies'] < 0) {
        $errors[] = 'Available Copies cannot be negative';
    }
    
    if ($book['available_copies'] > $book['total_copies']) {
        $errors[] = 'Available Copies cannot be greater than Total Copies';
    }
    
    // If no errors, insert the book
    if (empty($errors)) {
        $query = "INSERT INTO books (book_id, book_name, author, total_copies, available_copies) 
                  VALUES (?, ?, ?, ?, ?)";
        $params = [
            $book['book_id'],
            $book['book_name'],
            $book['author'],
            $book['total_copies'],
            $book['available_copies']
        ];
        
        $result = $db->query($query, $params);
        
        if ($result) {
            setSessionMessage('Book added successfully!');
            redirect(APP_URL . '/admin/dashboard.php');
        } else {
            $errors[] = 'Failed to add book. Please try again.';
        }
    }
}

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-plus"></i> Add New Book</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="book_id" class="form-label">Book ID</label>
                        <input type="text" class="form-control" id="book_id" name="book_id" value="<?php echo htmlspecialchars($book['book_id']); ?>" required>
                        <small class="text-muted">Unique identifier for the book (e.g., ISBN or custom ID)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="book_name" class="form-label">Book Name</label>
                        <input type="text" class="form-control" id="book_name" name="book_name" value="<?php echo htmlspecialchars($book['book_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="author" class="form-label">Author</label>
                        <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="total_copies" class="form-label">Total Copies</label>
                                <input type="number" class="form-control" id="total_copies" name="total_copies" value="<?php echo $book['total_copies']; ?>" min="1" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="available_copies" class="form-label">Available Copies</label>
                                <input type="number" class="form-control" id="available_copies" name="available_copies" value="<?php echo $book['available_copies']; ?>" min="0" required>
                                <small class="text-muted">Must be less than or equal to Total Copies</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Book
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-update available copies when total copies change
    document.addEventListener('DOMContentLoaded', function() {
        const totalCopies = document.getElementById('total_copies');
        const availableCopies = document.getElementById('available_copies');
        
        totalCopies.addEventListener('change', function() {
            if (parseInt(availableCopies.value) > parseInt(this.value)) {
                availableCopies.value = this.value;
            }
            availableCopies.max = this.value;
        });
    });
</script>

<?php include_once '../includes/footer.php'; ?>
