<?php
// Add this file to your project root and include it at the top of search.php
// to help debug the category selection issue

function debug_category()
{
    echo '<div style="background: #f8f9fa; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd;">';
    echo '<h3>Debug Information</h3>';

    echo '<p><strong>$_GET:</strong></p>';
    echo '<pre>';
    print_r($_GET);
    echo '</pre>';

    echo '<p><strong>Sanitized Category:</strong> ';
    if (isset($_GET['category'])) {
        $category = is_array($_GET['category']) ? $_GET['category'][0] : $_GET['category'];
        echo htmlspecialchars($category);
        echo ' (Type: ' . gettype($category) . ')';
    } else {
        echo 'Not set';
    }
    echo '</p>';

    echo '</div>';
}

// To use this, add the following line at the top of search.php after including config.php:
// require_once 'debug.php';
// 
// Then add this line right before the <h1 class="page-title"> tag:
// debug_category();
?>