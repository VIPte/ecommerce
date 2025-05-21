<?php
$pageTitle = 'Add Product';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access this page';
    redirect(BASE_URL);
}

// Get all categories
$categories = getCategories();

$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $image = sanitize($_POST['image']);
    $rating_rate = (float)$_POST['rating_rate'];
    $rating_count = (int)$_POST['rating_count'];
    
    // Validate input
    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Description is required';
    }
    
    if ($price <= 0) {
        $errors[] = 'Price must be greater than 0';
    }
    
    if ($category_id <= 0) {
        $errors[] = 'Category is required';
    }
    
    if (empty($image)) {
        $errors[] = 'Image URL is required';
    }
    
    // If no errors, add product
    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO products (title, description, price, category_id, image, rating_rate, rating_count) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssissi", $title, $description, $price, $category_id, $image, $rating_rate, $rating_count);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Product added successfully';
            redirect(ADMIN_URL . '/products.php');
        } else {
            $errors[] = 'Failed to add product. Please try again.';
        }
    }
}

// Include admin header
include 'includes/admin-header.php';
?>

<div class="admin-add-product">
    <h1 class="page-title">Add New Product</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="<?php echo ADMIN_URL; ?>/add-product.php" method="POST" class="admin-form">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image">Image URL *</label>
                    <input type="text" id="image" name="image" value="<?php echo isset($_POST['image']) ? htmlspecialchars($_POST['image']) : ''; ?>" required>
                </div>
            </div>
            
            <div class="form-col">
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="rating_rate">Rating (0-5)</label>
                    <input type="number" id="rating_rate" name="rating_rate" step="0.1" min="0" max="5" value="<?php echo isset($_POST['rating_rate']) ? htmlspecialchars($_POST['rating_rate']) : '0'; ?>">
                </div>
                
                <div class="form-group">
                    <label for="rating_count">Rating Count</label>
                    <input type="number" id="rating_count" name="rating_count" min="0" value="<?php echo isset($_POST['rating_count']) ? htmlspecialchars($_POST['rating_count']) : '0'; ?>">
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <a href="<?php echo ADMIN_URL; ?>/products.php" class="btn">Cancel</a>
            <button type="submit" class="btn">Add Product</button>
        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>