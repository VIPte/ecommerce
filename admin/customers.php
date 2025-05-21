<?php
$pageTitle = 'Manage Customers';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Get all customers
$stmt = $db->prepare("SELECT * FROM users WHERE user_type = 'client' ORDER BY user_id DESC");
$stmt->execute();
$result = $stmt->get_result();

$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-customers">
    <h1 class="page-title">Manage Customers</h1>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Full Name</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo $customer['user_id']; ?></td>
                    <td><?php echo $customer['username']; ?></td>
                    <td><?php echo $customer['email']; ?></td>
                    <td><?php echo $customer['full_name']; ?></td>
                    <td><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></td>
                    <td>
                        <a href="<?php echo ADMIN_URL; ?>/view-customer.php?id=<?php echo $customer['user_id']; ?>" class="btn">View</a>
                        <a href="<?php echo ADMIN_URL; ?>/delete-customer.php?id=<?php echo $customer['user_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this customer?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin-footer.php'; ?>