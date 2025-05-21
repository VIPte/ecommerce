<?php
$pageTitle = 'My Profile';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to view your profile';
    redirect(BASE_URL . '/login.php');
}

$user_id = $_SESSION['user_id'];

// Get user information
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);

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
        $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Profile updated successfully';
            redirect(BASE_URL . '/profile.php');
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
            redirect(BASE_URL . '/profile.php');
        } else {
            $errors[] = 'Failed to change password';
        }
    }
}

// Get user's recent orders
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$recent_orders = [];
while ($order = $result->fetch_assoc()) {
    $recent_orders[] = $order;
}

// Include header
include 'includes/header.php';
?>

<div class="container profile-page">
    <div class="page-header">
        <h1>My Profile</h1>
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

    <div class="profile-grid">
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-avatar">
                    <span class="avatar-text"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <p>Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>

            <div class="profile-nav">
                <ul>
                    <li><a href="#profile-info" class="active">Personal Information</a></li>
                    <li><a href="#change-password">Change Password</a></li>
                    <li><a href="#recent-orders">Recent Orders</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>

        <div class="profile-content">
            <section id="profile-info" class="profile-section">
                <h2>Personal Information</h2>
                <form action="<?php echo BASE_URL; ?>/profile.php" method="POST" class="profile-form">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>"
                            disabled>
                        <small>Username cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name"
                            value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="address">Shipping Address</label>
                        <textarea id="address" name="address"
                            rows="4"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Update Profile</button>
                    </div>
                </form>
            </section>

            <section id="change-password" class="profile-section">
                <h2>Change Password</h2>
                <form action="<?php echo BASE_URL; ?>/profile.php" method="POST" class="profile-form">
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
            </section>

            <section id="recent-orders" class="profile-section">
                <h2>Recent Orders</h2>

                <?php if (empty($recent_orders)): ?>
                    <div class="no-orders">
                        <p>You haven't placed any orders yet.</p>
                        <a href="<?php echo BASE_URL; ?>" class="btn">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <span class="order-status status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>/order-details.php?id=<?php echo $order['order_id']; ?>"
                                                class="btn btn-sm">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="view-all-orders">
                            <a href="<?php echo BASE_URL; ?>/orders.php" class="btn">View All Orders</a>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<style>
    .profile-page {
        padding: 40px 0;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 30px;
    }

    .profile-sidebar {
        position: sticky;
        top: 20px;
    }

    .profile-card {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
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

    .profile-nav {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .profile-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .profile-nav li {
        border-bottom: 1px solid #eee;
    }

    .profile-nav li:last-child {
        border-bottom: none;
    }

    .profile-nav a {
        display: block;
        padding: 15px 20px;
        color: #333;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .profile-nav a:hover,
    .profile-nav a.active {
        background-color: #f8f9fa;
        color: #007bff;
    }

    .profile-content {
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

    .profile-form .form-group {
        margin-bottom: 20px;
    }

    .profile-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .profile-form input,
    .profile-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .profile-form small {
        display: block;
        margin-top: 5px;
        color: #6c757d;
    }

    .form-actions {
        margin-top: 30px;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }

    .orders-table th,
    .orders-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .orders-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .order-status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 14px;
    }

    .status-pending {
        background-color: #ffeeba;
        color: #856404;
    }

    .status-processing {
        background-color: #b8daff;
        color: #004085;
    }

    .status-shipped {
        background-color: #c3e6cb;
        color: #155724;
    }

    .status-delivered {
        background-color: #d4edda;
        color: #155724;
    }

    .status-cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 14px;
    }

    .view-all-orders {
        margin-top: 20px;
        text-align: right;
    }

    .no-orders {
        padding: 30px;
        text-align: center;
        background-color: #f8f9fa;
        border-radius: 8px;
    }

    .no-orders p {
        margin-bottom: 20px;
        color: #6c757d;
    }

    @media (max-width: 992px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .profile-sidebar {
            position: static;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle navigation
        const navLinks = document.querySelectorAll('.profile-nav a');
        const sections = document.querySelectorAll('.profile-section');

        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                // Remove active class from all links
                navLinks.forEach(l => l.classList.remove('active'));

                // Add active class to clicked link
                this.classList.add('active');

                // Get the target section
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);

                // Scroll to the target section
                if (targetSection) {
                    window.scrollTo({
                        top: targetSection.offsetTop - 20,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Set active nav based on scroll position
        window.addEventListener('scroll', function () {
            const scrollPosition = window.scrollY;

            sections.forEach((section, index) => {
                const sectionTop = section.offsetTop - 100;
                const sectionBottom = sectionTop + section.offsetHeight;

                if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                    navLinks.forEach(link => link.classList.remove('active'));
                    navLinks[index].classList.add('active');
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>