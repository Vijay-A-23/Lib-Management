<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/session.php';

// Get the current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>/index.php"><?php echo APP_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <!-- Admin Navigation -->
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/admin/dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page == 'add_book.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/admin/add_book.php">
                                    <i class="fas fa-plus"></i> Add Book
                                </a>
                            </li>
                        <?php else: ?>
                            <!-- User Navigation -->
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/user/dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page == 'search.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/user/search.php">
                                    <i class="fas fa-search"></i> Search Books
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page == 'borrow.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/user/borrow.php">
                                    <i class="fas fa-book"></i> Borrow Books
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page == 'return.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/user/return.php">
                                    <i class="fas fa-undo"></i> Return Books
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'login.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'signup.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/signup.php">
                                <i class="fas fa-user-plus"></i> Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php echo displaySessionMessage(); ?>
