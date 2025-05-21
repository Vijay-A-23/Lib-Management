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
    'id' => 0,
    'book_id' => '',
    'book_name' => '',
    'author' => '',
    'total_copies' => 0,
    'available_copies' => 0,
    'borrowed_copies' => 0
];

// Get book ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setSessionMessage('Invalid book ID', 'error');
    redirect(APP_URL . '/admin/dashboard.php');
}

// Get book details
$bookDetails = $db->getRow("SELECT * FROM books WHERE id = ?", [$id]);
if (!$bookDetails) {
    setSessionMessage('Book not found', 'error');
    redirect(APP_URL . '/admin/dashboard.php');
}

// Calculate borrowed copies
$borrowed_copies = $bookDetails['total_copies'] - $bookDetails['available_copies'];

// Fill book array with fetched data
$book = [
    'id' => $bookDetails['id'],
    'book_id' => $bookDetails['book_id'],
    'book_name' => $bookDetails['book_name'],
    'author' => $bookDetails['author'],
    'total_copies' => $bookDetails['total_copies'],
    'available_copies' => $bookDetails['available_copies'],
    'borrowed_copies' => $borrowed_copies
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $updatedBook = [
        'id' => $id,
        'book_id' => cleanInput($_POST['book_id'] ?? ''),
        'book_name' => cleanInput($_POST['book_name'] ?? ''),
        'author' => cleanInput($_POST['author'] ?? ''),
        'total_copies' => (int)($_POST['total_copies'] ?? 0),
    ];
    
    // Validation
    if (empty($updatedBook['book_id'])) {
        $errors[] = 'Book ID is required';
    } else {
        // Check if book ID already exists and is not the current book
        $existingBook = $db->getRow("SELECT id FROM books WHERE book_id = ? AND id != ?", [$updatedBook['book_id'], $id]);
        if ($existingBook) {
            $errors[] = 'Book ID already exists. Please use a unique ID.';
        }
    }
    
    if (empty($updatedBook['book_name'])) {
        $errors[] = 'Book Name is required';
    }
    
    if (empty($updatedBook['author'])) {
        $errors[] = 'Author is required';
    }
    
    if ($updatedBook['total_copies'] <= 0) {
        $errors[] = 'Total Copies must be greater than 0';
    }
    
    if ($updatedBook['total_copies'] < $book['borrowed_copies']) {
        $errors[] = 'Total Copies cannot be less than the number of borrowed copies (' . $book['borrowed_copies'] . ')';
    }
    
    // If no errors, update the book
    if (empty($errors)) {
        // Calculate the new available copies
        $new_available_copies = $updatedBook['total_copies'] - $book['borrowed_copies'];
        
        $query = "UPDATE books SET book_id = ?, book_name = ?, author = ?, total_copies = ?, available_copies = ? 
                  WHERE id = ?";
        $params = [
            $updatedBook['book_id'],
            $updatedBook['book_name'],
            $updatedBook['author'],
            $updatedBook['total_copies'],
            $new_available_copies,
            $id
        ];
        
        $result = $db->query($query, $params);
        
        if ($result) {
            setSessionMessage('Book updated successfully!');
            redirect(APP_URL . '/admin/dashboard.php');
        } else {
            $errors[] = 'Failed to update book. Please try again.';
        }
    }
}

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-edit"></i> Edit Book</h4>
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
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="total_copies" class="form-label">Total Copies</label>
                                <input type="number" class="form-control" id="total_copies" name="total_copies" value="<?php echo $book['total_copies']; ?>" min="<?php echo $book['borrowed_copies']; ?>" required>
                                <small class="text-muted">Must be at least equal to borrowed copies</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="available_copies" class="form-label">Available Copies</label>
                                <input type="number" class="form-control" id="available_copies" value="<?php echo $book['available_copies']; ?>" disabled>
                                <small class="text-muted">Will be adjusted automatically</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="borrowed_copies" class="form-label">Borrowed Copies</label>
                                <input type="number" class="form-control" id="borrowed_copies" value="<?php echo $book['borrowed_copies']; ?>" disabled>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_available" class="form-label">New Available Copies (after update)</label>
                        <input type="text" class="form-control" id="new_available" readonly>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Book
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Calculate new available copies when total copies change
    document.addEventListener('DOMContentLoaded', function() {
        const totalCopies = document.getElementById('total_copies');
        const borrowedCopies = parseInt(document.getElementById('borrowed_copies').value);
        const newAvailable = document.getElementById('new_available');
        
        function updateNewAvailable() {
            const total = parseInt(totalCopies.value);
            const newAvailableValue = total - borrowedCopies;
            newAvailable.value = newAvailableValue;
            
            if (newAvailableValue < 0) {
                newAvailable.classList.add('is-invalid');
            } else {
                newAvailable.classList.remove('is-invalid');
                newAvailable.classList.add('is-valid');
            }
        }
        
        totalCopies.addEventListener('input', updateNewAvailable);
        
        // Initial calculation
        updateNewAvailable();
    });
</script>

<?php include_once '../includes/footer.php'; ?>
