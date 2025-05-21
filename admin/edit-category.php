<?php
$pageTitle = 'Edit Category';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Check if category ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'Category ID is required';
    redirect(ADMIN_URL . '/categories.php');
}

$categoryId = (int)$_GET['id'];

// Get category details
$stmt = $db->prepare("SELECT * FROM categories WHERE category_id = ?");
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Category not found';
    redirect(ADMIN_URL . '/categories.php');
}

$category = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = sanitize($_POST['name']);
    
    if (empty($categoryName)) {
        $_SESSION['error_message'] = 'Category name is required';
    } else {
        // Check if category name already exists (excluding current category)
        $stmt = $db->prepare("SELECT * FROM categories WHERE name = ? AND category_id != ?");
        $stmt->bind_param("si", $categoryName, $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['error_message'] = 'Category name already exists';
        } else {
            // Update category
            $stmt = $db->prepare("UPDATE categories SET name = ? WHERE category_id = ?");
            $stmt->bind_param("si", $categoryName, $categoryId);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Category updated successfully';
                redirect(ADMIN_URL . '/categories.php');
            } else {
                $_SESSION['error_message'] = 'Failed to update category: ' . $stmt->error;
            }
        }
    }
}

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-edit-category">
    <div class="admin-header-actions">
        <h1 class="page-title">Edit Category</h1>
        <a href="<?php echo ADMIN_URL; ?>/categories.php" class="btn">Back to Categories</a>
    </div>
    
    <div class="edit-category-form">
        <form action="<?php echo ADMIN_URL; ?>/edit-category.php?id=<?php echo $categoryId; ?>" method="POST">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Update Category</button>
                <a href="<?php echo ADMIN_URL; ?>/categories.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <!-- Products in this category -->
    <div class="category-products">
        <h2>Products in this Category</h2>
        
        <?php
        // Get products in this category
        $stmt = $db->prepare("SELECT * FROM products WHERE category_id = ?");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo '<p>No products in this category.</p>';
        } else {
            echo '<table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
                
            while ($product = $result->fetch_assoc()) {
                echo '<tr>
                    <td>' . $product['product_id'] . '</td>
                    <td><img src="' . $product['image'] . '" alt="' . $product['title'] . '" width="50"></td>
                    <td>' . htmlspecialchars($product['title']) . '</td>
                    <td>$' . number_format($product['price'], 2) . '</td>
                    <td>
                        <a href="' . ADMIN_URL . '/edit-product.php?id=' . $product['product_id'] . '" class="btn">Edit</a>
                        <a href="' . ADMIN_URL . '/delete-product.php?id=' . $product['product_id'] . '" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this product?\')">Delete</a>
                    </td>
                </tr>';
            }
            
            echo '</tbody></table>';
        }
        ?>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
