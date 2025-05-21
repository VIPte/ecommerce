<?php
$pageTitle = 'Manage Products';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Get all products
$products = getProducts();

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-products">
    <div class="page-header">
        <h1 class="page-title">Manage Products</h1>
        <a href="<?php echo ADMIN_URL; ?>/add-product.php" class="btn">Add New Product</a>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Title</th>
                <th>Price</th>
                <th>Category</th>
                <th>Rating</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product['product_id']; ?></td>
                    <td>
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>" class="product-image-preview">
                    </td>
                    <td><?php echo $product['title']; ?></td>
                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo $product['category_name']; ?></td>
                    <td><?php echo $product['rating_rate']; ?> (<?php echo $product['rating_count']; ?> reviews)</td>
                    <td>
                        <a href="<?php echo ADMIN_URL; ?>/edit-product.php?id=<?php echo $product['product_id']; ?>" class="btn">Edit</a>
                        <a href="<?php echo ADMIN_URL; ?>/delete-product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin-footer.php'; ?>