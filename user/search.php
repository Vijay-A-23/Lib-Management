<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Require user login (non-admin)
requireUser();

$db = Database::getInstance();
$conn = $db->getConnection();

$searchTerm = '';
$searchBy = 'book_name';
$searchResults = [];
$searched = false;

// Process search form
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search'])) {
    $searchTerm = cleanInput($_GET['search'] ?? '');
    $searchBy = cleanInput($_GET['search_by'] ?? 'book_name');
    
    // Validate search criteria
    if (!in_array($searchBy, ['book_name', 'book_id', 'author'])) {
        $searchBy = 'book_name'; // Default if invalid
    }
    
    if (!empty($searchTerm)) {
        $searched = true;
        
        // Perform search
        $query = "SELECT * FROM books WHERE " . $searchBy . " LIKE ? ORDER BY book_name";
        $searchResults = $db->getAll($query, ['%' . $searchTerm . '%']);
    }
}

// Recently added books (fallback if no search performed)
$recentBooks = [];
if (!$searched) {
    $recentBooks = $db->getAll("SELECT * FROM books ORDER BY id DESC LIMIT 10");
}

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1><i class="fas fa-search"></i> Search Books</h1>
        <hr>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-search"></i> Search Criteria</h5>
            </div>
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Enter search term..." value="<?php echo htmlspecialchars($searchTerm); ?>" required>
                    </div>
                    
                    <div class="col-md-2">
                        <select class="form-select" id="search_by" name="search_by">
                            <option value="book_name" <?php echo $searchBy == 'book_name' ? 'selected' : ''; ?>>Book Name</option>
                            <option value="book_id" <?php echo $searchBy == 'book_id' ? 'selected' : ''; ?>>Book ID</option>
                            <option value="author" <?php echo $searchBy == 'author' ? 'selected' : ''; ?>>Author</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <?php if ($searched): ?>
                    <h5><i class="fas fa-list"></i> Search Results for "<?php echo htmlspecialchars($searchTerm); ?>" in <?php echo ucfirst(str_replace('_', ' ', $searchBy)); ?></h5>
                <?php else: ?>
                    <h5><i class="fas fa-book"></i> Recently Added Books</h5>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php 
                $displayBooks = $searched ? $searchResults : $recentBooks;
                
                if (empty($displayBooks)): 
                ?>
                    <div class="alert alert-info">
                        <?php if ($searched): ?>
                            <i class="fas fa-info-circle"></i> No books found matching your search criteria.
                        <?php else: ?>
                            <i class="fas fa-info-circle"></i> No books available in the library yet.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Book ID</th>
                                    <th>Book Name</th>
                                    <th>Author</th>
                                    <th>Availability</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($displayBooks as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['book_id']); ?></td>
                                        <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td>
                                            <?php if ($book['available_copies'] <= 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($book['available_copies'] < ($book['total_copies'] / 2)): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <?php echo $book['available_copies']; ?> of <?php echo $book['total_copies']; ?> available
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <?php echo $book['available_copies']; ?> of <?php echo $book['total_copies']; ?> available
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($book['available_copies'] > 0): ?>
                                                <a href="<?php echo APP_URL; ?>/user/borrow.php?book_id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-book"></i> Borrow
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    <i class="fas fa-book"></i> Not Available
                                                </button>
                                            <?php endif; ?>
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
