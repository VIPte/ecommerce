<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to cancel an order';
    redirect(BASE_URL . '/login.php');
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
    $_SESSION['error_message'] = 'Invalid request';
    redirect(BASE_URL . '/profile.php');
}

$order_id = (int) $_POST['order_id'];
$user_id = $_SESSION['user_id'];

// Check if order exists and belongs to the user
$stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    $_SESSION['error_message'] = 'Order not found';
    redirect(BASE_URL . '/profile.php');
}

// Check if order can be cancelled (only pending or processing orders)
if ($order['status'] !== 'pending' && $order['status'] !== 'processing') {
    $_SESSION['error_message'] = 'This order cannot be cancelled';
    redirect(BASE_URL . '/order-details.php?id=' . $order_id);
}

// Update order status to cancelled
$stmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Order has been cancelled successfully';
} else {
    $_SESSION['error_message'] = 'Failed to cancel order';
}

// Redirect back to order details
redirect(BASE_URL . '/order-details.php?id=' . $order_id);
?>