<?php require_once '../includes/config.php'; ?>
<?php require_once '../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin Panel' : 'Admin Panel'; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/admin.css">
</head>

<body>
    <header class="admin-header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo ADMIN_URL; ?>">
                    <h1>Admin Panel</h1>
                </a>
            </div>

            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                <a href="<?php echo BASE_URL; ?>/logout.php">Logout</a>
            </div>
        </div>
    </header>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <nav class="admin-nav">
                <ul>
                    <li><a href="<?php echo ADMIN_URL; ?>">Dashboard</a></li>
                    <li><a href="<?php echo ADMIN_URL; ?>/products.php">Products</a></li>
                    <li><a href="<?php echo ADMIN_URL; ?>/categories.php">Categories</a></li>
                    <li><a href="<?php echo ADMIN_URL; ?>/orders.php">Orders</a></li>
                    <li><a href="<?php echo ADMIN_URL; ?>/order-reports.php">Order Reports</a></li>
                    <li><a href="<?php echo ADMIN_URL; ?>/customers.php">Customers</a></li>
                    <li><a href="<?php echo BASE_URL; ?>" target="_blank">View Store</a></li>
                </ul>
            </nav>
        </aside>

        <main class="admin-content">
            <div class="admin-content-inner">
                <?php
                // Display flash messages
                if (isset($_SESSION['success_message'])) {
                    echo displaySuccess($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                }

                if (isset($_SESSION['error_message'])) {
                    echo displayError($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                }
                ?>