<?php
$pageTitle = 'Home';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Import products from JSON if the database is empty
$result = $db->query("SELECT COUNT(*) as count FROM products");
$row = $result->fetch_assoc();

/*if ($row['count'] == 0) {
    importProductsFromJson('products.json');
}*/

// Get featured products (newest products)
$featuredProducts = getProducts(null, null, null, 8);

// Get products by category
$categories = getCategories();
$categoryProducts = [];

foreach ($categories as $category) {
    $categoryProducts[$category['name']] = getProducts($category['name'], null, null, 4);
}

include 'includes/header.php';
?>

<section class="hero-section">
    <div class="hero-content">
        <h1>Welcome to Our Online Store</h1>
        <p>Discover amazing products at great prices</p>
        <a href="<?php echo BASE_URL; ?>/search.php" class="btn">Shop Now</a>
    </div>
</section>

<section class="featured-products">
    <h2 class="section-title">Featured Products</h2>
    <div class="product-grid">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $product['product_id']; ?>">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>">
                    </a>
                </div>
                <div class="product-details">
                    <h3 class="product-title">
                        <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $product['product_id']; ?>">
                            <?php echo $product['title']; ?>
                        </a>
                    </h3>
                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                    <div class="product-category"><?php echo $product['category_name']; ?></div>
                    <div class="product-rating">
                        <div class="rating-stars">
                            <?php
                            $rating = round($product['rating_rate']);
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '★';
                                } else {
                                    echo '☆';
                                }
                            }
                            ?>
                        </div>
                        <div class="rating-count">(<?php echo $product['rating_count']; ?>)</div>
                    </div>
                    <div class="product-actions">
                        <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $product['product_id']; ?>" class="btn">View Details</a>
                        <?php 
                        // Check if product is already in cart
                        $cartItemId = isProductInCart($product['product_id']);
                        
                        if ($cartItemId): // Product is in cart
                        ?>
                            <div class="cart-buttons">
                                <form action="<?php echo BASE_URL; ?>/cart-actions.php" method="POST" style="display: inline-block; margin-right: 5px;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $cartItemId; ?>">
                                    <button type="submit" class="btn btn-danger">Remove</button>
                                </form>
                                <a href="<?php echo BASE_URL; ?>/cart.php" class="btn">View in Cart</a>
                            </div>
                        <?php else: // Product is not in cart ?>
                            <form action="<?php echo BASE_URL; ?>/cart-actions.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn">Add to Cart</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php foreach ($categories as $category): ?>
    <?php if (!empty($categoryProducts[$category['name']])): ?>
        <section class="category-section">
            <h2 class="section-title"><?php echo $category['name']; ?></h2>
            <div class="product-grid">
                <?php foreach ($categoryProducts[$category['name']] as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $product['product_id']; ?>">
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>">
                            </a>
                        </div>
                        <div class="product-details">
                            <h3 class="product-title">
                                <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $product['product_id']; ?>">
                                    <?php echo $product['title']; ?>
                                </a>
                            </h3>
                            <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-category"><?php echo $product['category_name']; ?></div>
                            <div class="product-rating">
                                <div class="rating-stars">
                                    <?php
                                    $rating = round($product['rating_rate']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '★';
                                        } else {
                                            echo '☆';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="rating-count">(<?php echo $product['rating_count']; ?>)</div>
                            </div>
                            <div class="product-actions">
                                <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $product['product_id']; ?>" class="btn">View Details</a>
                                <?php 
                                // Check if product is already in cart
                                $cartItemId = isProductInCart($product['product_id']);
                                
                                if ($cartItemId): // Product is in cart
                                ?>
                                    <div class="cart-buttons">
                                        <form action="<?php echo BASE_URL; ?>/cart-actions.php" method="POST" style="display: inline-block; margin-right: 5px;">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="cart_item_id" value="<?php echo $cartItemId; ?>">
                                            <button type="submit" class="btn btn-danger">Remove</button>
                                        </form>
                                        <a href="<?php echo BASE_URL; ?>/cart.php" class="btn">View in Cart</a>
                                    </div>
                                <?php else: // Product is not in cart ?>
                                    <form action="<?php echo BASE_URL; ?>/cart-actions.php" method="POST">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="view-more">
                <a href="<?php echo BASE_URL; ?>/search.php?category=<?php echo urlencode($category['name']); ?>" class="btn">View More</a>
            </div>
        </section>
    <?php endif; ?>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>