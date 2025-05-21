<?php
$pageTitle = 'Checkout';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to checkout';
    redirect(BASE_URL . '/login.php');
}

// Get user's cart
$userId = $_SESSION['user_id'];
$cart = getUserCart($userId);

// If cart is empty, redirect to cart page
if (empty($cart['items'])) {
    $_SESSION['error_message'] = 'Your cart is empty';
    redirect(BASE_URL . '/cart.php');
}

// Get user details
$user = getUserById($userId);

$errors = [];

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = sanitize($_POST['shipping_address']);
    $payment_method = sanitize($_POST['payment_method']);
    
    // Validate input
    if (empty($shipping_address)) {
        $errors[] = 'Shipping address is required';
    }
    
    if (empty($payment_method)) {
        $errors[] = 'Payment method is required';
    }
    
    // If no errors, create order
    if (empty($errors)) {
        $orderId = createOrder($userId, $shipping_address, $payment_method);
        
        if ($orderId) {
            $_SESSION['success_message'] = 'Order placed successfully';
            redirect(BASE_URL . '/order-confirmation.php?id=' . $orderId);
        } else {
            $errors[] = 'Failed to place order. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<h1 class="page-title">Checkout</h1>

<?php if (!empty($errors)): ?>
    <div class="error-message">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="checkout-container">
    <div class="checkout-form">
        <h2>Shipping Information</h2>
        
        <form action="<?php echo BASE_URL; ?>/checkout.php" method="POST">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="shipping_address">Shipping Address *</label>
                <textarea id="shipping_address" name="shipping_address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            
            <h2>Payment Method</h2>
            
            <div class="form-group">
                <div class="payment-methods">
                    <label>
                        <input type="radio" name="payment_method" value="credit_card" checked>
                        Credit Card
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="paypal">
                        PayPal
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="cash_on_delivery">
                        Cash on Delivery
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/cart.php" class="btn">Back to Cart</a>
                <button type="submit" class="btn">Place Order</button>
            </div>
        </form>
    </div>
    
    <div class="order-summary">
        <h2>Order Summary</h2>
        
        <div class="order-items">
            <?php foreach ($cart['items'] as $item): ?>
                <div class="order-item">
                    <div class="order-item-image">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>">
                    </div>
                    <div class="order-item-details">
                        <h3><?php echo $item['title']; ?></h3>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                        <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                        <p>Subtotal: $<?php echo number_format($item['subtotal'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="order-total">
            <div class="order-total-row">
                <span>Subtotal:</span>
                <span>$<?php echo number_format($cart['total_price'], 2); ?></span>
            </div>
            
            <div class="order-total-row">
                <span>Shipping:</span>
                <span>Free</span>
            </div>
            
            <div class="order-total-row total">
                <span>Total:</span>
                <span>$<?php echo number_format($cart['total_price'], 2); ?></span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>