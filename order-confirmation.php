<?php
$pageTitle = 'Order Confirmation';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

// Get order ID from URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get order details
$userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

// If order not found, redirect to home
if (!$order) {
    $_SESSION['error_message'] = 'Order not found';
    redirect(BASE_URL);
}

// Get order items
$stmt = $db->prepare("SELECT oi.*, p.title, p.image 
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.product_id 
                     WHERE oi.order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

$orderItems = [];
while ($item = $result->fetch_assoc()) {
    $orderItems[] = $item;
}

include 'includes/header.php';
?>

<div class="order-confirmation">
    <div class="confirmation-header">
        <h1 class="page-title">Order Confirmation</h1>
        <p class="confirmation-message">Thank you for your order! Your order has been placed successfully.</p>
    </div>
    
    <div class="order-details">
        <div class="order-info">
            <h2>Order Information</h2>
            <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
            <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
        </div>
        
        <div class="order-summary">
            <h2>Order Summary</h2>
            
            <div class="order-items">
                <?php foreach ($orderItems as $item): ?>
                    <div class="order-item">
                        <div class="order-item-image">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>">
                        </div>
                        <div class="order-item-details">
                            <h3><?php echo $item['title']; ?></h3>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                            <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                            <p>Subtotal: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-total">
                <div class="order-total-row">
                    <span>Total:</span>
                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="confirmation-actions">
        <a href="<?php echo BASE_URL; ?>" class="btn">Continue Shopping</a>
        <a href="<?php echo BASE_URL; ?>/profile.php" class="btn">View Your Orders</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>