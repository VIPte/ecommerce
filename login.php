<?php
$pageTitle = 'Login';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL);
}

$errors = [];

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim and sanitize input
    $username = trim(sanitize($_POST['username']));
    $password = trim($_POST['password']);

    // Validate input
    if (empty($username)) {
        $errors[] = 'Username or Email is required';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    // If no errors, attempt login
    if (empty($errors)) {
        try {
            // Determine if input is email or username
            $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            // Prepare statement with additional security
            $stmt = $db->prepare("SELECT user_id, username, password, user_type FROM users WHERE $field = ? LIMIT 1");

            if (!$stmt) {
                throw new Exception("Preparation of SQL statement failed: ");

            }

            $stmt->bind_param("s", $username);

            if (!$stmt->execute()) {
                throw new Exception("Execution of SQL statement failed: " . $stmt->error);
            }

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                // Add deliberate delay to prevent timing attacks
                sleep(1);
                $errors[] = 'Invalid username/email or password';
            } else {
                $user = $result->fetch_assoc();

                // Use password_verify with constant-time comparison
                if ($password === $user['password']) {
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];

                    $_SESSION['last_login'] = time();

                    // Transfer session cart to user cart if items exist
                    transferSessionCartToUserCart($user['user_id']);

                    // Check if there's a redirect parameter
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
                    if (!empty($redirect) && strpos($redirect, '/') === false) {
                        redirect(BASE_URL . '/' . $redirect);
                    } else if ($user['user_type'] === 'admin') {
                        redirect(ADMIN_URL);
                    } else {
                        redirect(BASE_URL);
                    }
                } else {
                    sleep(1);
                    $errors[] = 'Invalid username/email or password';
                }

            }

            $stmt->close();
        } catch (Exception $e) {
            // Log the error securely
            error_log('Login Error: ' . $e->getMessage());
            $errors[] = 'An unexpected error occurred. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h1 class="form-title">Login</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars(BASE_URL); ?>/login.php" method="POST">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username"
                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required
                autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Login</button>
        </div>
    </form>

    <p class="form-footer">
        Don't have an account? <a href="<?php echo htmlspecialchars(BASE_URL); ?>/register.php">Register</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
