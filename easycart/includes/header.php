<?php
/**
 * Global Header
 * 
 * Responsibility: Renders the site logo, search bar, navigation links, and account/cart buttons.
 * 
 * Why it exists: To provide consistent navigation and branding across all pages.
 * 
 * When it runs: Included at the top of every user-facing page.
 */

// Load configuration
require_once __DIR__ . '/bootstrap/config.php';
?>

<!-- Global EasyCart configuration for JavaScript -->
<script>
    window.EasyCart = {
        baseUrl: '<?php echo BASE_PATH; ?>',
        ajaxUrl: '<?php echo url('ajax'); ?>'
    };
</script>

<header id="site-header">
    <div class="header-top">
        <div class="logo">
            <h1><a href="<?php echo url('index.php'); ?>">EasyCart</a></h1>
        </div>
        <div class="search-bar">
            <form action="<?php echo url('pages/products.php'); ?>" method="get" role="search">
                <input type="search" id="search-input" name="q" placeholder="Search products..."
                    aria-label="Search products">
                <button type="submit">Search</button>
            </form>
        </div>
        <!-- Global Notification Container -->
        <div id="global-notification-container"></div>
        <div class="header-actions">
            <a href="<?php echo url('pages/login.php'); ?>" class="action-link" aria-label="Login">Login</a>
            <a href="<?php echo url('pages/cart.php#wishlist-section'); ?>"
                class="action-link icon-wrapper wishlist-link" aria-label="View wishlist">
                <i class="wishlist-icon" aria-hidden="true"></i>
                <span class="icon-badge" id="header-wishlist-count">0</span>
            </a>
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            if ($current_page !== 'login.php' && $current_page !== 'signup.php'):
                $header_cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                ?>
                <a href="<?php echo url('pages/cart.php'); ?>" class="action-link icon-wrapper cart-link"
                    aria-label="View cart">
                    <i class="cart-icon">🛒</i>
                    <span class="icon-badge" id="header-total-items"><?php echo $header_cart_count; ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <nav id="main-navigation" aria-label="Main navigation">
        <ul>
            <li><a href="<?php echo url('index.php'); ?>">Home</a></li>
            <li><a href="<?php echo url('pages/products.php'); ?>">Products</a></li>
            <li><a href="<?php echo url('pages/products.php?category=electronics'); ?>">Electronics</a></li>
            <li><a href="<?php echo url('pages/products.php?category=clothing'); ?>">Clothing</a></li>
            <li><a href="<?php echo url('pages/products.php?category=home'); ?>">Home & Garden</a></li>
            <li><a href="<?php echo url('pages/products.php?category=sports'); ?>">Sports</a></li>
        </ul>
    </nav>
    <script src="<?php echo asset('js/utils/confirm.js'); ?>"></script>
</header>