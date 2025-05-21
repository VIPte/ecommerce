<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Delete product
if ($product_id > 0) {
    $stmt = $db->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Product deleted successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to delete product';
    }
}

// Redirect back to products page
redirect(ADMIN_URL . '/products.php');
?>