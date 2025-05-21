<?php
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

// Check if customer exists and is not an admin
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ? AND user_type = 'client'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    $_SESSION['error_message'] = 'Customer not found or cannot be deleted';
    redirect(ADMIN_URL . '/customers.php');
}

// Delete customer
$db->getConnection()->begin_transaction();

try {
    // Delete customer's cart items
    $stmt = $db->prepare("DELETE ci FROM cart_items ci 
                         JOIN cart c ON ci.cart_id = c.cart_id 
                         WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Delete customer's cart
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Delete customer's order items
    $stmt = $db->prepare("DELETE oi FROM order_items oi 
                         JOIN orders o ON oi.order_id = o.order_id 
                         WHERE o.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Delete customer's orders
    $stmt = $db->prepare("DELETE FROM orders WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Delete customer
    $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $db->getConnection()->commit();

    $_SESSION['success_message'] = 'Customer deleted successfully';
} catch (Exception $e) {
    $db->getConnection()->rollback();
    $_SESSION['error_message'] = 'Failed to delete customer: ' . $e->getMessage();
}

// Redirect back to customers page
redirect(ADMIN_URL . '/customers.php');
?>