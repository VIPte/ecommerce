<?php
$pageTitle = 'Order Details';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to view order details';
    redirect(BASE_URL . '/login.php');
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get order details
$stmt = $db->prepare("SELECT o.*, u.full_name, u.email, u.phone 
                     FROM orders o 
                     JOIN users u ON o.user_id = u.user_id 
                     WHERE o.order_id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

// If order not found or doesn't belong to the user, redirect
if (!$order) {
    $_SESSION['error_message'] = 'Order not found';
    redirect(BASE_URL . '/profile.php');
}

// Get order items
$stmt = $db->prepare("SELECT oi.*, p.title, p.image 
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.product_id 
                     WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$order_items = [];
while ($item = $result->fetch_assoc()) {
    $order_items[] = $item;
}

// Include header
include 'includes/header.php';
?>

<div class="container order-details-page">
    <div class="page-header">
        <h1>Order Details</h1>
        <a href="<?php echo BASE_URL; ?>/profile.php#recent-orders" class="btn">Back to Profile</a>
    </div>

    <div class="order-details-grid">
        <div class="order-summary">
            <div class="order-info-card">
                <h2>Order #<?php echo $order['order_id']; ?></h2>
                <div class="order-meta">
                    <div class="meta-item">
                        <span class="meta-label">Date:</span>
                        <span
                            class="meta-value"><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Status:</span>
                        <span
                            class="meta-value status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Payment Method:</span>
                        <span
                            class="meta-value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Total:</span>
                        <span class="meta-value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="shipping-info-card">
                <h3>Shipping Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name'] ?? ''); ?></p>
                <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['address'] ?? '')); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone'] ?? ''); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email'] ?? ''); ?></p>
            </div>
        </div>

        <div class="order-items">
            <h3>Order Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td class="product-cell">
                                <div class="product-info">
                                    <img src="<?php echo $item['image']; ?>"
                                        alt="<?php echo htmlspecialchars($item['title']); ?>" class="product-image">
                                    <div class="product-details">
                                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                    </div>
                                </div>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Shipping:</strong></td>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
                        <td class="order-total">$<?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
        <div class="order-actions">
            <form action="<?php echo BASE_URL; ?>/cancel-order.php" method="POST"
                onsubmit="return confirm('Are you sure you want to cancel this order?');">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                <button type="submit" class="btn btn-danger">Cancel Order</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<style>
    .order-details-page {
        padding: 40px 0;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .order-details-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 30px;
    }

    .order-info-card,
    .shipping-info-card {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .order-info-card h2 {
        margin-top: 0;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .order-meta {
        margin-top: 20px;
    }

    .meta-item {
        display: flex;
        margin-bottom: 10px;
    }

    .meta-label {
        width: 150px;
        font-weight: 600;
    }

    .shipping-info-card h3 {
        margin-top: 0;
        margin-bottom: 15px;
    }

    .shipping-info-card p {
        margin: 10px 0;
    }

    .order-items {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .order-items h3 {
        margin-top: 0;
        margin-bottom: 20px;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table th,
    .items-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .items-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .product-cell {
        width: 50%;
    }

    .product-info {
        display: flex;
        align-items: center;
    }

    .product-image {
        width: 60px;
        height: 60px;
        object-fit: contain;
        margin-right: 15px;
    }

    .product-details h4 {
        margin: 0 0 5px 0;
        font-size: 16px;
    }

    .text-right {
        text-align: right;
    }

    .order-total {
        font-size: 18px;
        font-weight: 600;
        color: #007bff;
    }

    .order-actions {
        margin-top: 30px;
        text-align: right;
    }

    .btn-danger {
        background-color: #dc3545;
        color: #fff;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .status-badge {
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

    @media (max-width: 992px) {
        .order-details-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>