<?php
$pageTitle = 'Manage Categories';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $categoryName = sanitize($_POST['name']);
    
    if (empty($categoryName)) {
        $_SESSION['error_message'] = 'Category name is required';
    } else {
        // Check if category already exists
        $stmt = $db->prepare("SELECT * FROM categories WHERE name = ?");
        $stmt->bind_param("s", $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['error_message'] = 'Category already exists';
        } else {
            // Insert new category
            $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $categoryName);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Category added successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to add category: ' . $stmt->error;
            }
        }
    }
    
    redirect(ADMIN_URL . '/categories.php');
}

// Handle category deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $categoryId = (int)$_GET['id'];
    
    // Check if category has products
    $stmt = $db->prepare("SELECT COUNT(*) as product_count FROM products WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $productCount = $result->fetch_assoc()['product_count'];
    
    if ($productCount > 0) {
        $_SESSION['error_message'] = 'Cannot delete category with associated products. Reassign products first.';
    } else {
        // Delete category
        $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->bind_param("i", $categoryId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Category deleted successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to delete category: ' . $stmt->error;
        }
    }
    
    redirect(ADMIN_URL . '/categories.php');
}

// Get all categories
$categories = getCategories();

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-categories">
    <div class="admin-header-actions">
        <h1 class="page-title">Manage Categories</h1>
        <button class="btn" id="add-category-btn">Add New Category</button>
    </div>
    
    <!-- Add Category Form (hidden by default) -->
    <div class="add-category-form" id="add-category-form" style="display: none;">
        <h2>Add New Category</h2>
        <form action="<?php echo ADMIN_URL; ?>/categories.php" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Add Category</button>
                <button type="button" class="btn btn-secondary" id="cancel-add">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Categories List -->
    <div class="categories-list">
        <?php if (empty($categories)): ?>
            <p>No categories found.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Product Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): 
                        // Get product count for this category
                        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
                        $stmt->bind_param("i", $category['category_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $productCount = $result->fetch_assoc()['count'];
                    ?>
                        <tr>
                            <td><?php echo $category['category_id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo $productCount; ?></td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/edit-category.php?id=<?php echo $category['category_id']; ?>" class="btn">Edit</a>
                                <?php if ($productCount == 0): ?>
                                    <a href="<?php echo ADMIN_URL; ?>/categories.php?action=delete&id=<?php echo $category['category_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                <?php else: ?>
                                    <button class="btn btn-danger" disabled title="Cannot delete category with products">Delete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addCategoryBtn = document.getElementById('add-category-btn');
    const addCategoryForm = document.getElementById('add-category-form');
    const cancelAddBtn = document.getElementById('cancel-add');
    
    // Show add category form
    addCategoryBtn.addEventListener('click', function() {
        addCategoryForm.style.display = 'block';
        addCategoryBtn.style.display = 'none';
    });
    
    // Hide add category form
    cancelAddBtn.addEventListener('click', function() {
        addCategoryForm.style.display = 'none';
        addCategoryBtn.style.display = 'inline-block';
    });
});
</script>

<?php include 'includes/admin-footer.php'; ?>
