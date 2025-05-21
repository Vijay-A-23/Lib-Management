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
$email = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $userType = cleanInput($_POST['user_type'] ?? '');
    
    // Add debug message
    error_log("Login attempt - Email: $email, User Type: $userType");
    
    // Validate inputs
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!isValidEmail($email)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($userType) || !in_array($userType, [ROLE_ADMIN, ROLE_USER])) {
        $errors[] = "Invalid user type";
    }
    
    // If no errors, attempt to login
    if (empty($errors)) {
        if ($userType === ROLE_ADMIN) {
            // Admin login
            $admin = $db->getRow("SELECT * FROM admins WHERE email = ?", [$email]);
            
            // Debug admin record
            error_log("Admin lookup result: " . ($admin ? "Found admin record" : "No admin record found"));
            
            // Check for default admin credentials
            if ($email === DEFAULT_ADMIN_EMAIL && $password === DEFAULT_ADMIN_PASSWORD) {
                // Set session for default admin
                $_SESSION['user_id'] = $admin['id'] ?? 1;
                $_SESSION['user_email'] = DEFAULT_ADMIN_EMAIL;
                $_SESSION['user_name'] = 'Admin User';
                $_SESSION['user_role'] = ROLE_ADMIN;
                $_SESSION['last_activity'] = time();
                
                error_log("Default admin login successful");
                // Redirect to admin dashboard
                redirect(APP_URL . '/admin/dashboard.php');
            }
            elseif ($admin && password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_email'] = $admin['email'];
                $_SESSION['user_name'] = $admin['name'];
                $_SESSION['user_role'] = ROLE_ADMIN;
                $_SESSION['last_activity'] = time();
                
                error_log("Admin login successful with password verification");
                // Redirect to admin dashboard
                redirect(APP_URL . '/admin/dashboard.php');
            } else {
                $errors[] = "Invalid admin credentials";
                error_log("Admin login failed: Invalid credentials");
            }
        } else {
            // User login
            $user = $db->getRow("SELECT * FROM users WHERE email = ?", [$email]);
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = ROLE_USER;
                $_SESSION['last_activity'] = time();
                
                // Redirect to user dashboard
                redirect(APP_URL . '/user/dashboard.php');
            } else {
                $errors[] = "Invalid user credentials";
            }
        }
    }
}

include_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4><i class="fas fa-sign-in-alt"></i> Login</h4>
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
                
                <?php if (isset($_GET['timeout']) && $_GET['timeout'] == 1): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle"></i> Your session has expired. Please login again.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> You have been successfully logged out.
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="user_type" class="form-label">Login As</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="user_type_user" value="user" <?php echo (!isset($_POST['user_type']) || $_POST['user_type'] == 'user') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="user_type_user">
                                User
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="user_type_admin" value="admin" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'admin') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="user_type_admin">
                                Admin
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
            </div>
            <div class="card-footer">
                <div class="text-center">
                    <p>Don't have an account? <a href="<?php echo APP_URL; ?>/signup.php">Sign Up</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
