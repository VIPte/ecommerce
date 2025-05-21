<?php
$pageTitle = 'View Customer';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Get customer ID from URL
$user_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Get customer details
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ? AND user_type = 'client'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

// If customer not found, redirect to customers page
if (!$customer) {
    $_SESSION['error_message'] = 'Customer not found';
    redirect(ADMIN_URL . '/customers.php');
}

// Get customer orders
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($order = $result->fetch_assoc()) {
    $orders[] = $order;
}

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-view-customer">
    <div class="page-header">
        <h1 class="page-title">Customer: <?php echo htmlspecialchars($customer['username']); ?></h1>
        <a href="<?php echo ADMIN_URL; ?>/customers.php" class="btn">Back to Customers</a>
    </div>

    <div class="customer-details">
        <h2>Customer Information</h2>
        <table class="info-table">
            <tr>
                <th>ID:</th>
                <td><?php echo $customer['user_id']; ?></td>
            </tr>
            <tr>
                <th>Username:</th>
                <td><?php echo htmlspecialchars($customer['username']); ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?php echo htmlspecialchars($customer['email']); ?></td>
            </tr>
            <tr>
                <th>Full Name:</th>
                <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
            </tr>
            <tr>
                <th>Address:</th>
                <td><?php echo nl2br(htmlspecialchars($customer['address'])); ?></td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td><?php echo htmlspecialchars($customer['phone']); ?></td>
            </tr>
            <tr>
                <th>Registered:</th>
                <td><?php echo date('F j, Y, g:i a', strtotime($customer['created_at'])); ?></td>
            </tr>
        </table>
    </div>

    <div class="customer-orders">
        <h2>Customer Orders</h2>

        <?php if (empty($orders)): ?>
            <p>This customer has no orders yet.</p>
        <?php else: ?>
            <table class="admin-table">
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
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            <td><?php echo ucfirst($order['status']); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/view-order.php?id=<?php echo $order['order_id']; ?>"
                                    class="btn">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
    .customer-details {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .info-table {
        width: 100%;
    }

    .info-table th,
    .info-table td {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .info-table th {
        width: 20%;
        text-align: left;
        font-weight: 600;
    }

    .customer-orders {
        margin-top: 30px;
    }
</style>

<?php include 'includes/admin-footer.php'; ?>