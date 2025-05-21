<?php
$pageTitle = 'Manage Orders';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Get filter parameters
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build query
$sql = "SELECT o.*, u.username 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE 1=1";
$params = [];
$types = "";

if (!empty($status)) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR o.order_id LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if (!empty($date_from)) {
    $sql .= " AND o.order_date >= ?";
    $date_from_start = $date_from . " 00:00:00";
    $params[] = $date_from_start;
    $types .= "s";
}

if (!empty($date_to)) {
    $sql .= " AND o.order_date <= ?";
    $date_to_end = $date_to . " 23:59:59";
    $params[] = $date_to_end;
    $types .= "s";
}

// Add sorting
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY o.order_date ASC";
        break;
    case 'highest':
        $sql .= " ORDER BY o.total_amount DESC";
        break;
    case 'lowest':
        $sql .= " ORDER BY o.total_amount ASC";
        break;
    default:
        $sql .= " ORDER BY o.order_date DESC";
}

// Prepare and execute query
$stmt = $db->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Get order statistics - use COALESCE to handle NULL values
$result = $db->query("SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_revenue FROM orders");
$stats = $result->fetch_assoc();

$result = $db->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$status_counts = [];
while ($row = $result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-orders">
    <div class="page-header">
        <h1 class="page-title">Manage Orders</h1>
    </div>

    <div class="order-stats">
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>

        <div class="stat-card">
            <div class="stat-value">$<?php echo number_format((float) $stats['total_revenue'], 2); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo isset($status_counts['pending']) ? $status_counts['pending'] : 0; ?>
            </div>
            <div class="stat-label">Pending Orders</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo isset($status_counts['delivered']) ? $status_counts['delivered'] : 0; ?>
            </div>
            <div class="stat-label">Delivered Orders</div>
        </div>
    </div>

    <div class="order-filters">
        <form action="<?php echo ADMIN_URL; ?>/orders.php" method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Order ID, Username, Email">
                </div>

                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing
                        </option>
                        <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered
                        </option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled
                        </option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sort">Sort By</label>
                    <select id="sort" name="sort">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="highest" <?php echo $sort === 'highest' ? 'selected' : ''; ?>>Highest Amount
                        </option>
                        <option value="lowest" <?php echo $sort === 'lowest' ? 'selected' : ''; ?>>Lowest Amount</option>
                    </select>
                </div>
            </div>

            <div class="filter-row">
                <div class="filter-group">
                    <label for="date_from">Date From</label>
                    <input type="date" id="date_from" name="date_from"
                        value="<?php echo htmlspecialchars($date_from); ?>">
                </div>

                <div class="filter-group">
                    <label for="date_to">Date To</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn">Apply Filters</button>
                    <a href="<?php echo ADMIN_URL; ?>/orders.php" class="btn">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="order-list">
        <?php if (empty($orders)): ?>
            <div class="no-results">
                <p>No orders found matching your criteria.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo $order['username']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/view-order.php?id=<?php echo $order['order_id']; ?>"
                                    class="btn">View</a>
                                <a href="<?php echo ADMIN_URL; ?>/update-order-status.php?id=<?php echo $order['order_id']; ?>"
                                    class="btn">Update Status</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
    .order-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .filter-form {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 15px;
    }

    .filter-row:last-child {
        margin-bottom: 0;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
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

    .no-results {
        background-color: #f8f9fa;
        padding: 20px;
        text-align: center;
        border-radius: 8px;
    }

    @media (max-width: 768px) {
        .filter-group {
            min-width: 100%;
        }

        .filter-actions {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>

<?php include 'includes/admin-footer.php'; ?>