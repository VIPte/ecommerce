<?php
$pageTitle = 'View Order';
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
$stmt = $db->prepare("SELECT o.*, u.username, u.email, u.full_name, u.phone 
                     FROM orders o 
                     JOIN users u ON o.user_id = u.user_id 
                     WHERE o.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

// If order not found, redirect to orders page
if (!$order) {
    $_SESSION['error_message'] = 'Order not found';
    redirect(ADMIN_URL . '/orders.php');
}

// Get order items
$stmt = $db->prepare("SELECT oi.*, p.title, p.image 
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.product_id 
                     WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$orderItems = [];
while ($item = $result->fetch_assoc()) {
    $orderItems[] = $item;
}

// Process status update if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize($_POST['status']);

    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Order status updated successfully';
        // Refresh the page to show updated status
        redirect(ADMIN_URL . '/view-order.php?id=' . $order_id);
    } else {
        $_SESSION['error_message'] = 'Failed to update order status';
    }
}

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-view-order">
    <div class="page-header">
        <h1 class="page-title">Order #<?php echo $order_id; ?></h1>
        <a href="<?php echo ADMIN_URL; ?>/orders.php" class="btn">Back to Orders</a>
    </div>

    <div class="order-details-container">
        <div class="order-info">
            <h2>Order Information</h2>
            <table class="info-table">
                <tr>
                    <th>Order ID:</th>
                    <td>#<?php echo $order['order_id']; ?></td>
                </tr>
                <tr>
                    <th>Order Date:</th>
                    <td><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Total Amount:</th>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
                <tr>
                    <th>Payment Method:</th>
                    <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
                </tr>
            </table>

            <h3>Update Order Status</h3>
            <form action="<?php echo ADMIN_URL; ?>/view-order.php?id=<?php echo $order_id; ?>" method="POST"
                class="status-form">
                <div class="form-group">
                    <select name="status" id="status">
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending
                        </option>
                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>
                            Processing</option>
                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped
                        </option>
                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>
                            Delivered</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>
                            Cancelled</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="btn">Update Status</button>
            </form>
        </div>

        <div class="customer-info">
            <h2>Customer Information</h2>
            <table class="info-table">
                <tr>
                    <th>Name:</th>
                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                </tr>
                <tr>
                    <th>Username:</th>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td><?php echo htmlspecialchars($order['phone']); ?></td>
                </tr>
                <tr>
                    <th>Shipping Address:</th>
                    <td><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="order-items">
        <h2>Order Items</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td>
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>"
                                class="product-image-preview">
                        </td>
                        <td><?php echo $item['title']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Total:</th>
                    <th>$<?php echo number_format($order['total_amount'], 2); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
    .order-details-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .order-info,
    .customer-info {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .info-table {
        width: 100%;
        margin-bottom: 20px;
    }

    .info-table th,
    .info-table td {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .info-table th {
        width: 40%;
        text-align: left;
        font-weight: 600;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
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

    .status-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .status-form .form-group {
        margin-bottom: 0;
    }

    .text-right {
        text-align: right;
    }

    @media (max-width: 768px) {
        .order-details-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/admin-footer.php'; ?>