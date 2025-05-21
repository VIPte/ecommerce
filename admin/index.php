<?php
$pageTitle = 'Admin Dashboard';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Get statistics
$result = $db->query("SELECT COUNT(*) as total_products FROM products");
$totalProducts = $result->fetch_assoc()['total_products'];

$result = $db->query("SELECT COUNT(*) as total_users FROM users WHERE user_type = 'client'");
$totalUsers = $result->fetch_assoc()['total_users'];

$result = $db->query("SELECT COUNT(*) as total_orders FROM orders");
$totalOrders = $result->fetch_assoc()['total_orders'];

$result = $db->query("SELECT SUM(total_amount) as total_revenue FROM orders");
$totalRevenue = $result->fetch_assoc()['total_revenue'] ?? 0;

// Get recent orders
$result = $db->query("SELECT o.*, u.username
FROM orders o
JOIN users u ON o.user_id = u.user_id
ORDER BY o.order_date DESC
LIMIT 5");

$recentOrders = [];
while ($order = $result->fetch_assoc()) {
    $recentOrders[] = $order;
}

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-dashboard">
<h1 class="page-title">Admin Dashboard</h1>

<div class="dashboard-stats">
<div class="stat-card">
<div class="stat-value"><?php echo $totalProducts; ?></div>
<div class="stat-label">Total Products</div>
</div>

<div class="stat-card">
<div class="stat-value"><?php echo $totalUsers; ?></div>
<div class="stat-label">Registered Users</div>
</div>

<div class="stat-card">
<div class="stat-value"><?php echo $totalOrders; ?></div>
<div class="stat-label">Total Orders</div>
</div>

<div class="stat-card">
<div class="stat-value">$<?php echo number_format($totalRevenue, 2); ?></div>
<div class="stat-label">Total Revenue</div>
</div>
</div>

<div class="dashboard-recent">
<h2>Recent Orders</h2>

<?php if (empty($recentOrders)): ?>
<p>No orders yet.</p>
<?php else: ?>
<table class="admin-table">
<thead>
<tr>
<th>Order ID</th>
<th>Customer</th>
<th>Date</th>
<th>Amount</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($recentOrders as $order): ?>
<tr>
<td>#<?php echo $order['order_id']; ?></td>
<td><?php echo $order['username']; ?></td>
<td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
<td>$<?php echo number_format($order['total_amount'], 2); ?></td>
<td><?php echo ucfirst($order['status']); ?></td>
<td>
<a href="<?php echo ADMIN_URL; ?>/view-order.php?id=<?php echo $order['order_id']; ?>" class="btn">View</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</div>

<?php include 'includes/admin-footer.php'; ?>
