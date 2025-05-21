<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get cart (works for both logged-in and non-logged-in users)
$cart = getCart();

include 'includes/header.php';
?>

<h1 class="page-title">Shopping Cart</h1>

<?php if (empty($cart['items'])): ?>
    <div class="empty-cart">
        <p>Your cart is empty.</p>
        <a href="<?php echo BASE_URL; ?>" class="btn">Continue Shopping</a>
    </div>
<?php else: ?>
    <div class="cart-container">
        <div class="cart-items">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart['items'] as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <div class="cart-item-image">
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>">
                                    </div>
                                    <div class="cart-item-details">
                                        <h3><?php echo $item['title']; ?></h3>
                                    </div>
                                </div>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form action="<?php echo BASE_URL; ?>/cart-actions.php" method="POST" class="cart-update-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                    <div class="cart-quantity">
                                        <button type="button" class="quantity-decrease">-</button>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1">
                                        <button type="button" class="quantity-increase">+</button>
                                        <button type="submit" class="btn">Update</button>
                                    </div>
                                </form>
                            </td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                            <td>
                                <form action="<?php echo BASE_URL; ?>/cart-actions.php" method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                    <button type="submit" class="btn btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="cart-summary">
            <h2 class="cart-summary-title">Order Summary</h2>
            
            <div class="cart-summary-row">
                <span>Subtotal:</span>
                <span>$<?php echo number_format($cart['total_price'], 2); ?></span>
            </div>
            
            <div class="cart-summary-row">
                <span>Shipping:</span>
                <span>Free</span>
            </div>
            
            <div class="cart-summary-row cart-total">
                <span>Total:</span>
                <span>$<?php echo number_format($cart['total_price'], 2); ?></span>
            </div>
            
            <div class="cart-actions">
                <a href="<?php echo BASE_URL; ?>" class="btn">Continue Shopping</a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/checkout.php" class="btn">Proceed to Checkout</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php?redirect=checkout.php" class="btn">Login to Checkout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Add JavaScript for quantity buttons -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle quantity buttons on cart page
    const decreaseBtns = document.querySelectorAll('.quantity-decrease');
    const increaseBtns = document.querySelectorAll('.quantity-increase');
    
    // Remove existing event listeners by cloning and replacing the buttons
    decreaseBtns.forEach(function(btn) {
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        // Add new event listener
        newBtn.addEventListener('click', function() {
            const input = this.parentNode.querySelector('input[name="quantity"]');
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        });
    });
    
    // Add event listeners to increase buttons
    increaseBtns.forEach(function(btn) {
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        // Add new event listener
        newBtn.addEventListener('click', function() {
            const input = this.parentNode.querySelector('input[name="quantity"]');
            let value = parseInt(input.value);
            input.value = value + 1;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>