<?php
$pageTitle = 'My Orders';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to view your orders';
    redirect(BASE_URL . '/login.php');
}

$user_id = $_SESSION['user_id'];

// Get all user orders
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($order = $result->fetch_assoc()) {
    $orders[] = $order;
}

// Include header
include 'includes/header.php';
?>

<div class="container orders-page">
    <div class="page-header">
        <h1>My Orders</h1>
        <a href="<?php echo BASE_URL; ?>/profile.php" class="btn">Back to Profile</a>
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="no-orders">
            <p>You haven't placed any orders yet.</p>
            <a href="<?php echo BASE_URL; ?>" class="btn">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            <td>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
    .orders-page {
        padding: 40px 0;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .orders-table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .orders-table th, .orders-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .orders-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .order-status {
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
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 14px;
    }
    
    .no-orders {
        padding: 50px;
        text-align: center;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .no-orders p {
        margin-bottom: 20px;
        color: #6c757d;
        font-size: 18px;
    }
</style>

<?php include 'includes/footer.php'; ?>