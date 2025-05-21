<?php
$pageTitle = 'Update Order Status';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Get order details
$stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

// If order not found, redirect to orders page
if (!$order) {
    $_SESSION['error_message'] = 'Order not found';
    redirect(ADMIN_URL . '/orders.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = sanitize($_POST['status']);

    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Order status updated successfully';
        redirect(ADMIN_URL . '/orders.php');
    } else {
        $_SESSION['error_message'] = 'Failed to update order status';
    }
}

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-update-order">
    <h1 class="page-title">Update Order Status</h1>
    
    <div class="order-info">
        <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
        <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
        <p><strong>Current Status:</strong> <?php echo ucfirst($order['status']); ?></p>
        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
    </div>
    
    <form action="<?php echo ADMIN_URL; ?>/update-order-status.php?id=<?php echo $order_id; ?>" method="POST" class="admin-form">
        <div class="form-group">
            <label for="status">New Status</label>
            <select id="status" name="status" required>
                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        
        <div class="form-actions">
            <a href="<?php echo ADMIN_URL; ?>/orders.php" class="btn">Cancel</a>
            <button type="submit" class="btn">Update Status</button>
        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>