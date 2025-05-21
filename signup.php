<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(APP_URL . '/admin/dashboard.php');
    } else {
        redirect(APP_URL . '/user/dashboard.php');
    }
}

$db = Database::getInstance();
$conn = $db->getConnection();

$errors = [];
$name = '';
$email = '';

// Process signup form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!isValidEmail($email)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $existingUser = $db->getRow("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existingUser) {
            $errors[] = "Email already in use";
        }
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // If no errors, create the account
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())";
        $result = $db->query($query, [$name, $email, $hashedPassword]);
        
        if ($result) {
            // Get the new user ID
            $userId = $db->lastInsertId();
            
            // Set session variables
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = ROLE_USER;
            $_SESSION['last_activity'] = time();
            
            setSessionMessage('Your account has been created successfully!');
            redirect(APP_URL . '/user/dashboard.php');
        } else {
            $errors[] = "Failed to create account. Please try again.";
        }
    }
}

include_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4><i class="fas fa-user-plus"></i> Sign Up</h4>
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
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Password must be at least 6 characters long</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>
            <div class="card-footer">
                <div class="text-center">
                    <p>Already have an account? <a href="<?php echo APP_URL; ?>/login.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
