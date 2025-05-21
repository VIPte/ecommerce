        </div>
    </main>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>We offer a wide range of products at competitive prices.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Categories</h3>
                    <ul>
                        <?php 
                        $categories = getCategories();
                        foreach ($categories as $category) {
                            echo '<li><a href="' . BASE_URL . '/search.php?category=' . urlencode($category['name']) . '">' . $category['name'] . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Email: contact@example.com</p>
                    <p>Phone: +1 234 567 890</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
</body>
</html>