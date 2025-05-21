<?php require_once 'includes/config.php'; ?>
<?php require_once 'includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
</head>

<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">
                    <h1><?php echo SITE_NAME; ?></h1>
                </a>
            </div>

            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li class="dropdown">
                        <a href="#">Categories</a>
                        <div class="dropdown-content">
                            <?php
                            $categories = getCategories();
                            foreach ($categories as $category) {
                                echo '<a href="' . BASE_URL . '/search.php?category=' . urlencode($category['name']) . '">' . $category['name'] . '</a>';
                            }
                            ?>
                        </div>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>/search.php">filter</a></li>
                </ul>
            </nav>

            <div class="header-actions">
                <div class="search-form">
                    <form action="<?php echo BASE_URL; ?>/search.php" method="GET">
                        <input type="text" name="q" placeholder="Search products...">
                        <button type="submit">Search</button>
                    </form>
                </div>

                <div class="user-actions">
                    <a href="<?php echo BASE_URL; ?>/cart.php" class="cart-icon">
                        Cart
                        <?php
                        $cart = getCart();
                        if ($cart['item_count'] > 0) {
                            echo '<span class="cart-count">' . $cart['item_count'] . '</span>';
                        }
                        ?>
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <a href="#" class="user-menu">
                                <?php echo $_SESSION['username']; ?>
                            </a>
                            <div class="dropdown-content">
                                <a href="<?php echo BASE_URL; ?>/profile.php">My Profile</a>                                
                                <?php if (isAdmin()): ?>
                                    <a href="<?php echo ADMIN_URL; ?>">Admin Panel</a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>/logout.php">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login.php">Login</a>
                        <a href="<?php echo BASE_URL; ?>/register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
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