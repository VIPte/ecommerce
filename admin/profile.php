<?php
$pageTitle = 'Admin Profile';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

$user_id = $_SESSION['user_id'];

// Get admin information
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ? AND user_type = 'admin'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);

    $errors = [];

    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } else {
        // Check if email is already in use by another user
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = 'Email is already in use by another account';
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Profile updated successfully';
            redirect(ADMIN_URL . '/profile.php');
        } else {
            $errors[] = 'Failed to update profile';
        }
    }
}

// Process password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validate current password
    $stmt = $db->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    if (!password_verify($current_password, $user_data['password'])) {
        $errors[] = 'Current password is incorrect';
    }

    // Validate new password
    if (empty($new_password)) {
        $errors[] = 'New password is required';
    } elseif (strlen($new_password) < 6) {
        $errors[] = 'New password must be at least 6 characters long';
    }

    // Validate password confirmation
    if ($new_password !== $confirm_password) {
        $errors[] = 'Password confirmation does not match';
    }

    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Password changed successfully';
            redirect(ADMIN_URL . '/profile.php');
        } else {
            $errors[] = 'Failed to change password';
        }
    }
}

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-profile">
    <div class="page-header">
        <h1 class="page-title">Admin Profile</h1>
    </div>

    <?php
    // Display error messages
    if (isset($errors) && !empty($errors)) {
        echo '<div class="error-message"><ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul></div>';
    }
    ?>

    <div class="admin-profile-grid">
        <div class="admin-profile-sidebar">
            <div class="profile-card">
                <div class="profile-avatar">
                    <span class="avatar-text"><?php echo strtoupper(substr($admin['username'], 0, 1)); ?></span>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($admin['username']); ?></h2>
                    <p><?php echo htmlspecialchars($admin['email']); ?></p>
                    <p>Administrator</p>
                </div>
            </div>
        </div>

        <div class="admin-profile-content">
            <div class="profile-section">
                <h2>Personal Information</h2>
                <form action="<?php echo ADMIN_URL; ?>/profile.php" method="POST" class="admin-form">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($admin['username']); ?>"
                            disabled>
                        <small>Username cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name"
                            value="<?php echo htmlspecialchars($admin['full_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($admin['phone']); ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Update Profile</button>
                    </div>
                </form>
            </div>

            <div class="profile-section">
                <h2>Change Password</h2>
                <form action="<?php echo ADMIN_URL; ?>/profile.php" method="POST" class="admin-form">
                    <input type="hidden" name="change_password" value="1">

                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small>Password must be at least 6 characters long</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-profile-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 30px;
    }

    .profile-card {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: #007bff;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: bold;
        margin: 0 auto 15px;
    }

    .profile-info h2 {
        margin-top: 0;
        margin-bottom: 10px;
    }

    .profile-info p {
        margin: 5px 0;
        color: #6c757d;
    }

    .admin-profile-content {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }

    .profile-section {
        margin-bottom: 40px;
    }

    .profile-section:last-child {
        margin-bottom: 0;
    }

    .profile-section h2 {
        margin-top: 0;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .admin-form .form-group {
        margin-bottom: 20px;
    }

    .admin-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .admin-form input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .admin-form small {
        display: block;
        margin-top: 5px;
        color: #6c757d;
    }

    .form-actions {
        margin-top: 30px;
    }

    @media (max-width: 992px) {
        .admin-profile-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/admin-footer.php'; ?>