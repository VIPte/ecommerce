<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'add':
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        if ($productId > 0 && $quantity > 0) {
            if (isLoggedIn()) {
                $userId = $_SESSION['user_id'];
                if (addToCart($userId, $productId, $quantity)) {
                    $_SESSION['success_message'] = 'Product added to cart';
                } else {
                    $_SESSION['error_message'] = 'Failed to add product to cart';
                }
            } else {
                if (addToSessionCart($productId, $quantity)) {
                    $_SESSION['success_message'] = 'Product added to cart';
                } else {
                    $_SESSION['error_message'] = 'Failed to add product to cart';
                }
            }
        }
        
        // Redirect back to previous page or product page
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . '/product.php?id=' . $productId;
        redirect($redirect);
        break;
        
    case 'update':
        $cartItemId = isset($_POST['cart_item_id']) ? $_POST['cart_item_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        if ($cartItemId) {
            if (isLoggedIn()) {
                if (updateCartItem($cartItemId, $quantity)) {
                    $_SESSION['success_message'] = 'Cart updated';
                } else {
                    $_SESSION['error_message'] = 'Failed to update cart';
                }
            } else {
                if (updateSessionCartItem($cartItemId, $quantity)) {
                    $_SESSION['success_message'] = 'Cart updated';
                } else {
                    $_SESSION['error_message'] = 'Failed to update cart';
                }
            }
        }
        
        redirect(BASE_URL . '/cart.php');
        break;
        
    case 'remove':
        $cartItemId = isset($_POST['cart_item_id']) ? $_POST['cart_item_id'] : 0;
        
        if ($cartItemId) {
            if (isLoggedIn()) {
                if (removeCartItem($cartItemId)) {
                    $_SESSION['success_message'] = 'Item removed from cart';
                } else {
                    $_SESSION['error_message'] = 'Failed to remove item from cart';
                }
            } else {
                if (removeSessionCartItem($cartItemId)) {
                    $_SESSION['success_message'] = 'Item removed from cart';
                } else {
                    $_SESSION['error_message'] = 'Failed to remove item from cart';
                }
            }
        }
        
        // Redirect back to the referring page instead of always to cart.php
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . '/cart.php';
        redirect($redirect);
        break;
        
    default:
        redirect(BASE_URL);
}
?>