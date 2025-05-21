<?php
$pageTitle = 'Order Reports';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Get report parameters
$report_type = isset($_GET['report_type']) ? sanitize($_GET['report_type']) : 'daily';
$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : date('Y-m-d');

// Create date range variables for queries
$date_from_start = $date_from . " 00:00:00";
$date_to_end = $date_to . " 23:59:59";

// Build query based on report type
$sql = "";
$group_by = "";
$date_format = "";

switch ($report_type) {
    case 'monthly':
        $date_format = "%Y-%m";
        $group_by = "YEAR(order_date), MONTH(order_date)";
        break;
    case 'yearly':
        $date_format = "%Y";
        $group_by = "YEAR(order_date)";
        break;
    default: // daily
        $date_format = "%Y-%m-%d";
        $group_by = "DATE(order_date)";
}

$sql = "SELECT 
            DATE_FORMAT(order_date, '$date_format') as period,
            COUNT(*) as order_count,
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as average_order_value
        FROM orders
        WHERE order_date BETWEEN ? AND ?
        GROUP BY $group_by
        ORDER BY period ASC";

$stmt = $db->prepare($sql);
$stmt->bind_param("ss", $date_from_start, $date_to_end);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

// Get top selling products
$sql = "SELECT 
            p.product_id,
            p.title,
            p.image,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.quantity * oi.price) as total_revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.order_date BETWEEN ? AND ?
        GROUP BY p.product_id
        ORDER BY total_quantity DESC
        LIMIT 5";

$stmt = $db->prepare($sql);
$stmt->bind_param("ss", $date_from_start, $date_to_end);
$stmt->execute();
$result = $stmt->get_result();

$top_products = [];
while ($row = $result->fetch_assoc()) {
    $top_products[] = $row;
}

// Get order status distribution
$sql = "SELECT 
            status,
            COUNT(*) as count,
            (COUNT(*) / (SELECT COUNT(*) FROM orders WHERE order_date BETWEEN ? AND ?)) * 100 as percentage
        FROM orders
        WHERE order_date BETWEEN ? AND ?
        GROUP BY status";

$stmt = $db->prepare($sql);
$stmt->bind_param("ssss", $date_from_start, $date_to_end, $date_from_start, $date_to_end);
$stmt->execute();
$result = $stmt->get_result();

$status_distribution = [];
while ($row = $result->fetch_assoc()) {
    $status_distribution[] = $row;
}

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-reports">
    <div class="page-header">
        <h1 class="page-title">Order Reports</h1>
    </div>

    <div class="report-filters">
        <form action="<?php echo ADMIN_URL; ?>/order-reports.php" method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="report_type">Report Type</label>
                    <select id="report_type" name="report_type">
                        <option value="daily" <?php echo $report_type === 'daily' ? 'selected' : ''; ?>>Daily</option>
                        <option value="monthly" <?php echo $report_type === 'monthly' ? 'selected' : ''; ?>>Monthly
                        </option>
                        <option value="yearly" <?php echo $report_type === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                </div>

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
                    <button type="submit" class="btn">Generate Report</button>
                </div>
            </div>
        </form>
    </div>

    <div class="report-summary">
        <div class="summary-card">
            <div class="summary-title">Total Orders</div>
            <div class="summary-value">
                <?php
                $total_orders = 0;
                foreach ($reports as $report) {
                    $total_orders += $report['order_count'];
                }
                echo $total_orders;
                ?>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-title">Total Revenue</div>
            <div class="summary-value">
                <?php
                $total_revenue = 0;
                foreach ($reports as $report) {
                    $total_revenue += $report['total_revenue'];
                }
                echo '$' . number_format($total_revenue, 2);
                ?>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-title">Average Order Value</div>
            <div class="summary-value">
                <?php
                $avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;
                echo '$' . number_format($avg_order_value, 2);
                ?>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-title">Period</div>
            <div class="summary-value">
                <?php echo date('M j, Y', strtotime($date_from)) . ' - ' . date('M j, Y', strtotime($date_to)); ?>
            </div>
        </div>
    </div>

    <div class="report-grid">
        <div class="report-section">
            <h2>Sales Over Time</h2>
            <?php if (empty($reports)): ?>
                <div class="no-data">No data available for the selected period.</div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                            <th>Avg. Order Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?php echo $report['period']; ?></td>
                                <td><?php echo $report['order_count']; ?></td>
                                <td>$<?php echo number_format($report['total_revenue'], 2); ?></td>
                                <td>$<?php echo number_format($report['average_order_value'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="report-section">
            <h2>Top Selling Products</h2>
            <?php if (empty($top_products)): ?>
                <div class="no-data">No data available for the selected period.</div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Quantity Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                <td>
                                    <img src="<?php echo $product['image']; ?>"
                                        alt="<?php echo htmlspecialchars($product['title']); ?>" class="product-image-preview">
                                </td>
                                <td><?php echo $product['total_quantity']; ?></td>
                                <td>$<?php echo number_format($product['total_revenue'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="report-section">
            <h2>Order Status Distribution</h2>
            <?php if (empty($status_distribution)): ?>
                <div class="no-data">No data available for the selected period.</div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($status_distribution as $status): ?>
                            <tr>
                                <td>
                                    <span class="status-badge status-<?php echo $status['status']; ?>">
                                        <?php echo ucfirst($status['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $status['count']; ?></td>
                                <td><?php echo number_format($status['percentage'], 2); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .report-filters {
        margin-bottom: 30px;
    }

    .report-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .summary-card {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .summary-title {
        font-size: 16px;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .summary-value {
        font-size: 24px;
        font-weight: 600;
        color: #007bff;
    }

    .report-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .report-section {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .report-section h2 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 20px;
    }

    .no-data {
        padding: 20px;
        text-align: center;
        background-color: #f8f9fa;
        border-radius: 4px;
    }

    .product-image-preview {
        width: 50px;
        height: 50px;
        object-fit: contain;
    }
</style>

<?php include 'includes/admin-footer.php'; ?>